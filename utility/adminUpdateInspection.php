<?php
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
    $inspectionId          = $data['inspectionId'];
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
