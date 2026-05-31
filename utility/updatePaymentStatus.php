<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

// Get input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || (!isset($data['inspectionId']) && !isset($data['establishmentId']))) {
    die(json_encode(['success' => false, 'message' => 'Missing inspection ID or establishment ID']));
}

$inspectionId = isset($data['inspectionId']) ? (int)$data['inspectionId'] : 0;
$establishmentId = isset($data['establishmentId']) ? (int)$data['establishmentId'] : 0;
$paymentAmount = isset($data['paymentAmount']) ? (float)$data['paymentAmount'] : 0;
$userId = (int)$_SESSION['user'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bfpprofiler');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// If inspectionId is 0 (new establishment without inspection), create one first
if ($inspectionId === 0 && $establishmentId > 0) {
    $createInspStmt = $conn->prepare("INSERT INTO inspection (establishment_id, inspection_date, inspection_type, status) VALUES (?, NOW(), 'fire_code_fee', 'completed')");
    $createInspStmt->bind_param("i", $establishmentId);
    if (!$createInspStmt->execute()) {
        $createInspStmt->close();
        $conn->close();
        die(json_encode(['success' => false, 'message' => 'Failed to create inspection record']));
    }
    $inspectionId = $conn->insert_id;
    $createInspStmt->close();
}

// Get inspection details
require_once __DIR__ . '/mailer.php';

$stmt = $conn->prepare("SELECT i.id, i.establishment_id, e.name as business_name,
                                owner.fullname as owner_name, owner.email as owner_email, owner.phone_number as owner_phone
                        FROM inspection i
                        JOIN establishment e ON i.establishment_id = e.id
                        LEFT JOIN user owner ON e.owner_id = owner.id
                        WHERE i.id = ?");
$stmt->bind_param("i", $inspectionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'Inspection not found']));
}

$inspection = $result->fetch_assoc();
$stmt->close();

// Update payment status including audit columns and fire code receipt fields
$confirmedAt = date('Y-m-d H:i:s');
$paymentDate = date('Y-m-d H:i:s');
$updateStmt = $conn->prepare(
    "UPDATE inspection SET payment = 1, payment_amount = ?, payment_date = ?, payment_confirmed_by = ?, payment_confirmed_at = ? WHERE id = ?"
);
$updateStmt->bind_param("dsisi", $paymentAmount, $paymentDate, $userId, $confirmedAt, $inspectionId);

if ($updateStmt->execute()) {
    $updateStmt->close();

    // Ensure expiry_date column exists in certificates table
    $conn->query("ALTER TABLE certificates ADD COLUMN IF NOT EXISTS expiry_date DATE DEFAULT NULL");

    // Create a certificate if one doesn't already exist for this inspection
    $checkStmt = $conn->prepare("SELECT id FROM certificates WHERE inspection_id = ? AND status = 'authorized' LIMIT 1");
    $checkStmt->bind_param("i", $inspectionId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkStmt->close();

    if ($checkResult->num_rows === 0) {
        $certNum    = 'BFP-FSIC-' . date('Y') . '-' . str_pad($inspectionId, 6, '0', STR_PAD_LEFT);
        $expiryDate = date('Y-m-d', strtotime('+1 year'));
        $estId      = $inspection['establishment_id'];

        $certStmt = $conn->prepare(
            "INSERT INTO certificates (inspection_id, establishment_id, status, authorized_by, certificate_number, expiry_date)
             VALUES (?, ?, 'authorized', ?, ?, ?)"
        );
        $certStmt->bind_param("iiiss", $inspectionId, $estId, $userId, $certNum, $expiryDate);
        $certStmt->execute();
        $certStmt->close();
    }

    // Notify the owner about certificate issuance
    if (!empty($inspection['owner_email'])) {
        $certNumNotif  = 'BFP-FSIC-' . date('Y') . '-' . str_pad($inspectionId, 6, '0', STR_PAD_LEFT);
        $expiryNotif   = date('Y-m-d', strtotime('+1 year'));
        $busName       = htmlspecialchars($inspection['business_name']);
        $ownerName     = htmlspecialchars($inspection['owner_name']);
        $certEmailBody = "
        <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
            <div style='background:linear-gradient(135deg,#dc3545,#a02834);padding:20px;border-radius:10px 10px 0 0;color:white;'>
                <h2 style='margin:0;'>Fire Safety Inspection Certificate Issued</h2>
            </div>
            <div style='background:#f8f9fa;padding:20px;border-radius:0 0 10px 10px;'>
                <p>Dear {$ownerName},</p>
                <p>Congratulations! Your Fire Safety Inspection Certificate (FSIC) for <strong>{$busName}</strong> has been issued.</p>
                <ul>
                    <li><strong>Certificate No:</strong> {$certNumNotif}</li>
                    <li><strong>Valid Until:</strong> {$expiryNotif}</li>
                </ul>
                <p>Please log in to the BFP Site Profiler to download your certificate.</p>
            </div>
        </div>";
        sendEmail($inspection['owner_email'], $certEmailBody, 'FSIC Issued — BFP Site Profiler');
        if (!empty($inspection['owner_phone'])) {
            sendSMS($inspection['owner_phone'],
                "BFP Site Profiler: Your FSIC for {$inspection['business_name']} has been issued. Cert No: {$certNumNotif}. Valid until {$expiryNotif}. Log in to download."
            );
        }
    }

    // Log activity
    $logStmt = $conn->prepare("INSERT INTO activity_log (user_id, action, module, description, status, ip_address) 
                               VALUES (?, ?, ?, ?, ?, ?)");
    if ($logStmt) {
        $action = 'payment_updated';
        $module = 'payment';
        $description = "Updated payment status for {$inspection['business_name']} - Amount: ₱" . number_format($paymentAmount, 2);
        $status = 'success';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $logStmt->bind_param("isssss", $userId, $action, $module, $description, $status, $ip);
        $logStmt->execute();
        $logStmt->close();
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment status updated successfully',
        'inspectionId' => $inspectionId,
        'businessName' => $inspection['business_name'],
        'ownerName' => $inspection['owner_name'],
        'paymentAmount' => $paymentAmount
    ]);
} else {
    $updateStmt->close();
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'Failed to update payment status']));
}
?>
