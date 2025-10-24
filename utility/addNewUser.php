<?php
// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

session_start();
include 'db.php';
include 'mailer.php';

$data = json_decode(file_get_contents("php://input"),true);
if($data){
    $fullname = $data['fullname'];
    $role = $data['role'];
    $email = $data['email'];
    $address = $data['address'];
    $contact = isset($data['contact']) ? $data['contact'] : '';
    $password = isset($data['password']) ? $data['password'] : bin2hex(random_bytes(4));
    $status = $data['status'];

    $stmt = $conn->prepare("INSERT INTO user (fullname, email, address, phone_number, role, password, status, username) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssss",$fullname, $email, $address, $contact, $role, $password, $status, $email);
    if($stmt->execute()){
        $insertId = $conn->insert_id;
        
        // Log the user creation
        $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;
        if ($userId) {
            $activityLogger->logCreate(
                $userId,
                'user_created',
                'user',
                'Created new user: ' . $fullname . ' (' . $email . ') with role: ' . $role,
                [
                    'user_id' => $insertId,
                    'fullname' => $fullname,
                    'email' => $email,
                    'role' => $role,
                    'status' => $status
                ]
            );
        }
        
        // Try to send email but don't fail if it doesn't work
        sendEmail($email, 'Hi '.$fullname.'<h2>You have been successfully added to the system</h2>. <br> username: '.$email.'<br>'.'password: '.$password, 'Welcome to BFP profiler');
        
        echo json_encode(["success"=>"user has been added", "id" => $insertId]);
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