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

if (!$data || !isset($data['inspectionId'])) {
    die(json_encode(['success' => false, 'message' => 'Missing inspection ID']));
}

$inspectionId = (int)$data['inspectionId'];
$paymentAmount = isset($data['paymentAmount']) ? (float)$data['paymentAmount'] : 0;
$userId = (int)$_SESSION['user'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bfpprofiler');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get inspection details
$stmt = $conn->prepare("SELECT i.id, i.establishment_id, e.name as business_name, owner.fullname as owner_name 
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

// Update payment status
$updateStmt = $conn->prepare("UPDATE inspection SET payment = 1 WHERE id = ?");
$updateStmt->bind_param("i", $inspectionId);

if ($updateStmt->execute()) {
    $updateStmt->close();
    
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
