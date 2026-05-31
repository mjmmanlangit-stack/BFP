<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'cro') {
    header('Location: ../index.php');
    exit;
}
$croName = htmlspecialchars($_SESSION['fullname'] ?? 'CRO Officer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BFP CRO – Compliance Reports</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; --bfp-gold:#ffc107; --bfp-light:#f8f9fa; }
        body  { background:var(--bfp-light); font-family:'Segoe UI',sans-serif; }
        .main-container { padding:30px 20px; padding-left:270px; }
        .page-header { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; padding:25px 30px; border-radius:10px; margin-bottom:25px; }
        .stat-card { background:#fff; border-radius:10px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,.07); border-left:4px solid; height:100%; }
        .stat-card.primary   { border-left-color:#007bff; }
        .stat-card.success   { border-left-color:#28a745; }
        .stat-card.warning   { border-left-color:#ffc107; }
        .stat-card.danger    { border-left-color:#dc3545; }
        .chart-card { background:#fff; border-radius:10px; padding:25px; box-shadow:0 2px 8px rgba(0,0,0,.07); margin-bottom:25px; }
        .chart-card .card-title { font-weight:600; color:#333; margin-bottom:15px; }
        table thead { background:var(--bfp-red); color:#fff; }
        .badge-compliant           { background:#198754; color:#fff; }
        .badge-partially_compliant { background:#ffc107; color:#212529; }
        .badge-non_compliant       { background:#dc3545; color:#fff; }
        @media(max-width:768px){ .main-container{ padding-left:20px; } }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="logo-section">
        <div class="logo"><i class="fas fa-shield-alt" style="color:var(--bfp-red);font-size:24px"></i></div>
        <h5 class="mb-0">BFP SiteProfiler</h5>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-item"><a href="./dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="./establishments.php" class="nav-link"><i class="fas fa-building"></i> Establishments</a></div>
        <div class="nav-item"><a href="./payment-verification.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Fee Management</a></div>
        <div class="nav-item"><a href="./user-management.php" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-item"><a href="./inspection-history.php" class="nav-link"><i class="fas fa-history"></i> Inspection History</a></div>
        <div class="nav-item"><a href="./reports.php" class="nav-link active"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <div class="nav-item"><a href="./gis-map.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> GIS Map</a></div>
    </nav>
    <div class="sidebar-logout"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>

<div class="container-fluid main-container">
    <div class="page-header">
        <h4 class="mb-1"><i class="fas fa-chart-bar me-2"></i>Compliance Reports</h4>
        <p class="mb-0 opacity-75">Live data – establishment compliance, inspection trends, and inspector performance</p>
    </div>

    <!-- Summary stats -->
    <div class="row mb-4" id="summaryRow">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card primary">
                <div class="text-muted small mb-1">Total Inspections</div>
                <div class="fs-3 fw-bold text-primary" id="sumTotal">—</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card success">
                <div class="text-muted small mb-1">Compliance Rate</div>
                <div class="fs-3 fw-bold text-success" id="sumRate">—%</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card warning">
                <div class="text-muted small mb-1">Active Establishments</div>
                <div class="fs-3 fw-bold text-warning" id="sumEst">—</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card danger">
                <div class="text-muted small mb-1">Overdue Defects</div>
                <div class="fs-3 fw-bold text-danger" id="sumOverdue">—</div>
            </div>
        </div>
    </div>

    <!-- Export button -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success btn-sm me-2" onclick="exportCSV()">
            <i class="fas fa-file-csv me-1"></i>Export CSV
        </button>
    </div>

    <!-- Charts row 1 -->
    <div class="row mb-2">
        <div class="col-lg-5 mb-3">
            <div class="chart-card">
                <div class="card-title"><i class="fas fa-chart-pie text-danger me-2"></i>Compliance Distribution</div>
                <canvas id="complianceChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-lg-7 mb-3">
            <div class="chart-card">
                <div class="card-title"><i class="fas fa-chart-bar text-danger me-2"></i>Monthly Inspection Trend (12 months)</div>
                <canvas id="trendChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts row 2 -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="chart-card">
                <div class="card-title"><i class="fas fa-building text-danger me-2"></i>Compliance by Establishment Type</div>
                <canvas id="estTypeChart" height="180"></canvas>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="chart-card">
                <div class="card-title"><i class="fas fa-clipboard-list text-danger me-2"></i>Inspection Types Breakdown</div>
                <canvas id="inspTypeChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <!-- Inspector performance table -->
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="card-title mb-0"><i class="fas fa-user-shield text-danger me-2"></i>Inspector Performance</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Inspector</th><th>Assigned</th><th>Completed</th><th>Completion Rate</th><th>Compliant Reports</th></tr>
                </thead>
                <tbody id="inspectorTable">
                    <tr><td colspan="5" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script src="../../assets/scripts/components/sidebar.js"></script>
<script>
let _data = null;

async function loadReports() {
    try {
        const res = await fetch('../../utility/getReportData.php');
        _data = await res.json();
        if (!_data.success) { console.error(_data.message); return; }

        /* Summary */
        const s = _data.summary || {};
        document.getElementById('sumTotal').textContent   = s.total_inspections    ?? '—';
        document.getElementById('sumRate').textContent    = (_data.complianceRate  ?? '—') + '%';
        document.getElementById('sumEst').textContent     = s.active_establishments?? '—';
        document.getElementById('sumOverdue').textContent = s.overdue_defects      ?? '—';

        renderComplianceChart(_data.complianceDist);
        renderTrendChart(_data.monthlyTrend);
        renderEstTypeChart(_data.byEstablishmentType);
        renderInspTypeChart(_data.inspectionTypes);
        renderInspectorTable(_data.inspectorPerformance);
    } catch(e) { console.error('Report load error:', e); }
}

function renderComplianceChart(dist) {
    new Chart(document.getElementById('complianceChart'), {
        type: 'doughnut',
        data: {
            labels: ['Compliant', 'Partially Compliant', 'Non-Compliant'],
            datasets: [{ data: [dist.compliant||0, dist.partially_compliant||0, dist.non_compliant||0],
                backgroundColor: ['#28a745','#ffc107','#dc3545'], borderWidth:0 }]
        },
        options: { responsive:true, plugins:{ legend:{ position:'bottom' } } }
    });
}

function renderTrendChart(trend) {
    new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: {
            labels: trend.map(t => t.month),
            datasets: [
                { label:'Completed', data:trend.map(t=>parseInt(t.completed)||0), backgroundColor:'#28a745', borderRadius:3 },
                { label:'Scheduled', data:trend.map(t=>parseInt(t.scheduled)||0), backgroundColor:'#ffc107', borderRadius:3 }
            ]
        },
        options: {
            responsive:true,
            plugins:{ legend:{ position:'bottom' } },
            scales:{ x:{ stacked:true }, y:{ stacked:true, beginAtZero:true } }
        }
    });
}

function renderEstTypeChart(types) {
    new Chart(document.getElementById('estTypeChart'), {
        type: 'bar',
        data: {
            labels: types.map(t => t.establishment_type || 'Unknown'),
            datasets: [
                { label:'Compliant',           data:types.map(t=>parseInt(t.compliant)||0),           backgroundColor:'#28a745' },
                { label:'Partially Compliant', data:types.map(t=>parseInt(t.partially_compliant)||0), backgroundColor:'#ffc107' },
                { label:'Non-Compliant',       data:types.map(t=>parseInt(t.non_compliant)||0),       backgroundColor:'#dc3545' }
            ]
        },
        options: {
            responsive:true,
            plugins:{ legend:{ position:'bottom' } },
            scales:{ x:{ stacked:true }, y:{ stacked:true, beginAtZero:true } }
        }
    });
}

function renderInspTypeChart(types) {
    new Chart(document.getElementById('inspTypeChart'), {
        type: 'doughnut',
        data: {
            labels: types.map(t => t.inspection_type || 'Unknown'),
            datasets: [{ data:types.map(t=>parseInt(t.count)||0),
                backgroundColor:['#007bff','#28a745','#ffc107','#dc3545','#6c757d','#17a2b8'],
                borderWidth:0 }]
        },
        options: { responsive:true, plugins:{ legend:{ position:'bottom' } } }
    });
}

function renderInspectorTable(perf) {
    const tbody = document.getElementById('inspectorTable');
    if (!perf || !perf.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No inspector data.</td></tr>';
        return;
    }
    tbody.innerHTML = perf.map(p => {
        const rate = p.completion_rate || 0;
        const cls  = rate >= 80 ? 'bg-success' : rate >= 50 ? 'bg-warning text-dark' : 'bg-danger';
        return `<tr>
            <td><i class="fas fa-user-shield text-muted me-1"></i>${p.inspector_name}</td>
            <td>${p.assigned}</td>
            <td>${p.completed}</td>
            <td><span class="badge ${cls}">${rate}%</span></td>
            <td>${p.compliant_reports}</td>
        </tr>`;
    }).join('');
}

function exportCSV() {
    if (!_data) { alert('Data not loaded yet.'); return; }
    const rows = [['Month','Total','Completed','Scheduled','Compliance Rate']];
    (_data.monthlyTrend || []).forEach(t => rows.push([t.month, t.total, t.completed, t.scheduled, '']));
    rows.push([]);
    rows.push(['Inspector','Assigned','Completed','Completion Rate','Compliant Reports']);
    (_data.inspectorPerformance || []).forEach(p =>
        rows.push([p.inspector_name, p.assigned, p.completed, p.completion_rate+'%', p.compliant_reports]));
    const csv = rows.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'cro-compliance-report-' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}

document.addEventListener('DOMContentLoaded', loadReports);
</script>
</body>
</html>
