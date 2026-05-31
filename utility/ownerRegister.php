<?php
ob_start();
ini_set('display_errors', 0);

ob_clean();
header('Content-Type: application/json');

include 'db.php';
include 'mailer.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "No data received"]);
    exit;
}

$fullname    = trim($data['fullname'] ?? '');
$email       = trim($data['email'] ?? '');
$rawPassword = trim($data['password'] ?? '');
$address     = trim($data['address'] ?? '');
$contact     = trim($data['contact'] ?? '');

if (!$fullname || !$email || !$rawPassword) {
    echo json_encode(["error" => "Full name, email, and password are required."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email address."]);
    exit;
}

// Check if email already exists
$check = $conn->prepare("SELECT id FROM user WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(["error" => "An account with this email already exists."]);
    $check->close();
    exit;
}
$check->close();

// Generate email verification token
$token = bin2hex(random_bytes(32));
$tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Hash password before storing
$password = password_hash($rawPassword, PASSWORD_DEFAULT);

// Insert new user with status 'pending' until email is verified
$stmt = $conn->prepare(
    "INSERT INTO user (fullname, email, username, address, phone_number, role, password, status, verification_token, token_expiry)
     VALUES (?, ?, ?, ?, ?, 'owner', ?, 'pending', ?, ?)"
);
$stmt->bind_param(
    "ssssssss",
    $fullname, $email, $email, $address, $contact, $password, $token, $tokenExpiry
);

if ($stmt->execute()) {
    $userId = $conn->insert_id;

    // Build verification link
    $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $verifyUrl = $protocol . '://' . $host . '/BFP-Site-Profiler/utility/verifyEmail.php?token=' . $token;

    $emailBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #dc3545, #a02834); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
            <h1 style="color: white; margin: 0;">BFP Site Profiler</h1>
            <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0 0;">Bureau of Fire Protection</p>
        </div>
        <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;">
            <h2 style="color: #333;">Welcome, ' . htmlspecialchars($fullname) . '!</h2>
            <p>Thank you for registering with the BFP Site Profiler system. Please verify your email address to activate your account.</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . $verifyUrl . '"
                   style="background: #dc3545; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 16px;">
                    Verify Email Address
                </a>
            </div>
            <p style="color: #666; font-size: 0.9em;">This link expires in 24 hours. If you did not register, please ignore this email.</p>
            <hr style="border: 1px solid #ddd;">
            <p style="color: #999; font-size: 0.8em; text-align: center;">&copy; ' . date('Y') . ' Bureau of Fire Protection. All rights reserved.</p>
        </div>
    </div>';

    $sent = sendEmail($email, $emailBody, 'Verify Your BFP Site Profiler Account');

    echo json_encode([
        "success" => true,
        "message" => "Registration successful! Please check your email to verify your account.",
        "email_sent" => $sent
    ]);
} else {
    echo json_encode(["error" => "Registration failed. " . $stmt->error]);
}

$stmt->close();
$conn->close();
exit;
?>
