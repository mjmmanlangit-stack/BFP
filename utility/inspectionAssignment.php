<?php
    include_once 'db.php';

    $stmt = $conn->prepare("SELECT e.x_coordinate as lng, e.y_coordinate as lat, e.type, e.name FROM inspection i RIGHT JOIN establishment e ON e.id = i.establishment_id");
    $stmt->execute();
    $res = $stmt->get_result();
    $result = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
    $stmt->close();
    $conn->close();
?>