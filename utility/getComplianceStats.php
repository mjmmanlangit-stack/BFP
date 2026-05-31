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

// Overall compliance stats
$totalSql = "SELECT
    COUNT(*) AS total_reports,
    SUM(CASE WHEN compliance_status = 'compliant' THEN 1 ELSE 0 END) AS compliant,
    SUM(CASE WHEN compliance_status = 'partially_compliant' THEN 1 ELSE 0 END) AS partially_compliant,
    SUM(CASE WHEN compliance_status = 'non_compliant' THEN 1 ELSE 0 END) AS non_compliant,
    SUM(CASE WHEN compliance_status IS NULL OR compliance_status = '' THEN 1 ELSE 0 END) AS pending_finalization
FROM reports";
$totalResult = $conn->query($totalSql);
$totals      = $totalResult->fetch_assoc();

$totalReports = (int) $totals['total_reports'];
$compRate     = $totalReports > 0
    ? round(((int)$totals['compliant'] / $totalReports) * 100, 1)
    : 0;

// Compliance rate by establishment type
$byTypeSql = "SELECT
    e.type AS establishment_type,
    COUNT(r.id) AS total_inspected,
    SUM(CASE WHEN r.compliance_status = 'compliant' THEN 1 ELSE 0 END) AS compliant,
    SUM(CASE WHEN r.compliance_status = 'partially_compliant' THEN 1 ELSE 0 END) AS partially_compliant,
    SUM(CASE WHEN r.compliance_status = 'non_compliant' THEN 1 ELSE 0 END) AS non_compliant
FROM establishment e
JOIN inspection i ON i.establishment_id = e.id
JOIN reports r ON r.inspection_id = i.id
WHERE r.compliance_status IS NOT NULL
GROUP BY e.type
ORDER BY total_inspected DESC";
$byTypeResult = $conn->query($byTypeSql);
$byType = [];
while ($row = $byTypeResult->fetch_assoc()) {
    $row['compliance_rate'] = $row['total_inspected'] > 0
        ? round(($row['compliant'] / $row['total_inspected']) * 100, 1) : 0;
    $byType[] = $row;
}

// Monthly compliance trend (last 12 months)
$trendSql = "SELECT
    DATE_FORMAT(r.finalized_at, '%Y-%m') AS month,
    COUNT(*) AS total,
    SUM(CASE WHEN r.compliance_status = 'compliant' THEN 1 ELSE 0 END) AS compliant,
    SUM(CASE WHEN r.compliance_status = 'partially_compliant' THEN 1 ELSE 0 END) AS partially_compliant,
    SUM(CASE WHEN r.compliance_status = 'non_compliant' THEN 1 ELSE 0 END) AS non_compliant
FROM reports r
WHERE r.finalized_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
  AND r.finalized_at IS NOT NULL
GROUP BY DATE_FORMAT(r.finalized_at, '%Y-%m')
ORDER BY month ASC";
$trendResult = $conn->query($trendSql);
$monthlyTrend = [];
while ($row = $trendResult->fetch_assoc()) {
    $row['compliance_rate'] = $row['total'] > 0
        ? round(($row['compliant'] / $row['total']) * 100, 1) : 0;
    $monthlyTrend[] = $row;
}

// Overdue defects count
$overdueCount = 0;
$overdueResult = $conn->query(
    "SELECT COUNT(*) AS c FROM defects WHERE status = 'pending' AND grace_period < CURDATE()"
);
if ($overdueResult) {
    $overdueCount = (int) $overdueResult->fetch_assoc()['c'];
}

// Establishments with no inspection in last 12 months
$uninspectedResult = $conn->query(
    "SELECT COUNT(DISTINCT e.id) AS c
     FROM establishment e
     WHERE e.status = 'active'
       AND NOT EXISTS (
           SELECT 1 FROM inspection i
           WHERE i.establishment_id = e.id
             AND i.inspection_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
       )"
);
$uninspectedCount = $uninspectedResult ? (int) $uninspectedResult->fetch_assoc()['c'] : 0;

echo json_encode([
    'success'             => true,
    'overallCompliance'   => [
        'total'               => $totalReports,
        'compliant'           => (int) $totals['compliant'],
        'partially_compliant' => (int) $totals['partially_compliant'],
        'non_compliant'       => (int) $totals['non_compliant'],
        'pending'             => (int) $totals['pending_finalization'],
        'compliance_rate'     => $compRate,
    ],
    'byEstablishmentType' => $byType,
    'monthlyTrend'        => $monthlyTrend,
    'overdueDefects'      => $overdueCount,
    'uninspectedEstablishments' => $uninspectedCount,
]);
