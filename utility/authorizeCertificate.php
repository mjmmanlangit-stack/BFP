<?php
session_start();
header('Content-Type: application/json');

// Check if logged in and restrict to Admin role
if (!isset($_SESSION['user'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}
if (strtolower($_SESSION['role']) !== 'admin') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized: Admin role required']));
}

// Get input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['inspectionId'])) {
    die(json_encode(['success' => false, 'message' => 'Missing inspection ID']));
}

$inspectionId = (int)$data['inspectionId'];
$action = $data['action'] ?? 'authorize';
$userId = (int)$_SESSION['user'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bfpprofiler');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'DB Error']));
}

// Get inspection
$stmt = $conn->prepare("SELECT i.establishment_id, e.name FROM inspection i JOIN establishment e ON i.establishment_id = e.id WHERE i.id = ?");
$stmt->bind_param("i", $inspectionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'Inspection not found']));
}

$row = $result->fetch_assoc();
$establishmentId = $row['establishment_id'];
$businessName = $row['name'];
$stmt->close();

// Create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspection_id INT NOT NULL,
    establishment_id INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    authorized_by INT NOT NULL,
    authorized_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    certificate_number VARCHAR(50)
)");

// Generate certificate number and expiry
$status  = ($action === 'authorize') ? 'authorized' : 'denied';
$certNum = ($action === 'authorize') ? 'BFP-FSIC-' . date('Y') . '-' . str_pad($inspectionId, 6, '0', STR_PAD_LEFT) : null;
$expiryDate = ($action === 'authorize') ? date('Y-m-d', strtotime('+1 year')) : null;

// Insert certificate
$stmt = $conn->prepare("INSERT INTO certificates (inspection_id, establishment_id, status, authorized_by, certificate_number, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisiss", $inspectionId, $establishmentId, $status, $userId, $certNum, $expiryDate);

if ($stmt->execute()) {
    $certId = $stmt->insert_id;
    
    // Update payment
    if ($action === 'authorize') {
        $conn->query("UPDATE inspection SET payment = 1 WHERE id = $inspectionId");
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => $action === 'authorize' ? 'Authorized' : 'Denied',
        'certificateId' => $certId,
        'certificateNumber' => $certNum,
        'expiry_date' => $expiryDate,
        'status' => $status
    ]);
} else {
    $stmt->close();
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'Insert failed']));
}
?>
