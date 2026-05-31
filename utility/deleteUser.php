<?php
session_start();
header('Content-Type: application/json');
include 'db.php';
include 'mailer.php';

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

    // Send removal notification email
    if ($userData && !empty($userData['email'])) {
        $fullname = $userData['fullname'];
        $userEmail = $userData['email'];
        $emailBody = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
            <div style="background-color: #dc3545; padding: 20px; text-align: center;">
                <h2 style="color: #fff; margin: 0;">BFP Site Profiler</h2>
            </div>
            <div style="padding: 30px;">
                <p>Hi <strong>' . htmlspecialchars($fullname) . '</strong>,</p>
                <p>We would like to inform you that your account on <strong>BFP Site Profiler</strong> has been <strong style="color:#dc3545;">removed</strong> by an administrator.</p>
                <p>You will no longer be able to log into the system using your credentials.</p>
                <p>If you believe this was done in error or have any concerns, please contact your BFP administrator immediately.</p>
            </div>
            <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;">
                &copy; BFP Site Profiler &mdash; Bureau of Fire Protection
            </div>
        </div>';
        sendEmail($userEmail, $emailBody, 'BFP Site Profiler - Account Removal Notice');
    }

    echo json_encode(["success"=>"user has been deleted"]);
}else{
    echo json_encode(["error"=> $stmt->error]);

}

$stmt->close();
$conn->close();
?>