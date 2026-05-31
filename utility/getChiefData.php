<?php
ob_start();
ini_set('display_errors', 0);
session_start();
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'chief') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include_once 'db.php';

try {
    // Total inspections
    $r1 = $conn->query("SELECT COUNT(*) as total FROM inspection");
    $total = $r1->fetch_assoc()['total'];

    // Pending (not yet started)
    $r2 = $conn->query("SELECT COUNT(*) as cnt FROM inspection WHERE status IN ('pending','status-pen')");
    $pending = $r2->fetch_assoc()['cnt'];

    // Scheduled this week
    $r3 = $conn->query("SELECT COUNT(*) as cnt FROM inspection
                        WHERE inspection_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
                          AND status = 'scheduled'");
    $thisWeek = $r3->fetch_assoc()['cnt'];

    // Active inspectors
    $r4 = $conn->query("SELECT COUNT(*) as cnt FROM user WHERE role = 'inspector' AND status = 'active'");
    $activeInspectors = $r4->fetch_assoc()['cnt'];

    // Pending endorsements (reports awaiting Chief review)
    $r5 = $conn->query("SELECT COUNT(*) as cnt FROM reports WHERE endorsement_status = 'pending'");
    $pendingEndorsements = $r5->fetch_assoc()['cnt'];

    // Upcoming inspections
    $upcoming = [];
    $r6 = $conn->query("SELECT i.id, i.inspection_date, i.time_slot, i.status, i.priority_level,
                               e.name as establishment_name, e.type as establishment_type,
                               u1.fullname as inspector1_name, u2.fullname as inspector2_name
                        FROM inspection i
                        INNER JOIN establishment e ON i.establishment_id = e.id
                        LEFT JOIN user u1 ON i.inspector1 = u1.id
                        LEFT JOIN user u2 ON i.inspector2 = u2.id
                        WHERE i.inspection_date >= NOW()
                        ORDER BY i.inspection_date ASC LIMIT 8");
    while ($row = $r6->fetch_assoc()) {
        $upcoming[] = $row;
    }

    echo json_encode([
        'success'             => true,
        'total'               => (int)$total,
        'pending'             => (int)$pending,
        'thisWeek'            => (int)$thisWeek,
        'activeInspectors'    => (int)$activeInspectors,
        'pendingEndorsements' => (int)$pendingEndorsements,
        'upcoming'            => $upcoming
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
