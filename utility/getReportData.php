<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);
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

function safeQuery($conn, $sql) {
    $result = $conn->query($sql);
    if ($result === false) {
        error_log("getReportData.php SQL error: " . $conn->error . " | SQL: " . $sql);
        return null;
    }
    return $result;
}

// Monthly inspection trend (last 12 months)
$trendSql = "SELECT
    DATE_FORMAT(inspection_date, '%Y-%m') AS month,
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'completed' OR status = 'approved' THEN 1 ELSE 0 END) AS completed,
    SUM(CASE WHEN status = 'scheduled' OR status = 'status-pen' THEN 1 ELSE 0 END) AS scheduled
FROM inspection
WHERE inspection_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(inspection_date, '%Y-%m')
ORDER BY month ASC";

$monthlyTrend = [];
$trendResult = safeQuery($conn, $trendSql);
if ($trendResult) {
    while ($row = $trendResult->fetch_assoc()) {
        $monthlyTrend[] = $row;
    }
}

// Compliance distribution
$compSql = "SELECT compliance_status, COUNT(*) AS count
FROM reports
WHERE compliance_status IS NOT NULL
GROUP BY compliance_status";
$complianceDist = ['compliant' => 0, 'partially_compliant' => 0, 'non_compliant' => 0];
$compResult = safeQuery($conn, $compSql);
if ($compResult) {
    while ($row = $compResult->fetch_assoc()) {
        if (isset($complianceDist[$row['compliance_status']])) {
            $complianceDist[$row['compliance_status']] = (int) $row['count'];
        }
    }
}

// Compliance rate
$totalComp = array_sum($complianceDist);
$compRate  = $totalComp > 0 ? round(($complianceDist['compliant'] / $totalComp) * 100, 1) : 0;

// Inspection type breakdown
$inspectionTypes = [];
$typeResult = safeQuery($conn, "SELECT inspection_type, COUNT(*) AS count FROM inspection GROUP BY inspection_type ORDER BY count DESC");
if ($typeResult) {
    while ($row = $typeResult->fetch_assoc()) {
        $inspectionTypes[] = $row;
    }
}

// Establishment type breakdown with compliance
$estTypeSql = "SELECT
    e.type AS establishment_type,
    COUNT(DISTINCT e.id) AS total_establishments,
    SUM(CASE WHEN r.compliance_status = 'compliant' THEN 1 ELSE 0 END) AS compliant,
    SUM(CASE WHEN r.compliance_status = 'partially_compliant' THEN 1 ELSE 0 END) AS partially_compliant,
    SUM(CASE WHEN r.compliance_status = 'non_compliant' THEN 1 ELSE 0 END) AS non_compliant
FROM establishment e
LEFT JOIN inspection i ON i.establishment_id = e.id
LEFT JOIN reports r ON r.inspection_id = i.id
GROUP BY e.type
ORDER BY total_establishments DESC";
$byEstablishmentType = [];
$estTypeResult = safeQuery($conn, $estTypeSql);
if ($estTypeResult) {
    while ($row = $estTypeResult->fetch_assoc()) {
        $byEstablishmentType[] = $row;
    }
}

// Inspector performance (uses inspector, inspector1, inspector2 columns)
$inspPerf = "SELECT
    u.fullname AS inspector_name,
    COUNT(DISTINCT CASE WHEN i.inspector1 = u.id OR i.inspector2 = u.id OR i.inspector = u.id THEN i.id END) AS assigned,
    SUM(CASE WHEN (i.inspector1 = u.id OR i.inspector2 = u.id OR i.inspector = u.id)
              AND (i.status = 'completed' OR i.status = 'approved') THEN 1 ELSE 0 END) AS completed,
    SUM(CASE WHEN (i.inspector1 = u.id OR i.inspector2 = u.id OR i.inspector = u.id)
              AND r.compliance_status = 'compliant' THEN 1 ELSE 0 END) AS compliant_reports
