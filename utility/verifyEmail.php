<?php
session_start();
include 'db.php';

$token = trim($_GET['token'] ?? '');

if (empty($token)) {
    header('Location: /BFP-Site-Profiler/html/index.php?error=invalid_token');
    exit;
}

// Find user with matching valid token
$stmt = $conn->prepare(
    "SELECT id, fullname, email, token_expiry FROM user WHERE verification_token = ? AND status = 'pending' LIMIT 1"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: /BFP-Site-Profiler/html/index.php?error=invalid_token');
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Check token expiry
if (strtotime($user['token_expiry']) < time()) {
    // Token expired — delete pending user
    $del = $conn->prepare("DELETE FROM user WHERE id = ?");
    $del->bind_param("i", $user['id']);
    $del->execute();
    $del->close();
    header('Location: /BFP-Site-Profiler/html/owner/register.php?error=token_expired');
    exit;
}

// Activate the account
$update = $conn->prepare(
    "UPDATE user SET status = 'active', verification_token = NULL, token_expiry = NULL WHERE id = ?"
);
$update->bind_param("i", $user['id']);
$update->execute();
$update->close();

// Log them in automatically
$_SESSION['user']     = $user['id'];
$_SESSION['role']     = 'owner';
$_SESSION['email']    = $user['email'];
$_SESSION['fullname'] = $user['fullname'];

$conn->close();

// Redirect to owner dashboard
header('Location: /BFP-Site-Profiler/html/owner/dashboard.php?verified=1');
exit;
?>
