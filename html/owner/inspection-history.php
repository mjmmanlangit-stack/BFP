<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'owner') {
    header('Location: ../index.php');
    exit;
}
$ownerName = htmlspecialchars($_SESSION['fullname'] ?? 'Owner');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BFP Site Profiler - Inspection History</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; --bfp-gold:#ffc107; }
        body { background:#f8f9fa; font-family:'Segoe UI',sans-serif; }
        .main-content { padding-left:250px; }
        .page-header { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; padding:30px; margin-bottom:30px; }
        .card-section { border:none; border-radius:15px; box-shadow:0 5px 15px rgba(0,0,0,.1); margin-bottom:20px; }
        .section-header { background:var(--bfp-red); color:#fff; padding:15px 20px; border-radius:15px 15px 0 0; font-weight:bold; }
        .table thead { background:var(--bfp-red); color:#fff; }
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
        <div class="nav-item"><a href="./my-establishments.php" class="nav-link"><i class="fas fa-building"></i> My Establishments</a></div>
        <div class="nav-item"><a href="./certificates.php" class="nav-link"><i class="fas fa-certificate"></i> Certificates</a></div>
        <div class="nav-item"><a href="./documents.php" class="nav-link"><i class="fas fa-file-alt"></i> Documents</a></div>
        <div class="nav-item"><a href="./inspection-history.php" class="nav-link active"><i class="fas fa-history"></i> Inspection History</a></div>
    </nav>
    <div class="nav-item"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>

<div class="main-content">
    <div class="page-header">
        <div class="container-fluid px-4">
            <h2><i class="fas fa-history"></i> My Inspection History</h2>
            <p class="mb-0">Track all fire safety inspections for your establishments.</p>
        </div>
    </div>

    <div class="container-fluid px-4 pb-5">
        <div class="card-section">
            <div class="section-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clipboard-list me-2"></i>Inspection Records</span>
                <button class="btn btn-sm btn-light" onclick="loadHistory()"><i class="fas fa-sync-alt"></i> Refresh</button>
            </div>
            <div class="card-body p-4">
                <!-- Filters -->
                <div class="d-flex gap-2 flex-wrap mb-3">
                    <input type="text" id="searchInput" class="form-control" style="max-width:220px" placeholder="Search establishment…"/>
                    <select id="complianceFilter" class="form-select" style="max-width:180px">
                        <option value="">All Compliance</option>
                        <option value="compliant">Compliant</option>
                        <option value="non-compliant">Non-Compliant</option>
                        <option value="conditionally compliant">Conditional</option>
                    </select>
                    <select id="statusFilter" class="form-select" style="max-width:160px">
                        <option value="">All Statuses</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Establishment</th>
                                <th>Inspector(s)</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Compliance</th>
                                <th>Certificate</th>
                                <th>Endorsed</th>
                            </tr>
                        </thead>
                        <tbody id="historyTable">
                            <tr><td colspan="7" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="historyCount" class="text-muted small mt-2"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
let allHistory = [];

async function loadHistory() {
    const tbody = document.getElementById('historyTable');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading…</td></tr>';
    try {
        const res = await fetch('../../utility/getInspectionHistory.php');
        const d   = await res.json();
        if (!d.success) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-danger text-center">${d.message||'Error'}</td></tr>`;
            return;
        }
        allHistory = d.history || [];
        applyFilters();
    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading records.</td></tr>';
    }
}

function applyFilters() {
    const search  = document.getElementById('searchInput').value.toLowerCase();
    const compF   = document.getElementById('complianceFilter').value.toLowerCase();
    const statF   = document.getElementById('statusFilter').value.toLowerCase();
    const tbody   = document.getElementById('historyTable');

    let rows = allHistory;
    if (search) rows = rows.filter(r => (r.establishment_name||'').toLowerCase().includes(search));
    if (compF)  rows = rows.filter(r => (r.compliance_status||'').toLowerCase() === compF);
    if (statF)  rows = rows.filter(r => (r.status||'').toLowerCase() === statF);

    document.getElementById('historyCount').textContent = `Showing ${rows.length} of ${allHistory.length} records`;

    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No inspection records found.</td></tr>';
        return;
    }

    tbody.innerHTML = rows.map(r => {
        const inspectors = [r.inspector1_name, r.inspector2_name].filter(Boolean).join(', ') || '—';
        const date = r.scheduled_date ? new Date(r.scheduled_date).toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'}) : '—';

        const compBadge = r.compliance_status
            ? `<span class="badge ${r.compliance_status.toLowerCase()==='compliant'?'bg-success':r.compliance_status.toLowerCase().includes('conditional')?'bg-warning text-dark':'bg-danger'}">${r.compliance_status}</span>`
            : '<span class="text-muted">—</span>';

        const endBadge = r.endorsement_status
            ? `<span class="badge ${r.endorsement_status==='endorsed'?'bg-success':r.endorsement_status==='rejected'?'bg-danger':'bg-secondary'}">${r.endorsement_status}</span>`
            : '—';

        return `<tr>
            <td><strong>${r.establishment_name||'—'}</strong><br><small class="text-muted">${r.registration_no||''}</small></td>
            <td>${inspectors}</td>
            <td>${date}</td>
            <td><span class="badge bg-secondary">${r.status||'—'}</span></td>
            <td>${compBadge}</td>
            <td>${r.certificate_number ? `<span class="badge bg-info text-dark">${r.certificate_number}</span>` : '—'}</td>
            <td>${endBadge}</td>
        </tr>`;
    }).join('');
}

document.getElementById('searchInput').addEventListener('input', applyFilters);
document.getElementById('complianceFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.addEventListener('DOMContentLoaded', loadHistory);
</script>
</body>
</html>
