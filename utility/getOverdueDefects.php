<?php
ob_start();
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$allowedRoles = ['admin', 'cro', 'chief', 'inspector'];
if (!in_array(strtolower($_SESSION['role']), $allowedRoles)) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

include_once 'db.php';

$userId = (int) $_SESSION['user'];
$role   = strtolower($_SESSION['role']);

// Overdue defects — grace_period < today and still pending
$whereInspector = '';
$params = [];
$types  = '';

if ($role === 'inspector') {
    $whereInspector = ' AND (i.inspector = ? OR i.inspector1 = ? OR i.inspector2 = ?)';
    $params = [$userId, $userId, $userId];
    $types  = 'iii';
}

$sql = "SELECT
    d.id              AS defect_id,
    d.defects_details AS description,
    d.grace_period,
    d.status          AS defect_status,
    DATEDIFF(CURDATE(), d.grace_period) AS days_overdue,
    r.id              AS report_id,
    r.compliance_status,
    i.id              AS inspection_id,
    i.inspection_date,
    e.id              AS establishment_id,
    e.name            AS establishment_name,
    e.address,
    e.type            AS establishment_type,
    u_owner.fullname  AS owner_name,
    u_owner.email     AS owner_email,
    u1.fullname       AS inspector1_name,
    u2.fullname       AS inspector2_name
FROM defects d
JOIN reports r ON r.id = d.report_id
JOIN inspection i ON i.id = r.inspection_id
JOIN establishment e ON e.id = i.establishment_id
JOIN user u_owner ON u_owner.id = e.owner_id
LEFT JOIN user u1 ON u1.id = i.inspector1
LEFT JOIN user u2 ON u2.id = i.inspector2
WHERE d.status = 'pending'
  AND d.grace_period < CURDATE()
  AND d.grace_period IS NOT NULL
  AND d.grace_period != ''
$whereInspector
ORDER BY d.grace_period ASC
LIMIT 100";

try {
    if ($types && count($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    $overdueDefects = [];
    while ($row = $result->fetch_assoc()) {
        $overdueDefects[] = [
            'defectId'          => (int) $row['defect_id'],
            'description'       => $row['description'],
            'gracePeriod'       => $row['grace_period'],
            'daysOverdue'       => (int) $row['days_overdue'],
            'defectStatus'      => $row['defect_status'],
            'reportId'          => (int) $row['report_id'],
            'complianceStatus'  => $row['compliance_status'],
            'inspectionId'      => (int) $row['inspection_id'],
            'inspectionDate'    => $row['inspection_date'],
            'establishmentId'   => (int) $row['establishment_id'],
            'establishmentName' => $row['establishment_name'],
            'address'           => $row['address'],
            'establishmentType' => $row['establishment_type'],
            'ownerName'         => $row['owner_name'],
            'ownerEmail'        => $row['owner_email'],
            'inspector1'        => $row['inspector1_name'],
            'inspector2'        => $row['inspector2_name'],
        ];
    }

    // Also get compliance trend (last 6 months)
    $trendSql = "SELECT
        DATE_FORMAT(r.finalized_at, '%Y-%m') AS month,
        SUM(CASE WHEN r.compliance_status = 'compliant' THEN 1 ELSE 0 END) AS compliant,
        SUM(CASE WHEN r.compliance_status = 'partially_compliant' THEN 1 ELSE 0 END) AS partially_compliant,
        SUM(CASE WHEN r.compliance_status = 'non_compliant' THEN 1 ELSE 0 END) AS non_compliant
    FROM reports r
    WHERE r.finalized_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(r.finalized_at, '%Y-%m')
    ORDER BY month ASC";
    $trendResult = $conn->query($trendSql);
    $trend = [];
    while ($row = $trendResult->fetch_assoc()) {
        $trend[] = $row;
    }

    // Summary counts
    $totalOverdue     = count($overdueDefects);
    $establishmentIds = array_unique(array_column($overdueDefects, 'establishmentId'));
    $affectedCount    = count($establishmentIds);

    echo json_encode([
        'success'        => true,
        'totalOverdue'   => $totalOverdue,
        'affectedEstablishments' => $affectedCount,
        'overdueDefects' => $overdueDefects,
        'complianceTrend'=> $trend,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
