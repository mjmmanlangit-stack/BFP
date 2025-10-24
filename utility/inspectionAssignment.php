<?php
// Start output buffering
ob_start();

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

include_once 'db.php';

    $stmt = $conn->prepare("SELECT e.x_coordinate as lng, e.y_coordinate as lat, e.type, e.name FROM inspection i RIGHT JOIN establishment e ON e.id = i.establishment_id");
    $stmt->execute();
    $res = $stmt->get_result();
    $result = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
    $stmt->close();
    $conn->close();
?>