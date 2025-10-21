<?php
include_once 'db.php';
$data = json_decode(file_get_contents("php://input"),true);
session_start();
$user_id = $_SESSION['user'];
if($data){
    $business_name = $data['business_name'];
    $registration_no = $data['registration_no'];
    $type = $data['type'];
    $busiaddressness_name = $data['address'];
    $x_coordinate = $data['x_coordinate'];
    $y_coordinate = $data['y_coordinate'];
    $status = "active";
    $stmt = $conn->prepare("INSERT INTO `establishment`(`owner_id`, `address`, `type`, `name`, `status`, `x_coordinate`, `y_coordinate`, `registration_no`) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param('isssssss', $user_id, $busiaddressness_name, $type, $business_name,$status,$x_coordinate, $y_coordinate, $registration_no );
    if($stmt->execute()){
        echo json_encode(['success'=>true]);
    }else{
         echo json_encode(['error'=>$stmt->error]);
    }
}else{
    echo json_encode(['error'=> 'no data']);
}

?>