FROM user u
LEFT JOIN inspection i ON (i.inspector1 = u.id OR i.inspector2 = u.id OR i.inspector = u.id)
LEFT JOIN reports r ON r.inspection_id = i.id
WHERE u.role = 'inspector'
GROUP BY u.id, u.fullname
ORDER BY completed DESC
LIMIT 10";
$inspectorPerformance = [];
$inspPerfResult = safeQuery($conn, $inspPerf);
if ($inspPerfResult) {
    while ($row = $inspPerfResult->fetch_assoc()) {
        $row['completion_rate'] = $row['assigned'] > 0
            ? round(($row['completed'] / $row['assigned']) * 100, 1) : 0;
        $inspectorPerformance[] = $row;
    }
}

// Summary stats
$summary = [
    'total_inspections'     => 0,
    'approved_inspections'  => 0,
    'scheduled_inspections' => 0,
    'active_establishments' => 0,
    'certificates_issued'   => 0,
    'non_compliant_count'   => 0,
    'overdue_defects'       => 0,
];

$statsSql = "SELECT
    (SELECT COUNT(*) FROM inspection) AS total_inspections,
    (SELECT COUNT(*) FROM inspection WHERE status = 'approved') AS approved_inspections,
    (SELECT COUNT(*) FROM inspection WHERE status = 'scheduled' OR status = 'status-pen') AS scheduled_inspections,
    (SELECT COUNT(*) FROM establishment WHERE status = 'active') AS active_establishments,
    (SELECT COUNT(*) FROM reports WHERE compliance_status = 'non_compliant') AS non_compliant_count";
$statsResult = safeQuery($conn, $statsSql);
if ($statsResult) {
    $row = $statsResult->fetch_assoc();
    if ($row) $summary = array_merge($summary, $row);
}

$certResult = safeQuery($conn, "SELECT COUNT(*) AS cnt FROM certificates WHERE status = 'authorized'");
if ($certResult) {
    $certRow = $certResult->fetch_assoc();
    $summary['certificates_issued'] = (int)($certRow['cnt'] ?? 0);
}

$defResult = safeQuery($conn, "SELECT COUNT(*) AS cnt FROM defects WHERE status = 'pending' AND STR_TO_DATE(grace_period, '%Y-%m-%d') < CURDATE()");
if ($defResult) {
    $defRow = $defResult->fetch_assoc();
    $summary['overdue_defects'] = (int)($defRow['cnt'] ?? 0);
}

// Violation type breakdown (uses defects_details column)
$violationTypes = [];
$vtSql = "SELECT
    CASE
        WHEN LOWER(d.defects_details) LIKE '%fire exit%' OR LOWER(d.defects_details) LIKE '%emergency exit%' OR LOWER(d.defects_details) LIKE '%blocked exit%' THEN 'Fire Exit'
        WHEN LOWER(d.defects_details) LIKE '%extinguisher%' OR LOWER(d.defects_details) LIKE '%equipment%' OR LOWER(d.defects_details) LIKE '%suppression%' OR LOWER(d.defects_details) LIKE '%detector%' OR LOWER(d.defects_details) LIKE '%alarm%' THEN 'Equipment'
        WHEN LOWER(d.defects_details) LIKE '%electri%' OR LOWER(d.defects_details) LIKE '%wiring%' OR LOWER(d.defects_details) LIKE '%voltage%' THEN 'Electrical'
        ELSE 'Other'
    END AS violation_type,
    COUNT(*) AS count
FROM defects d
GROUP BY violation_type
ORDER BY count DESC";
$vtResult = safeQuery($conn, $vtSql);
if ($vtResult) {
    while ($row = $vtResult->fetch_assoc()) {
        $violationTypes[] = $row;
    }
}

echo json_encode([
    'success'              => true,
    'summary'              => $summary,
    'complianceRate'       => $compRate,
    'complianceDist'       => $complianceDist,
    'monthlyTrend'         => $monthlyTrend,
    'inspectionTypes'      => $inspectionTypes,
    'byEstablishmentType'  => $byEstablishmentType,
    'inspectorPerformance' => $inspectorPerformance,
    'violationTypes'       => $violationTypes,
]);
