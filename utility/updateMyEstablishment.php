<?php
include_once 'db.php';
$data = json_decode(file_get_contents("php://input"),true);
session_start();
if($data){
    $business_name = $data['business_name'];
    $id = $data['id'];
    $registration_no = $data['registration_no'];
    $type = $data['type'];
    $busiaddressness_name = $data['address'];
    $x_coordinate = $data['x_coordinate'];
    $y_coordinate = $data['y_coordinate'];
    $status = "active";
    $stmt = $conn->prepare("UPDATE establishment SET  address = ?, type = ?, name = ?, status = ?, x_coordinate = ?, y_coordinate = ?, registration_no = ? WHERE id = ?");
    $stmt->bind_param('sssssssi', $busiaddressness_name, $type, $business_name,$status,$x_coordinate, $y_coordinate, $registration_no, $id );
    if($stmt->execute()){
        echo json_encode(['success'=>true, "message"=>"establishment update successful", "Type"=>$type]);
    }else{
         echo json_encode(['error'=>$stmt->error]);
    }
}else{
    echo json_encode(['error'=> 'no data']);
}

?>