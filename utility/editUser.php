<?php
 include 'db.php';
$data = json_decode(file_get_contents("php://input"),true);
if($data){
    $fullname = $data['fullname'];
    $role = $data['role'];
    $email = $data['email'];
    $address = $data['address'];
    $password = $data['password'];
    $status = $data['status'];
    $id = $data['id'];

    $stmt = $conn->prepare("UPDATE user SET fullname = ?, address = ?, role = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssi",$fullname,$address,$role,$status,$id);
    if($stmt->execute()){
        echo json_encode(["success"=>"user has been added"]);
    }else{
        echo json_encode(["error"=> $stmt->error]);
    
    }
    
    $stmt->close();
    $conn->close();
}
?>