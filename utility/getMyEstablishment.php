<?php
    include_once 'db.php';
    session_start();
    $user = $_SESSION['user'];
    
    $stmt = $conn->prepare('SELECT * FROM establishment WHERE owner_id = ?');
    $stmt->bind_param('i', $user);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $data = [];
        while($row = $result->fetch_assoc()){
            $data[] = [
                "id"=>$row['id'],
                "name"=>$row['name'],
                "type"=>!empty($row['type']) ? $row['type'] : "N/A",
                "bfpRegNo"=>!empty($row['registration_no']) ? $row['registration_no']: "Not Registered",
                "type"=>$row['type'],
                "lat"=>$row['y_coordinate'],
                "lng"=>$row['x_coordinate'],
                "address"=>$row['address'],
                "lastInspection"=>isset($row['last_inspection']) ? $row['last_inspection'] : "N/A",
            ];
        }
        echo json_encode($data);
    }else{
        echo json_encode(['error'=>$stmt->error, "user"=>$user]);
    }
    $stmt->close();
    $conn->close();





?>