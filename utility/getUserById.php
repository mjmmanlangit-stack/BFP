<?php
// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

include_once 'db.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()){
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "User not found"]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["error" => "No ID provided"]);
}

$conn->close();
exit;
?>
