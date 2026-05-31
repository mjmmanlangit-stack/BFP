<?php
ob_start();
ini_set('display_errors', 0);
session_start();
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include_once 'db.php';

$ownerId = $_SESSION['user'];

try {
    // Total establishments owned
    $r1 = $conn->query("SELECT COUNT(*) as total FROM establishment WHERE owner_id = $ownerId");
    $total = $r1->fetch_assoc()['total'];

    // Inspections scheduled (upcoming)
    $r2 = $conn->query("SELECT COUNT(*) as cnt FROM inspection i
                        INNER JOIN establishment e ON i.establishment_id = e.id
                        WHERE e.owner_id = $ownerId AND i.status IN ('pending','scheduled')");
    $scheduled = $r2->fetch_assoc()['cnt'];

    // Completed inspections
    $r3 = $conn->query("SELECT COUNT(*) as cnt FROM inspection i
                        INNER JOIN establishment e ON i.establishment_id = e.id
                        WHERE e.owner_id = $ownerId AND i.status = 'completed'");
    $completed = $r3->fetch_assoc()['cnt'];

    // Active certificates
    $r4 = $conn->query("SELECT COUNT(*) as cnt FROM certificates c
                        INNER JOIN establishment e ON c.establishment_id = e.id
                        WHERE e.owner_id = $ownerId AND c.status = 'authorized'
                        AND (c.expiry_date IS NULL OR c.expiry_date >= CURDATE())");
    $activeCerts = $r4->fetch_assoc()['cnt'];

    // Upcoming inspections (next 30 days) — include inspector names
    $upcoming = [];
    $r5 = $conn->query("SELECT i.id, i.inspection_date AS scheduled_date, i.time_slot, i.status,
                               e.name AS establishment_name, e.address,
                               u1.fullname AS inspector1_name,
                               u2.fullname AS inspector2_name
                        FROM inspection i
                        INNER JOIN establishment e ON i.establishment_id = e.id
                        LEFT JOIN user u1 ON i.inspector1 = u1.id
                        LEFT JOIN user u2 ON i.inspector2 = u2.id
                        WHERE e.owner_id = $ownerId
                          AND i.inspection_date >= NOW()
                          AND i.inspection_date <= DATE_ADD(NOW(), INTERVAL 30 DAY)
                        ORDER BY i.inspection_date ASC LIMIT 5");
    while ($row = $r5->fetch_assoc()) {
        $upcoming[] = $row;
    }

    // Recent notifications: payment status changes and inspection updates
    $notifications = [];
    $r7 = $conn->query("SELECT i.id, i.status, i.payment, i.payment_confirmed_at, i.createdAt,
                               e.name AS establishment_name,
                               CASE
                                 WHEN i.payment = 1 AND i.payment_confirmed_at IS NOT NULL THEN 'Payment Confirmed'
                                 WHEN i.payment = 1 THEN 'Payment Approved'
                                 WHEN i.status = 'scheduled' THEN 'Inspection Scheduled'
                                 WHEN i.status = 'completed' THEN 'Inspection Completed'
                                 WHEN i.status = 'pending' THEN 'Inspection Pending'
                                 ELSE CONCAT('Status: ', i.status)
                               END AS title,
                               COALESCE(i.payment_confirmed_at, i.createdAt) AS event_date
                        FROM inspection i
                        INNER JOIN establishment e ON i.establishment_id = e.id
                        WHERE e.owner_id = $ownerId
                        ORDER BY COALESCE(i.payment_confirmed_at, i.createdAt) DESC
                        LIMIT 10");
    while ($row = $r7->fetch_assoc()) {
        $notifications[] = $row;
    }

    // Certificates with expiry info
    $certs = [];
    $r6 = $conn->query("SELECT c.certificate_number, c.authorized_at, c.expiry_date, c.status,
                               e.name as establishment_name
                        FROM certificates c
                        INNER JOIN establishment e ON c.establishment_id = e.id
                        WHERE e.owner_id = $ownerId
                        ORDER BY c.authorized_at DESC LIMIT 5");
    while ($row = $r6->fetch_assoc()) {
        $certs[] = $row;
    }

    echo json_encode([
        'success'               => true,
        'total_establishments'  => (int)$total,
        'scheduled_inspections' => (int)$scheduled,
        'completed_inspections' => (int)$completed,
        'active_certificates'   => (int)$activeCerts,
        'upcoming'              => $upcoming,
        'certificates'          => $certs,
        'notifications'         => $notifications
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
