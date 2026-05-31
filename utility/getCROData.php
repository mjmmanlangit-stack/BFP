<?php
ob_start();
ini_set('display_errors', 0);
session_start();
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'cro') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include_once 'db.php';

try {
    // Total registered establishments
    $r1 = $conn->query("SELECT COUNT(*) as total FROM establishment");
    $total = $r1->fetch_assoc()['total'];

    // Pending verification (payment not confirmed)
    $r2 = $conn->query("SELECT COUNT(*) as cnt FROM inspection WHERE payment = 0");
    $pending = $r2->fetch_assoc()['cnt'];

    // Payment confirmed
    $r3 = $conn->query("SELECT COUNT(*) as cnt FROM inspection WHERE payment = 1");
    $confirmed = $r3->fetch_assoc()['cnt'];

    // Pending document reviews
    $r4 = $conn->query("SELECT COUNT(*) as cnt FROM documents WHERE status = 'pending'");
    $pendingDocs = $r4->fetch_assoc()['cnt'];

    // Recent establishments needing review
    $recent = [];
    $r5 = $conn->query("SELECT e.id, e.name, e.type, e.address, e.status, e.createdAt,
                               u.fullname as owner_name, u.email as owner_email,
                               i.id as inspection_id, i.payment, i.status as inspection_status
                        FROM establishment e
                        INNER JOIN user u ON e.owner_id = u.id
                        LEFT JOIN inspection i ON i.establishment_id = e.id
                        ORDER BY e.createdAt DESC LIMIT 10");
    while ($row = $r5->fetch_assoc()) {
        $recent[] = $row;
    }

    // Establishments by type for chart
    $byType = [];
    $r6 = $conn->query("SELECT type, COUNT(*) as cnt FROM establishment GROUP BY type ORDER BY cnt DESC");
    while ($row = $r6->fetch_assoc()) {
        $byType[] = $row;
    }

    echo json_encode([
        'success'     => true,
        'total'       => (int)$total,
        'pending'     => (int)$pending,
        'confirmed'   => (int)$confirmed,
        'pendingDocs' => (int)$pendingDocs,
        'recent'      => $recent,
        'byType'      => $byType
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
