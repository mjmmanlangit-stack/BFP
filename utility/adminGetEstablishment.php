<?php<?php

include_once 'db.php';    include_once 'db.php';

session_start();    session_start();

    

// Get establishments that either:    $stmt = $conn->prepare('SELECT e.id , e.name, e.type, e.registration_no, e.type, e.address as location, 

// 1. Have never been inspected (first time) - no payment required    u.fullname as owner, u.phone_number as contact, i.createdAt as lastInspection, i.notes

// 2. Have paid for their latest inspection - can schedule new inspection    FROM establishment e JOIN user u ON e.owner_id = u.id LEFT JOIN inspection i ON e.id = i.establishment_id');

$query = 'SELECT     $stmt->execute();

    e.id,     $result = $stmt->get_result();

    e.name,     $row = $result->fetch_all(MYSQLI_ASSOC);

    e.type,     // if($result->num_rows > 0){

    e.registration_no,     //     $data = [];

    e.address as location,     //     while($row = $result->fetch_assoc()){

    u.fullname as owner,     //         $data[] = [

    u.phone_number as contact,    //             "id"=>$row['e.id'],

    (SELECT MAX(i2.createdAt) FROM inspection i2 WHERE i2.establishment_id = e.id) as lastInspection,    //             "name"=>$row['e.name'],

    (SELECT i3.notes FROM inspection i3 WHERE i3.establishment_id = e.id ORDER BY i3.createdAt DESC LIMIT 1) as notes,    //             "type"=>!empty($row['e.type']) ? $row['e.type'] : "N/A",

    (SELECT i4.payment FROM inspection i4 WHERE i4.establishment_id = e.id ORDER BY i4.createdAt DESC LIMIT 1) as lastPaymentStatus    //             "bfpRegNo"=>!empty($row['e.registration_no']) ? $row['e.registration_no']: "Not Registered",

FROM establishment e     //             "type"=>$row['e.type'],

JOIN user u ON e.owner_id = u.id    //             "lat"=>$row['e.y_coordinate'],

HAVING lastPaymentStatus IS NULL OR lastPaymentStatus = 1';    //             "lng"=>$row['e.x_coordinate'],

    //             "address"=>$row['e.address'],

$stmt = $conn->prepare($query);    //             "lastInspection"=>isset($row['e.last_inspection']) ? $row['e.last_inspection'] : "N/A",

$stmt->execute();    //         ];

$result = $stmt->get_result();        // }

$row = $result->fetch_all(MYSQLI_ASSOC);        echo json_encode($row);

    // }else{

echo json_encode($row);    //     echo json_encode(['error'=>$stmt->error, "user"=>$user]);

    // }

$stmt->close();    $stmt->close();

$conn->close();    $conn->close();

?>





?>