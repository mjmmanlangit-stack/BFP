<?php
// Get activity log statistics
ob_start();
ob_clean();
header('Content-Type: application/json');

session_start();
include_once 'db.php';

try {
    // Check if user is admin
    if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stats = [];

    // Total activities today
    $query = "SELECT COUNT(*) as count FROM activity_log WHERE DATE(created_at) = CURDATE()";
    $result = $conn->query($query);
    $stats['total_today'] = $result->fetch_assoc()['count'];

    // Total logins today
    $query = "SELECT COUNT(*) as count FROM activity_log 
              WHERE DATE(created_at) = CURDATE() 
              AND action_type = 'login' 
              AND status = 'success'";
    $result = $conn->query($query);
    $stats['logins_today'] = $result->fetch_assoc()['count'];

    // Failed logins today
    $query = "SELECT COUNT(*) as count FROM activity_log 
              WHERE DATE(created_at) = CURDATE() 
              AND action_type = 'login' 
              AND status = 'failed'";
    $result = $conn->query($query);
    $stats['failed_logins_today'] = $result->fetch_assoc()['count'];

    // Active users today (users who performed any action)
    $query = "SELECT COUNT(DISTINCT user_id) as count FROM activity_log 
              WHERE DATE(created_at) = CURDATE()";
    $result = $conn->query($query);
    $stats['active_users_today'] = $result->fetch_assoc()['count'];

    // Activities by action type (last 7 days)
    $query = "SELECT action_type, COUNT(*) as count 
              FROM activity_log 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
              GROUP BY action_type";
    $result = $conn->query($query);
    $stats['by_action_type'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['by_action_type'][$row['action_type']] = $row['count'];
    }

    // Activities by module (last 7 days)
    $query = "SELECT module, COUNT(*) as count 
              FROM activity_log 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
              GROUP BY module";
    $result = $conn->query($query);
    $stats['by_module'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['by_module'][$row['module']] = $row['count'];
    }

    // Most active users (last 7 days)
    $query = "SELECT u.fullname, u.role, COUNT(*) as activity_count 
              FROM activity_log al
              LEFT JOIN user u ON al.user_id = u.id
              WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
              GROUP BY al.user_id, u.fullname, u.role
              ORDER BY activity_count DESC
              LIMIT 5";
    $result = $conn->query($query);
    $stats['most_active_users'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['most_active_users'][] = $row;
    }

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
