<?php
ob_start();
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

include_once 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if($data){
    $business_name   = $data['business_name']  ?? '';
    $id              = (int)($data['id']        ?? 0);
    $registration_no = $data['registration_no'] ?? '';
    $type            = $data['type']            ?? '';
    $ownership_type  = $data['ownership_type']  ?? '';
    $tin_number      = $data['tin_number']      ?? '';
    $contact_number  = $data['contact_number']  ?? '';
    $contact_email   = $data['contact_email']   ?? '';
    $address         = $data['address']         ?? '';
    $x_coordinate    = $data['x_coordinate']    ?? '';
    $y_coordinate    = $data['y_coordinate']    ?? '';
    $status          = 'active';

    if (!$id || !$business_name || !$type || !$address) {
        echo json_encode(['error' => 'ID, business name, type, and address are required']);
        exit;
    }

    // Get old establishment data before updating for logging
    $getStmt = $conn->prepare('SELECT * FROM establishment WHERE id = ?');
    $getStmt->bind_param('i', $id);
    $getStmt->execute();
    $result  = $getStmt->get_result();
    $oldData = $result->fetch_assoc();
    $getStmt->close();

    $stmt = $conn->prepare('UPDATE establishment SET address = ?, type = ?, ownership_type = ?, tin_number = ?, contact_number = ?, contact_email = ?, name = ?, status = ?, x_coordinate = ?, y_coordinate = ?, registration_no = ? WHERE id = ?');
    $stmt->bind_param('sssssssssssi', $address, $type, $ownership_type, $tin_number, $contact_number, $contact_email, $business_name, $status, $x_coordinate, $y_coordinate, $registration_no, $id);
    if($stmt->execute()){
        $userId = $_SESSION['user'];
        if ($oldData) {
            $activityLogger->logUpdate(
                $userId,
                'establishment_updated',
                'establishment',
                'Updated establishment ID: ' . $id . ' (' . $business_name . ')',
                [
                    'name'            => $oldData['name'],
                    'type'            => $oldData['type'],
                    'registration_no' => $oldData['registration_no'],
                    'address'         => $oldData['address']
                ],
                [
                    'name'            => $business_name,
                    'type'            => $type,
                    'registration_no' => $registration_no,
                    'address'         => $address        // ← was $busiaddressness_name (typo)
                ]
            );
        }
        echo json_encode(['success' => true, 'message' => 'Establishment updated successfully']);
    } else {
        echo json_encode(['error' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'No data received']);
}
?>