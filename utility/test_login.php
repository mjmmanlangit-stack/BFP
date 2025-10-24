<?php
// Test script to debug login issues
header('Content-Type: text/html; charset=utf-8');

include 'db.php';

echo "<h2>Login Debug Test</h2>";
echo "<hr>";

// Get all users
$stmt = $conn->prepare("SELECT id, fullname, email, username, password, role, status FROM user");
$stmt->execute();
$result = $stmt->get_result();

echo "<h3>All Users in Database:</h3>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Username</th><th>Password</th><th>Role</th><th>Status</th><th>Pass Length</th></tr>";

while ($row = $result->fetch_assoc()) {
    $passLength = strlen($row['password']);
    $passDisplay = htmlspecialchars($row['password']);
    $statusColor = strtolower(trim($row['status'])) === 'active' ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['fullname']}</td>";
    echo "<td>{$row['email']}</td>";
    echo "<td>{$row['username']}</td>";
    echo "<td><code>'{$passDisplay}'</code></td>";
    echo "<td>{$row['role']}</td>";
    echo "<td style='color:{$statusColor}'><b>{$row['status']}</b></td>";
    echo "<td>{$passLength}</td>";
    echo "</tr>";
}

echo "</table>";

$stmt->close();

// Test a specific login
echo "<hr>";
echo "<h3>Test Login Form:</h3>";
echo "<form method='POST'>";
echo "Email/Username: <input type='text' name='test_email' value='' size='40'><br><br>";
echo "Password: <input type='text' name='test_password' value='' size='40'><br><br>";
echo "<input type='submit' value='Test Login'>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    $testPassword = $_POST['test_password'];
    
    echo "<hr>";
    echo "<h3>Test Results:</h3>";
    echo "<p><b>Input Email/Username:</b> '{$testEmail}' (length: " . strlen($testEmail) . ")</p>";
    echo "<p><b>Input Password:</b> '{$testPassword}' (length: " . strlen($testPassword) . ")</p>";
    
    $stmt2 = $conn->prepare("SELECT id, username, role, password, email, fullname, status FROM user WHERE email = ? OR username = ?");
    $stmt2->bind_param("ss", $testEmail, $testEmail);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    if ($result2->num_rows > 0) {
        $row = $result2->fetch_assoc();
        echo "<p style='color:green'><b>✓ User Found!</b></p>";
        echo "<p><b>DB Email:</b> '{$row['email']}'</p>";
        echo "<p><b>DB Username:</b> '{$row['username']}'</p>";
        echo "<p><b>DB Password:</b> '{$row['password']}' (length: " . strlen($row['password']) . ")</p>";
        echo "<p><b>DB Role:</b> '{$row['role']}'</p>";
        echo "<p><b>DB Status:</b> '{$row['status']}'</p>";
        
        $dbPasswordTrimmed = trim($row['password']);
        $inputPasswordTrimmed = trim($testPassword);
        
        echo "<hr>";
        echo "<h4>Password Comparison:</h4>";
        echo "<p><b>Input (trimmed):</b> '{$inputPasswordTrimmed}' (length: " . strlen($inputPasswordTrimmed) . ")</p>";
        echo "<p><b>DB (trimmed):</b> '{$dbPasswordTrimmed}' (length: " . strlen($dbPasswordTrimmed) . ")</p>";
        
        if ($inputPasswordTrimmed === $dbPasswordTrimmed) {
            echo "<p style='color:green; font-size:20px'><b>✓ PASSWORDS MATCH! Login should work.</b></p>";
        } else {
            echo "<p style='color:red; font-size:20px'><b>✗ PASSWORDS DO NOT MATCH!</b></p>";
            echo "<p><b>Character-by-character comparison:</b></p>";
            echo "<pre>";
            echo "Input: ";
            for ($i = 0; $i < strlen($inputPasswordTrimmed); $i++) {
                echo "[" . ord($inputPasswordTrimmed[$i]) . "]";
            }
            echo "\nDB:    ";
            for ($i = 0; $i < strlen($dbPasswordTrimmed); $i++) {
                echo "[" . ord($dbPasswordTrimmed[$i]) . "]";
            }
            echo "</pre>";
        }
        
        // Check status
        if (strtolower(trim($row['status'])) !== 'active') {
            echo "<p style='color:orange'><b>⚠ WARNING: Account status is not 'active'!</b></p>";
        }
        
    } else {
        echo "<p style='color:red'><b>✗ User Not Found!</b></p>";
        echo "<p>No user found with email or username: '{$testEmail}'</p>";
    }
    
    $stmt2->close();
}

$conn->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
}
table {
    border-collapse: collapse;
    margin: 20px 0;
}
th {
    background-color: #dc3545;
    color: white;
    padding: 10px;
}
td {
    padding: 8px;
}
code {
    background-color: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
}
</style>
