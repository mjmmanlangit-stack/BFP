<?php
session_start();
header('Content-Type: application/json');

// Check authentication - allow CRO and admin
if (!isset($_SESSION['user'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}
$role = strtolower($_SESSION['role']);
if (!in_array($role, ['cro', 'admin'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Get inspection ID from query parameter
if (!isset($_GET['inspectionId']) || !is_numeric($_GET['inspectionId'])) {
    die(json_encode(['success' => false, 'message' => 'Invalid or missing inspection ID']));
}

$inspectionId = (int)$_GET['inspectionId'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bfpprofiler');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

try {
    // Fetch inspection, establishment, owner, and payment details
    $stmt = $conn->prepare("
        SELECT 
            i.id as inspection_id,
            i.inspection_date,
            i.payment,
            i.payment_amount,
            i.payment_date,
            i.payment_confirmed_by,
            i.payment_confirmed_at,
            e.id as establishment_id,
            e.name as establishment_name,
            e.address as establishment_address,
            owner.fullname as owner_name,
            owner.email as owner_email,
            owner.phone_number as owner_phone,
            u1.fullname as inspector1_name,
            u2.fullname as inspector2_name,
            r.compliance_status,
            r.finalized_at,
            c.certificate_number,
            c.remarks,
            confirmedBy.fullname as confirmed_by_name
        FROM inspection i
        INNER JOIN establishment e ON i.establishment_id = e.id
        LEFT JOIN user owner ON e.owner_id = owner.id
        LEFT JOIN user u1 ON i.inspector1 = u1.id
        LEFT JOIN user u2 ON i.inspector2 = u2.id
        LEFT JOIN reports r ON i.id = r.inspection_id
        LEFT JOIN certificates c ON i.id = c.inspection_id
        LEFT JOIN user confirmedBy ON i.payment_confirmed_by = confirmedBy.id
        WHERE i.id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
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
    
    // Fetch defects and fees for this inspection
    $defectStmt = $conn->prepare("
        SELECT d.defects_details, d.grace_period, d.status
        FROM defects d
        INNER JOIN reports r ON d.report_id = r.id
        WHERE r.inspection_id = ?
    ");
    
    if (!$defectStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $defectStmt->bind_param("i", $inspectionId);
    $defectStmt->execute();
    $defectResult = $defectStmt->get_result();
    
    $defects = [];
    while ($defect = $defectResult->fetch_assoc()) {
        $defects[] = [
            'details' => $defect['defects_details'],
            'grace_period' => $defect['grace_period'],
            'status' => $defect['status']
        ];
    }
    $defectStmt->close();
    
    // Build receipt data
    $receipt = [
        'inspectionId' => $inspection['inspection_id'],
        'establishmentName' => $inspection['establishment_name'] ?? '—',
        'establishmentAddress' => $inspection['establishment_address'] ?? '—',
        'ownerName' => $inspection['owner_name'] ?? '—',
        'ownerEmail' => $inspection['owner_email'] ?? '—',
        'ownerPhone' => $inspection['owner_phone'] ?? '—',
        'inspector1' => $inspection['inspector1_name'] ?? '—',
        'inspector2' => $inspection['inspector2_name'] ?? '—',
        'inspectionDate' => $inspection['inspection_date'] ?? '—',
        'complianceStatus' => $inspection['compliance_status'] ?? '—',
        'fireCodeCertificateNumber' => $inspection['certificate_number'] ?? '—',
        'paymentAmount' => !empty($inspection['payment_amount']) ? floatval($inspection['payment_amount']) : 0,
        'paymentDate' => $inspection['payment_date'] ?? '—',
        'paymentStatus' => ($inspection['payment'] == 1) ? 'Paid' : 'Pending',
        'paymentConfirmedBy' => $inspection['confirmed_by_name'] ?? '—',
        'paymentConfirmedAt' => $inspection['payment_confirmed_at'] ?? '—',
        'remarks' => $inspection['remarks'] ?? '—',
        'defects' => $defects,
        'finalizedAt' => $inspection['finalized_at'] ?? '—'
    ];
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'receipt' => $receipt
    ]);
    
} catch (Exception $e) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching payment receipt: ' . $e->getMessage()
    ]);
}
?>
