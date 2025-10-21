<?php
 include 'db.php';
 $id = (int)$_GET['id'];
$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->bind_param("i", $id);
if($stmt->execute()){
    echo json_encode(["success"=>"user has been deleted"]);
}else{
    echo json_encode(["error"=> $stmt->error]);

}

$stmt->close();
$conn->close();
?>