<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'chief') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BFP Chief - Inspection History</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; --bfp-gold:#ffc107; --bfp-dark:#1a1a1a; --bfp-light:#f8f9fa; }
        body { background:var(--bfp-light); font-family:'Segoe UI',sans-serif; }
        .main-content { margin-left:250px; padding:20px; }
        .page-header { background:#fff; padding:20px 25px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:25px; }
        .card { border:none; box-shadow:0 2px 10px rgba(0,0,0,.06); border-radius:10px; }
        .card-header { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; border-radius:10px 10px 0 0 !important; }
        .table thead th { background:var(--bfp-dark); color:#fff; }
        .badge-compliant { background:#198754; color:#fff; }
        .badge-non_compliant { background:var(--bfp-red); color:#fff; }
        .badge-partially_compliant { background:var(--bfp-gold); color:var(--bfp-dark); }
        .badge-endorsed { background:#198754; color:#fff; }
        .badge-rejected { background:var(--bfp-red); color:#fff; }
        .badge-pending  { background:var(--bfp-gold); color:var(--bfp-dark); }
        .modal-header { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; }
        @media(max-width:768px){ .main-content{ margin-left:70px; } }
    </style>
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="logo-section">
        <div class="logo"><i class="fas fa-shield-alt" style="color:var(--bfp-red);font-size:24px"></i></div>
        <h5 class="mb-0">BFP SiteProfiler</h5>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-item"><a href="./dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="./sched_inspection.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Schedule Inspection</a></div>
        <div class="nav-item"><a href="./review-reports.php" class="nav-link"><i class="fas fa-check-double"></i> Review Reports</a></div>
        <div class="nav-item"><a href="./reports.php" class="nav-link active"><i class="fas fa-file-alt"></i> Inspection History</a></div>
        <div class="nav-item"><a href="./gis-map.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> GIS Map</a></div>
    </nav>
    <div class="nav-item"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>

<div class="main-content">
    <div class="page-header">
        <h4 class="mb-0"><i class="fas fa-history text-danger me-2"></i>Inspection History & Reports</h4>
        <small class="text-muted">All establishment inspection records, compliance stats, and endorsement results</small>
    </div>

    <!-- Live stats summary -->
    <div class="row mb-4" id="statsRow">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-primary"   id="statTotal">—</div>
                    <div class="text-muted small">Total Inspections</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-success"   id="statRate">—%</div>
                    <div class="text-muted small">Compliance Rate</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-warning"   id="statCerts">—</div>
                    <div class="text-muted small">Certificates Issued</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-danger"    id="statOverdue">—</div>
                    <div class="text-muted small">Overdue Defects</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts row -->
    <div class="row mb-4">
        <div class="col-lg-5 mb-3">
            <div class="card"><div class="card-header"><i class="fas fa-chart-pie me-2"></i>Compliance Distribution</div>
                <div class="card-body"><canvas id="complianceChart" height="220"></canvas></div>
            </div>
        </div>
        <div class="col-lg-7 mb-3">
            <div class="card"><div class="card-header"><i class="fas fa-chart-bar me-2"></i>Monthly Inspection Trend</div>
                <div class="card-body"><canvas id="trendChart" height="130"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Export button -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success btn-sm" onclick="exportCSV()">
            <i class="fas fa-file-csv me-1"></i>Export CSV
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap gap-2">
            <input type="text" class="form-control w-auto" id="searchInput" placeholder="Search establishment..." oninput="applyFilters()">
            <select class="form-select w-auto" id="complianceFilter" onchange="applyFilters()">
                <option value="">All Compliance</option>
                <option value="compliant">Compliant</option>
                <option value="partially_compliant">Partially Compliant</option>
                <option value="non_compliant">Non-Compliant</option>
            </select>
            <select class="form-select w-auto" id="endorseFilter" onchange="applyFilters()">
                <option value="">All Endorsements</option>
                <option value="pending">Pending</option>
                <option value="endorsed">Endorsed</option>
                <option value="rejected">Rejected</option>
            </select>
            <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()"><i class="fas fa-undo"></i> Reset</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i>Inspection Records</span>
            <span class="badge bg-light text-dark" id="recordCount">0 records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>Establishment</th><th>Owner</th><th>Inspector(s)</th>
                        <th>Date</th><th>Compliance</th><th>Endorsement</th><th>Certificate</th>
                    </tr></thead>
                    <tbody id="historyTable">
                        <tr><td colspan="7" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
let allHistory = [], filteredHistory = [], _reportData = null;

/* ---------- Inspection history table ---------- */
async function loadHistory() {
    try {
        const res = await fetch('../../utility/getInspectionHistory.php');
        const d   = await res.json();
        allHistory     = d.success ? d.history : [];
        filteredHistory = [...allHistory];
        renderTable();
    } catch (e) {
        document.getElementById('historyTable').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Network error.</td></tr>';
    }
}

function applyFilters() {
    const search  = document.getElementById('searchInput').value.toLowerCase();
    const comp    = document.getElementById('complianceFilter').value;
    const endorse = document.getElementById('endorseFilter').value;

    filteredHistory = allHistory.filter(r => {
        const matchSearch = !search || (r.establishment_name||'').toLowerCase().includes(search);
        const matchComp   = !comp   || r.compliance_status === comp;
        const matchEndorse= !endorse|| (r.endorsement_status||'pending') === endorse;
        return matchSearch && matchComp && matchEndorse;
    });
    renderTable();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('complianceFilter').value = '';
    document.getElementById('endorseFilter').value = '';
    filteredHistory = [...allHistory];
    renderTable();
}

function renderTable() {
    document.getElementById('recordCount').textContent = filteredHistory.length + ' records';
    const tbody = document.getElementById('historyTable');
    if (!filteredHistory.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No records found.</td></tr>';
        return;
    }
    tbody.innerHTML = filteredHistory.map(r => {
        const comp    = r.compliance_status || '—';
        const endorse = r.endorsement_status || 'pending';
        return `<tr>
            <td><strong>${r.establishment_name}</strong><br><small class="text-muted">${r.establishment_type||''}</small></td>
            <td>${r.owner_name||'—'}</td>
            <td>${[r.inspector1_name,r.inspector2_name].filter(Boolean).join(', ')||'—'}</td>
            <td>${r.inspection_date ? new Date(r.inspection_date).toLocaleDateString() : '—'}</td>
            <td>${r.report_id ? `<span class="badge badge-${comp}">${comp.replace('_',' ')}</span>` : '<span class="text-muted">No report</span>'}</td>
            <td>${r.report_id ? `<span class="badge badge-${endorse}">${endorse}</span>` : '—'}</td>
            <td>${r.certificate_number ? `<span class="badge bg-success">${r.certificate_number}</span>` : '—'}</td>
        </tr>`;
    }).join('');
}

/* ---------- Live stats + charts ---------- */
async function loadReportStats() {
    try {
        const res = await fetch('../../utility/getReportData.php');
        _reportData = await res.json();
        if (!_reportData.success) return;

        const s = _reportData.summary || {};
        document.getElementById('statTotal').textContent   = s.total_inspections      ?? '—';
        document.getElementById('statRate').textContent    = (_reportData.complianceRate ?? '—') + '%';
        document.getElementById('statCerts').textContent   = s.certificates_issued      ?? '—';
        document.getElementById('statOverdue').textContent = s.overdue_defects          ?? '—';

        renderComplianceChart(_reportData.complianceDist);
        renderTrendChart(_reportData.monthlyTrend);
    } catch(e) { console.error(e); }
}

function renderComplianceChart(dist) {
    const ctx = document.getElementById('complianceChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Compliant','Partially Compliant','Non-Compliant'],
            datasets:[{ data:[dist.compliant||0,dist.partially_compliant||0,dist.non_compliant||0],
                backgroundColor:['#198754','#ffc107','#dc3545'], borderWidth:0 }]
        },
        options:{ responsive:true, plugins:{ legend:{ position:'bottom' } } }
    });
}

function renderTrendChart(trend) {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: trend.map(t => t.month),
            datasets:[
                { label:'Completed', data:trend.map(t=>parseInt(t.completed)||0), backgroundColor:'#198754', borderRadius:3 },
                { label:'Scheduled', data:trend.map(t=>parseInt(t.scheduled)||0), backgroundColor:'#ffc107', borderRadius:3 }
            ]
        },
        options:{
            responsive:true,
            plugins:{ legend:{ position:'bottom' } },
            scales:{ x:{ stacked:true }, y:{ stacked:true, beginAtZero:true } }
        }
    });
}

function exportCSV() {
    const rows = [['Establishment','Owner','Inspector(s)','Date','Compliance','Endorsement','Certificate']];
    filteredHistory.forEach(r => rows.push([
        r.establishment_name, r.owner_name||'',
        [r.inspector1_name,r.inspector2_name].filter(Boolean).join('; '),
        r.inspection_date||'', r.compliance_status||'', r.endorsement_status||'', r.certificate_number||''
    ]));
    const csv = rows.map(r => r.map(c=>`"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob([csv],{type:'text/csv'});
    const a = document.createElement('a'); a.href=URL.createObjectURL(blob);
    a.download='chief-inspection-history-'+new Date().toISOString().slice(0,10)+'.csv';
    a.click();
}

document.addEventListener('DOMContentLoaded', () => {
    loadHistory();
    loadReportStats();
});
</script>
</body>
</html>
