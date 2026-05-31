<?php
// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

session_start();
include 'db.php';
include 'mailer.php';

$data = json_decode(file_get_contents("php://input"),true);
if($data){
    $fullname = trim($data['fullname'] ?? '');
    $role     = trim($data['role'] ?? '');
    $email    = trim($data['email'] ?? '');
    $address  = trim($data['address'] ?? '');
    $contact  = trim($data['contact'] ?? '');
    $rawPassword = isset($data['password']) && $data['password'] !== '' ? $data['password'] : bin2hex(random_bytes(4));
    $status   = $data['status'] ?? 'active';

    // Validate allowed staff roles (FireMarshal merged into admin, Accessor merged into CRO)
    $allowedRoles = ['admin', 'inspector', 'cro', 'Chief', 'owner'];
    if (!in_array($role, $allowedRoles)) {
        echo json_encode(['error' => 'Invalid role specified']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email address']);
        exit;
    }

    // Check for duplicate email
    $dup = $conn->prepare("SELECT id FROM user WHERE email = ? OR username = ?");
    $dup->bind_param('ss', $email, $email);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) {
        echo json_encode(['error' => 'An account with this email already exists']);
        $dup->close();
        exit;
    }
    $dup->close();

    // Hash the password before storing
    $password = password_hash($rawPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO user (fullname, email, address, phone_number, role, password, status, username) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssss", $fullname, $email, $address, $contact, $role, $password, $status, $email);
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
        $emailBody = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
            <div style="background-color: #dc3545; padding: 20px; text-align: center;">
                <h2 style="color: #fff; margin: 0;">BFP Site Profiler</h2>
            </div>
            <div style="padding: 30px;">
                <p>Hi <strong>' . htmlspecialchars($fullname) . '</strong>,</p>
                <p>You have been successfully added to the <strong>BFP Site Profiler</strong> system. Below are your login credentials:</p>
                <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <tr>
                        <td style="padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; font-weight: bold; width: 40%;">Username</td>
                        <td style="padding: 10px; border: 1px solid #dee2e6;">' . htmlspecialchars($email) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; font-weight: bold;">Password</td>
                        <td style="padding: 10px; border: 1px solid #dee2e6;">' . htmlspecialchars($rawPassword) . '</td>
                    </tr>
                </table>
                <p style="color: #dc3545;"><strong>Important:</strong> Please change your password after your first login.</p>
                <p>If you have any questions, please contact your administrator.</p>
            </div>
            <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;">
                &copy; BFP Site Profiler &mdash; Bureau of Fire Protection
            </div>
        </div>';
        sendEmail($email, $emailBody, 'Welcome to BFP Site Profiler - Your Account Details');
        
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