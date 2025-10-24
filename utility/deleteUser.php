<?php
session_start();
include 'db.php';

$id = (int)$_GET['id'];

// Get user data before deleting for logging
$getStmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$getStmt->bind_param("i", $id);
$getStmt->execute();
$result = $getStmt->get_result();
$userData = $result->fetch_assoc();
$getStmt->close();

$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->bind_param("i", $id);
if($stmt->execute()){
    // Log the user deletion
    $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;
    if ($userId && $userData) {
        $activityLogger->logDelete(
            $userId,
            'user_deleted',
            'user',
            'Deleted user ID: ' . $id . ' (' . $userData['fullname'] . ' - ' . $userData['email'] . ')',
            [
                'user_id' => $id,
                'fullname' => $userData['fullname'],
                'email' => $userData['email'],
                'role' => $userData['role'],
                'status' => $userData['status']
            ]
        );
    }
    
    echo json_encode(["success"=>"user has been deleted"]);
}else{
    echo json_encode(["error"=> $stmt->error]);

}

$stmt->close();
$conn->close();
?>