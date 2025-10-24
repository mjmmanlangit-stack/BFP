<?php
// Start output buffering
ob_start();

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

session_start();
include_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {

    $inspector       = $data['inspectorId'];
    $inspection_type = $data['type'];
    $inspection_date = $data['dateTime'];
    $priority_level  = $data['priority'];
    $notes           = $data['notes'];
    // $time_slot       = $data['time_slot'];
    $status          = $data['status'];
    $inspectionId    = $data['inspectionId'];
    
    // Get old inspection data before updating for logging
    $getStmt = $conn->prepare("SELECT * FROM inspection WHERE id = ?");
    $getStmt->bind_param("i", $inspectionId);
    $getStmt->execute();
    $result = $getStmt->get_result();
    $oldData = $result->fetch_assoc();
    $getStmt->close();
    
    $stmt = $conn->prepare("
        UPDATE inspection SET
            inspector = ?, inspection_type = ?, inspection_date = ?, priority_level = ?, notes = ?, status = ? WHERE id = ?
    ");
    if (!$stmt) {
        die(json_encode(['error' => true, 'message' => $conn->error]));
    }
    $stmt->bind_param(
        "isssssi",
        $inspector,
        $inspection_type,
        $inspection_date,
        $priority_level,
        $notes,
        $status,
        $inspectionId
    );

    if ($stmt->execute()) {
        // Log the inspection update
        $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;
        if ($userId && $oldData) {
            $activityLogger->logUpdate(
                $userId,
                'admin_inspection_updated',
                'inspection',
                'Admin updated inspection ID: ' . $inspectionId,
                [
                    'inspector' => $oldData['inspector'],
                    'inspection_type' => $oldData['inspection_type'],
                    'inspection_date' => $oldData['inspection_date'],
                    'priority_level' => $oldData['priority_level'],
                    'status' => $oldData['status']
                ],
                [
                    'inspector' => $inspector,
                    'inspection_type' => $inspection_type,
                    'inspection_date' => $inspection_date,
                    'priority_level' => $priority_level,
                    'status' => $status
                ]
            );
        }
        
        echo json_encode(['success' => true, "message"=>"sa admin update", "p"=>$priority_level]);
    } else {
        echo json_encode(['error' => true, 'message' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => "no data"]);
}

$conn->close();
?>
