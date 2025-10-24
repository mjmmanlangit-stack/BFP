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
    $establisment_id = $data['establishmentId'];
    $inspector       = $data['inspectorId'];
    $inspection_type = $data['type'];
    $inspection_date = $data['dateTime'];
    $priority_level  = $data['priority'];
    $notes           = $data['notes'];
    $time_slot       = $data['time_slot'];
    $status          = $data['status'];

    $stmt = $conn->prepare("
        INSERT INTO inspection
            (inspector, inspection_type, inspection_date, priority_level, notes, time_slot, status, establishment_id)
        VALUES (?,?,?,?,?,?,?,?)
    ");

    if (!$stmt) {
        die(json_encode(['error' => true, 'message' => $conn->error]));
    }

    $stmt->bind_param(
        "issssssi",
        $inspector,
        $inspection_type,
        $inspection_date,
        $priority_level,
        $notes,
        $time_slot,
        $status,
        $establisment_id
    );

    if ($stmt->execute()) {
        $inspection_id = $conn->insert_id;
        
        // Log the inspection creation
        $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;
        if ($userId) {
            $activityLogger->logCreate(
                $userId,
                'admin_inspection_added',
                'inspection',
                'Admin added inspection ID: ' . $inspection_id . ' for establishment ID: ' . $establisment_id,
                [
                    'inspection_id' => $inspection_id,
                    'establishment_id' => $establisment_id,
                    'inspector' => $inspector,
                    'inspection_type' => $inspection_type,
                    'inspection_date' => $inspection_date,
                    'priority_level' => $priority_level,
                    'status' => $status
                ]
            );
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => true, 'message' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => "no data"]);
}

$conn->close();
?>
