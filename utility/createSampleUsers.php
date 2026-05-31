<?php
ob_start();
header('Content-Type: text/html; charset=utf-8');

include 'db.php';

$results = [];
$errors = [];

// Function to create a user
function createUser($conn, $fullname, $email, $role, $phone, $address, $rawPassword) {
    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $check->bind_param('s', $email);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $check->close();
        return ['success' => false, 'message' => "Email already exists"];
    }
    $check->close();
    
    // Hash password
    $password = password_hash($rawPassword, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO user (fullname, email, username, role, password, phone_number, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("sssssss", $fullname, $email, $email, $role, $password, $phone, $address);
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'id' => $conn->insert_id, 'message' => "User created successfully"];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => $conn->error];
    }
}

// Create sample accounts
$sampleUsers = [
    [
        'fullname' => 'Juan Inspector',
        'email' => 'inspector@bfp.gov.ph',
        'role' => 'inspector',
        'phone' => '09123456789',
        'address' => 'Fire Station 1, Manila',
        'password' => 'inspector123'
    ],
    [
        'fullname' => 'Maria Chief',
        'email' => 'chief@bfp.gov.ph',
        'role' => 'Chief',
        'phone' => '09123456790',
        'address' => 'BFP Headquarters, Manila',
        'password' => 'chief123'
    ],
    [
        'fullname' => 'Pedro CRO',
        'email' => 'cro@bfp.gov.ph',
        'role' => 'cro',
        'phone' => '09123456791',
        'address' => 'Compliance Records Office, Manila',
        'password' => 'cro123'
    ],
    [
        'fullname' => 'Rosa Owner',
        'email' => 'owner@bfp.gov.ph',
        'role' => 'owner',
        'phone' => '09123456792',
        'address' => '123 Business Street, Manila',
        'password' => 'owner123'
    ]
];

foreach ($sampleUsers as $user) {
    $result = createUser($conn, $user['fullname'], $user['email'], $user['role'], $user['phone'], $user['address'], $user['password']);
    $results[] = [
        'email' => $user['email'],
        'role' => $user['role'],
        'password' => $user['password'],
        'result' => $result
    ];
}

// Now fetch all users to display
$stmt = $conn->prepare("SELECT id, fullname, email, username, role, status, createdAt FROM user ORDER BY role, fullname");
$stmt->execute();
$allUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create Sample Users - BFP Site Profiler</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: white; margin-bottom: 30px; text-align: center; }
        .creation-results { 
            background: white; 
            padding: 25px; 
            border-radius: 8px; 
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .creation-results h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .user-creation {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .user-card {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 4px;
            font-size: 14px;
        }
        .user-card.success {
            border-left-color: #28a745;
            background: #f0f8f4;
        }
        .user-card.error {
            border-left-color: #dc3545;
            background: #fef2f2;
        }
        .user-card .role {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 12px;
        }
        .user-card .status {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 12px;
        }
        .user-card .status.success {
            color: #28a745;
        }
        .user-card .status.error {
            color: #dc3545;
        }
        .all-users {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .all-users h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table thead {
            background: #667eea;
            color: white;
        }
        table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        table tbody tr:hover {
            background: #f0f0f0;
        }
        .role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .role-admin { background: #fff3cd; color: #856404; }
        .role-inspector { background: #d1ecf1; color: #0c5460; }
        .role-chief { background: #d4edda; color: #155724; }
        .role-cro { background: #f8d7da; color: #721c24; }
        .role-owner { background: #e7d4f5; color: #4a148c; }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .credentials-section {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 13px;
        }
        .credentials-section h3 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .credential {
            background: white;
            padding: 10px;
            margin: 8px 0;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
        }
        .credential-label {
            font-weight: bold;
            color: #333;
        }
        .success-icon { color: #28a745; }
        .error-icon { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✨ Creating Sample User Accounts</h1>
        
        <div class="creation-results">
            <h2>📝 Account Creation Results</h2>
            <div class="user-creation">
                <?php foreach ($results as $result): ?>
                <div class="user-card <?php echo $result['result']['success'] ? 'success' : 'error'; ?>">
                    <div class="role"><?php echo ucfirst($result['role']); ?></div>
                    <div><?php echo htmlspecialchars($result['email']); ?></div>
                    <div class="status <?php echo $result['result']['success'] ? 'success' : 'error'; ?>">
                        <?php echo $result['result']['success'] ? '✓ ' : '✗ '; ?>
                        <?php echo htmlspecialchars($result['result']['message']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="all-users">
            <h2>👥 All Users in Database</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allUsers as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo strtolower($user['role']); ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($user['status']); ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($user['createdAt']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="credentials-section">
                <h3>🔑 Test Account Credentials</h3>
                <p style="margin-bottom: 10px;"><strong>Use these credentials to test each role:</strong></p>
                
                <div class="credential">
                    <span class="credential-label">Admin:</span> admin / admin
                </div>
                <div class="credential">
                    <span class="credential-label">Inspector:</span> inspector@bfp.gov.ph / inspector123
                </div>
                <div class="credential">
                    <span class="credential-label">Chief:</span> chief@bfp.gov.ph / chief123
                </div>
                <div class="credential">
                    <span class="credential-label">CRO:</span> cro@bfp.gov.ph / cro123
                </div>
                <div class="credential">
                    <span class="credential-label">Owner:</span> owner@bfp.gov.ph / owner123
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```
