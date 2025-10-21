<?php
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
