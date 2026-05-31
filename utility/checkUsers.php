<?php
ob_start();
header('Content-Type: text/html; charset=utf-8');

include 'db.php';

// Query all users
$stmt = $conn->prepare("SELECT id, fullname, email, username, role, status, createdAt FROM user ORDER BY role, fullname");
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

// Count users by role
$countByRole = [];
foreach ($users as $user) {
    $role = $user['role'];
    if (!isset($countByRole[$role])) {
        $countByRole[$role] = 0;
    }
    $countByRole[$role]++;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>BFP Site Profiler - User Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 3px solid #dc3545; padding-bottom: 10px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
        .summary-card { background: #f9f9f9; border-left: 4px solid #dc3545; padding: 15px; border-radius: 4px; }
        .summary-card h3 { margin: 0 0 5px 0; color: #666; font-size: 12px; text-transform: uppercase; }
        .summary-card .count { font-size: 28px; font-weight: bold; color: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table thead { background: #dc3545; color: white; }
        table th { padding: 12px; text-align: left; font-weight: bold; }
        table td { padding: 10px; border-bottom: 1px solid #ddd; }
        table tbody tr:nth-child(even) { background: #f9f9f9; }
        table tbody tr:hover { background: #f0f0f0; }
        .role-admin { background: #fff3cd; color: #856404; }
        .role-inspector { background: #d1ecf1; color: #0c5460; }
        .role-chief { background: #d4edda; color: #155724; }
        .role-cro { background: #f8d7da; color: #721c24; }
        .role-owner { background: #e7d4f5; color: #4a148c; }
        .status-active { color: #28a745; font-weight: bold; }
        .status-inactive { color: #dc3545; font-weight: bold; }
        .role-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 BFP Site Profiler - User Account Check</h1>
        
        <div class="summary">
            <div class="summary-card">
                <h3>Total Users</h3>
                <div class="count"><?php echo count($users); ?></div>
            </div>
            <?php foreach ($countByRole as $role => $count): ?>
            <div class="summary-card">
                <h3><?php echo ucfirst($role); ?></h3>
                <div class="count"><?php echo $count; ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($users) > 0): ?>
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
                <?php foreach ($users as $user): ?>
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
                        <span class="status-<?php echo strtolower($user['status']); ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($user['createdAt']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-top: 20px;">
            ⚠️ No users found in the database!
        </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #0066cc; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #0066cc;">📋 Required Accounts</h3>
            <ul style="margin: 10px 0;">
                <li><strong>Admin:</strong> System administrator (for managing the system)</li>
                <li><strong>Inspector:</strong> Conducts fire safety inspections</li>
                <li><strong>Chief:</strong> Endorses inspection reports</li>
                <li><strong>CRO:</strong> Compliance Records Officer</li>
                <li><strong>Owner:</strong> Establishment owners (can register and manage properties)</li>
            </ul>
        </div>
    </div>
</body>
</html>
```
