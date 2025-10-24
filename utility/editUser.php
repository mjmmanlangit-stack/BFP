<?php
// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

session_start();
include 'db.php';

$data = json_decode(file_get_contents("php://input"),true);
if($data){
    $fullname = $data['fullname'];
    $role = $data['role'];
    $email = $data['email'];
    $address = $data['address'];
    $contact = isset($data['contact']) ? $data['contact'] : '';
    $status = $data['status'];
    $id = $data['id'];
    
    // Get old user data before updating for logging
    $getStmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    $getStmt->bind_param("i", $id);
    $getStmt->execute();
    $result = $getStmt->get_result();
    $oldData = $result->fetch_assoc();
    $getStmt->close();

    $stmt = $conn->prepare("UPDATE user SET fullname = ?, email = ?, address = ?, phone_number = ?, role = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssssi",$fullname, $email, $address, $contact, $role, $status, $id);
    if($stmt->execute()){
        // Log the user update
        $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;
        if ($userId && $oldData) {
            $activityLogger->logUpdate(
                $userId,
                'user_updated',
                'user',
                'Updated user ID: ' . $id . ' (' . $fullname . ')',
                [
                    'fullname' => $oldData['fullname'],
                    'email' => $oldData['email'],
                    'role' => $oldData['role'],
                    'status' => $oldData['status']
                ],
                [
                    'fullname' => $fullname,
                    'email' => $email,
                    'role' => $role,
                    'status' => $status
                ]
            );
        }
        
        echo json_encode(["success"=>"user has been updated"]);
    }else{
        echo json_encode(["error"=> $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error"=> "No data received"]);
}
exit;
?>