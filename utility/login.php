<?php
// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    // Trim whitespace from input
    $username = trim($data["username"]);
    $password = trim($data["password"]);

    // Query supports both email and username fields
    $stmt = $conn->prepare("SELECT id, username, role, password, email, fullname, status FROM user WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Trim password from database for comparison
        $dbPassword = trim($row["password"]);
        
        // Check if user is active
        if (strtolower(trim($row["status"])) !== "active") {
            // Log failed login attempt - inactive account
            $activityLogger->log(
                $row['id'],
                'user_login_failed',
                'login',
                'authentication',
                'Failed login attempt - Account is inactive',
                null,
                null,
                'failed',
                'Account is inactive'
            );
            echo json_encode(["error" => "Account is inactive. Please contact administrator."]);
        } elseif ($password === $dbPassword) {
            session_start();
            $_SESSION['user'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['fullname'] = $row['fullname'];
            
            // Log successful login
            $activityLogger->logLogin($row['id'], true);
            
            echo json_encode([
                "success" => "Login successful", 
                "user" => $row["email"], 
                "id" => $row['id'], 
                "role" => $row['role'],
                "fullname" => $row['fullname']
            ]);
        } else {
            // Password mismatch - Log failed attempt
            $activityLogger->log(
                $row['id'],
                'user_login_failed',
                'login',
                'authentication',
                'Failed login attempt - Invalid password for user: ' . $username,
                null,
                null,
                'failed',
                'Invalid password'
            );
            echo json_encode(["error" => "Invalid password. Please check your password and try again."]);
        }
    } else {
        echo json_encode(["error" => "User not found. Please check your email/username."]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "No data received"]);
}

$conn->close();
exit;
?>
