<?php
    include_once 'db.php';
    session_start();
    
    $stmt = $conn->prepare('SELECT e.id , e.name, e.type, e.registration_no, e.type, e.address as location, 
    u.fullname as owner, u.phone_number as contact, i.createdAt as lastInspection, i.notes
    FROM establishment e JOIN user u ON e.owner_id = u.id LEFT JOIN inspection i ON e.id = i.establishment_id');
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_all(MYSQLI_ASSOC);
    // if($result->num_rows > 0){
    //     $data = [];
    //     while($row = $result->fetch_assoc()){
    //         $data[] = [
    //             "id"=>$row['e.id'],
    //             "name"=>$row['e.name'],
    //             "type"=>!empty($row['e.type']) ? $row['e.type'] : "N/A",
    //             "bfpRegNo"=>!empty($row['e.registration_no']) ? $row['e.registration_no']: "Not Registered",
    //             "type"=>$row['e.type'],
    //             "lat"=>$row['e.y_coordinate'],
    //             "lng"=>$row['e.x_coordinate'],
    //             "address"=>$row['e.address'],
    //             "lastInspection"=>isset($row['e.last_inspection']) ? $row['e.last_inspection'] : "N/A",
    //         ];
        // }
        echo json_encode($row);
    // }else{
    //     echo json_encode(['error'=>$stmt->error, "user"=>$user]);
    // }
    $stmt->close();
    $conn->close();





?>