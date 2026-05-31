<?php
ob_start();
ini_set('display_errors', 0);
session_start();
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include_once 'db.php';

// Self-migrate: ensure endorsement columns exist on reports
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsement_status VARCHAR(20) DEFAULT 'pending'");
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsement_notes TEXT DEFAULT NULL");
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsed_by INT(11) DEFAULT NULL");
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsed_at DATETIME DEFAULT NULL");

$role    = strtolower($_SESSION['role']);
$userId  = $_SESSION['user'];
$allowed = ['chief', 'inspector', 'cro', 'owner', 'admin'];

if (!in_array($role, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    // Base query - filter by role
    $whereClause = '';
    $params      = [];
    $types       = '';

    if ($role === 'owner') {
        // Owner sees only their own establishments' inspection history
        $whereClause = ' WHERE e.owner_id = ?';
        $params[]    = $userId;
        $types       = 'i';
    } elseif ($role === 'inspector') {
        // Inspector sees only their assigned inspections (all three assignment columns)
        $whereClause = ' WHERE (i.inspector = ? OR i.inspector1 = ? OR i.inspector2 = ?)';
        $params      = [$userId, $userId, $userId];
        $types       = 'iii';
    }
    // Chief, CRO, Admin see all

    $sql = "SELECT
                i.id as inspection_id,
                i.inspection_date,
                i.time_slot,
                i.inspection_type,
                i.priority_level,
                i.status as inspection_status,
                i.payment,
                e.id as establishment_id,
                e.name as establishment_name,
                e.type as establishment_type,
                e.address,
                e.registration_no,
                u_owner.fullname as owner_name,
                u1.fullname as inspector1_name,
                u2.fullname as inspector2_name,
                r.id as report_id,
                r.compliance_status,
                r.endorsement_status,
                r.finalized_at,
                c.certificate_number,
                c.status as cert_status,
                c.expiry_date
            FROM inspection i
            INNER JOIN establishment e ON i.establishment_id = e.id
            INNER JOIN user u_owner ON e.owner_id = u_owner.id
            LEFT JOIN user u1 ON i.inspector1 = u1.id
            LEFT JOIN user u2 ON i.inspector2 = u2.id
            LEFT JOIN reports r ON r.inspection_id = i.id
            LEFT JOIN certificates c ON c.inspection_id = i.id
            $whereClause
            ORDER BY i.inspection_date DESC
            LIMIT 100";

    if ($types && count($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    echo json_encode(['success' => true, 'history' => $history]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
