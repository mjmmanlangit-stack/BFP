<?php
// Get all activity logs with user information
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

    // Get limit and offset from query params
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    // Query to get all activity logs with user information
    $query = "
        SELECT 
            al.*,
            u.fullname as user_fullname,
            u.email as user_email,
            u.role as user_role
        FROM activity_log al
        LEFT JOIN user u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $activities = [];
    while ($row = $result->fetch_assoc()) {
        // Parse JSON fields if they are strings
        if ($row['old_values'] && is_string($row['old_values'])) {
            $row['old_values'] = json_decode($row['old_values'], true);
        }
        if ($row['new_values'] && is_string($row['new_values'])) {
            $row['new_values'] = json_decode($row['new_values'], true);
        }
        
        $activities[] = $row;
    }

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM activity_log";
    $countResult = $conn->query($countQuery);
    $totalCount = $countResult->fetch_assoc()['total'];

    echo json_encode([
        'success' => true,
        'activities' => $activities,
        'total' => $totalCount,
        'limit' => $limit,
        'offset' => $offset
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
