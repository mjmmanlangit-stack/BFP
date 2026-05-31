<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'chief') {
    header('Location: ../index.php');
    exit;
}
$chiefName = htmlspecialchars($_SESSION['fullname'] ?? 'Fire Chief');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BFP Chief - Review Reports</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; --bfp-gold:#ffc107; --bfp-dark:#1a1a1a; --bfp-light:#f8f9fa; }
        body { background:var(--bfp-light); font-family:'Segoe UI',sans-serif; }
        .main-content { margin-left:250px; padding:20px; }
        .page-header { background:#fff; padding:20px 25px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:25px; }
        .table-card { background:#fff; border-radius:10px; padding:25px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .table thead { background:var(--bfp-red); color:#fff; }
        .badge-pending   { background:var(--bfp-gold); color:var(--bfp-dark); }
        .badge-endorsed  { background:#198754; color:#fff; }
        .badge-rejected  { background:var(--bfp-red); color:#fff; }
        .badge-compliant { background:#198754; color:#fff; }
        .badge-non_compliant { background:var(--bfp-red); color:#fff; }
        .badge-partially_compliant { background:var(--bfp-gold); color:var(--bfp-dark); }
        .btn-endorse { background:#198754; color:#fff; border:none; padding:6px 14px; border-radius:5px; }
        .btn-endorse:hover { background:#146c43; color:#fff; }
        .btn-reject { background:var(--bfp-red); color:#fff; border:none; padding:6px 14px; border-radius:5px; }
        .btn-reject:hover { background:var(--bfp-dark-red); color:#fff; }
        .modal-header { background:linear-gradient(90deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; }
        .defect-card { border:1px solid #dee2e6; border-radius:8px; padding:14px; margin-bottom:12px; background:#fafafa; }
        .defect-card.status-solved { border-left:4px solid #198754; }
        .defect-card.status-pending { border-left:4px solid var(--bfp-gold); }
        .evidence-link { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:5px; background:#e9ecef; color:#495057; text-decoration:none; font-size:.85rem; }
        .evidence-link:hover { background:#dee2e6; color:#212529; }
        .report-section-title { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#6c757d; border-bottom:1px solid #dee2e6; padding-bottom:4px; margin-bottom:10px; }
        @media(max-width:768px){ .main-content{ margin-left:70px; } }
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
        <div class="nav-item"><a href="./sched_inspection.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Schedule Inspection</a></div>
        <div class="nav-item"><a href="./review-reports.php" class="nav-link active"><i class="fas fa-check-double"></i> Review Reports</a></div>
        <div class="nav-item"><a href="./reports.php" class="nav-link"><i class="fas fa-file-alt"></i> Inspection History</a></div>
        <div class="nav-item"><a href="./gis-map.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> GIS Map</a></div>
    </nav>
    <div class="nav-item"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>

<div class="main-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-check-double text-danger me-2"></i>Review & Endorse Reports</h4>
            <small class="text-muted">Inspect submitted reports and endorse or reject them</small>
        </div>
        <div>
            <select class="form-select form-select-sm d-inline-block w-auto" id="filterStatus" onchange="applyFilter()">
                <option value="">All Reports</option>
                <option value="pending" selected>Pending Review</option>
                <option value="endorsed">Endorsed</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Establishment</th>
                        <th>Inspector(s)</th>
                        <th>Compliance</th>
                        <th>Finalized</th>
                        <th>Endorsement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="reportsTable">
                    <tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading reports...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Review Modal (full detail + endorse/reject) -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i><span id="reviewModalTitle">Review Report</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reviewModalBody">
                <div class="text-center py-5"><span class="spinner-border text-danger"></span><p class="mt-2 text-muted">Loading report details…</p></div>
            </div>
            <div class="modal-footer" id="reviewModalFooter" style="display:none">
                <div class="w-100 mb-2">
                    <label class="form-label fw-bold">Chief Notes <small class="text-muted fw-normal">(optional)</small></label>
                    <textarea class="form-control" id="reviewNotes" rows="2" placeholder="Add any comments or justification…"></textarea>
                </div>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-reject" id="rejectBtn" onclick="submitReview('reject')"><i class="fas fa-times me-1"></i>Reject</button>
                <button class="btn btn-endorse" id="endorseBtn" onclick="submitReview('endorse')"><i class="fas fa-check me-1"></i>Endorse</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
let allReports = [];
let currentReportId = null;

async function loadReports() {
    const tbody = document.getElementById('reportsTable');
    try {
        const res = await fetch('../../utility/getInspectionHistory.php');
        const d   = await res.json();
        if (!d.success) { tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Failed to load reports.</td></tr>'; return; }

        allReports = d.history.filter(h => h.report_id);
        renderTable();
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Network error.</td></tr>';
    }
}

function applyFilter() { renderTable(); }

function renderTable() {
    const filter = document.getElementById('filterStatus').value;
    const tbody  = document.getElementById('reportsTable');
    let data = allReports;
    if (filter) data = data.filter(r => (r.endorsement_status || 'pending') === filter);

    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No reports found.</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(r => {
        const comp    = r.compliance_status || '—';
        const endorse = r.endorsement_status || 'pending';
        return `<tr>
            <td><strong>${r.establishment_name}</strong><br><small class="text-muted">${r.establishment_type||''}</small></td>
            <td>${[r.inspector1_name,r.inspector2_name].filter(Boolean).join(', ')||'—'}</td>
            <td><span class="badge badge-${comp}">${comp.replace(/_/g,' ')}</span></td>
            <td>${r.finalized_at ? new Date(r.finalized_at).toLocaleDateString() : '—'}</td>
            <td><span class="badge badge-${endorse}">${endorse}</span></td>
            <td class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary" title="View Details" onclick="openReview(${r.report_id}, false)"><i class="fas fa-eye"></i></button>
                ${endorse === 'pending' ? `<button class="btn btn-sm btn-endorse" onclick="openReview(${r.report_id}, true)"><i class="fas fa-check-double me-1"></i>Review</button>` : ''}
            </td>
        </tr>`;
    }).join('');
}

async function openReview(reportId, canAct) {
    currentReportId = reportId;
    document.getElementById('reviewModalTitle').textContent = 'Loading…';
    document.getElementById('reviewModalBody').innerHTML = '<div class="text-center py-5"><span class="spinner-border text-danger"></span><p class="mt-2 text-muted">Loading report details…</p></div>';
    document.getElementById('reviewModalFooter').style.display = 'none';
    document.getElementById('reviewNotes').value = '';
    new bootstrap.Modal(document.getElementById('reviewModal')).show();

    try {
        const res = await fetch(`../../utility/getReportDetails.php?report_id=${reportId}`);
        const d   = await res.json();
        if (!d.success) {
            document.getElementById('reviewModalBody').innerHTML = `<div class="alert alert-danger">${d.message}</div>`;
            return;
        }
        renderReviewModal(d, canAct);
    } catch(e) {
        document.getElementById('reviewModalBody').innerHTML = '<div class="alert alert-danger">Network error loading report details.</div>';
    }
}

function complianceBadge(c) {
    if (!c) return '<span class="badge bg-secondary">—</span>';
    const map = { compliant:'#198754', partially_compliant:'#ffc107', non_compliant:'#dc3545' };
    const textMap = { compliant:'#fff', partially_compliant:'#1a1a1a', non_compliant:'#fff' };
    return `<span class="badge" style="background:${map[c]||'#6c757d'};color:${textMap[c]||'#fff'}">${c.replace(/_/g,' ')}</span>`;
}

function renderReviewModal(d, canAct) {
    document.getElementById('reviewModalTitle').textContent = d.establishmentName;

    // Build defects HTML
    let defectsHtml = '';
    if (d.defects && d.defects.length > 0) {
        defectsHtml = d.defects.map((def, i) => {
            const isSolved  = def.status === 'solved';
            const today     = new Date(); today.setHours(0,0,0,0);
            const graceDate = def.gracePeriod ? new Date(def.gracePeriod) : null;
            const overdue   = graceDate && graceDate < today && !isSolved;

            const evidenceHtml = def.evidencePath
                ? `<a href="../../${def.evidencePath}" target="_blank" class="evidence-link mt-1">
                       <i class="fas fa-paperclip"></i> View Evidence
                   </a>`
                : '<span class="text-muted" style="font-size:.83rem"><i class="fas fa-ban me-1"></i>No evidence uploaded</span>';

            return `<div class="defect-card status-${def.status}">
                <div class="d-flex justify-content-between align-items-start">
                    <strong>Defect #${i+1}</strong>
                    <div class="d-flex gap-2 align-items-center">
                        ${overdue ? '<span class="badge bg-danger">Overdue</span>' : ''}
                        <span class="badge ${isSolved ? 'bg-success' : 'bg-warning text-dark'}">${def.status}</span>
                    </div>
                </div>
                <p class="mb-1 mt-2">${def.details}</p>
                <div class="d-flex gap-3 mt-1 flex-wrap" style="font-size:.84rem">
                    <span class="text-muted"><i class="fas fa-calendar-alt me-1"></i>Grace period: <strong>${def.gracePeriod || '—'}</strong></span>
                    ${evidenceHtml}
                </div>
            </div>`;
        }).join('');
    } else {
        defectsHtml = '<p class="text-muted">No defects recorded for this report.</p>';
    }

    const endorsedInfo = (d.endorsementStatus && d.endorsementStatus !== 'pending')
        ? `<div class="alert alert-${d.endorsementStatus === 'endorsed' ? 'success' : 'danger'} py-2 mt-2">
               <i class="fas fa-${d.endorsementStatus === 'endorsed' ? 'check' : 'times'}-circle me-1"></i>
               <strong>${d.endorsementStatus.charAt(0).toUpperCase() + d.endorsementStatus.slice(1)}</strong>
               ${d.endorsedByName ? `by ${d.endorsedByName}` : ''}
               ${d.endorsedAt ? `on ${new Date(d.endorsedAt).toLocaleDateString()}` : ''}
               ${d.endorsementNotes ? `<br><em>${d.endorsementNotes}</em>` : ''}
           </div>`
        : '';

    document.getElementById('reviewModalBody').innerHTML = `
        ${endorsedInfo}
        <div class="row g-3 mb-4">
            <div class="col-12"><div class="report-section-title"><i class="fas fa-building me-1"></i>Establishment</div></div>
            <div class="col-md-4"><small class="text-muted d-block">Name</small><strong>${d.establishmentName}</strong></div>
            <div class="col-md-4"><small class="text-muted d-block">Type</small>${d.establishmentType || '—'}</div>
            <div class="col-md-4"><small class="text-muted d-block">Registration No.</small>${d.registrationNo}</div>
            <div class="col-md-8"><small class="text-muted d-block">Address</small>${d.address || '—'}</div>
            <div class="col-md-4"><small class="text-muted d-block">Owner</small>${d.ownerName || '—'}</div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-12"><div class="report-section-title"><i class="fas fa-search me-1"></i>Inspection</div></div>
            <div class="col-md-3"><small class="text-muted d-block">Date</small>${d.inspectionDate ? new Date(d.inspectionDate).toLocaleDateString() : '—'}</div>
            <div class="col-md-3"><small class="text-muted d-block">Time Slot</small>${d.timeSlot || '—'}</div>
            <div class="col-md-3"><small class="text-muted d-block">Type</small>${d.inspectionType || '—'}</div>
            <div class="col-md-3"><small class="text-muted d-block">Priority</small>${d.priorityLevel || '—'}</div>
            <div class="col-md-6"><small class="text-muted d-block">Inspector(s)</small>${[d.inspector1Name, d.inspector2Name].filter(Boolean).join(', ') || '—'}</div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-12"><div class="report-section-title"><i class="fas fa-file-alt me-1"></i>Report</div></div>
            <div class="col-md-4"><small class="text-muted d-block">Order No.</small><strong>${d.inspectionOrderNo || '—'}</strong></div>
            <div class="col-md-4"><small class="text-muted d-block">Compliance Status</small>${complianceBadge(d.complianceStatus)}</div>
            <div class="col-md-4"><small class="text-muted d-block">Finalized At</small>${d.finalizedAt ? new Date(d.finalizedAt).toLocaleString() : '<span class="text-warning">Not yet finalized</span>'}</div>
            ${d.inspectorNotes ? `<div class="col-12"><small class="text-muted d-block">Inspector Notes</small><p class="mb-0">${d.inspectorNotes}</p></div>` : ''}
        </div>
        <div class="mb-2"><div class="report-section-title"><i class="fas fa-exclamation-triangle me-1"></i>Defects / Deficiencies (${d.defects ? d.defects.length : 0})</div></div>
        ${defectsHtml}`;

    if (canAct && (!d.endorsementStatus || d.endorsementStatus === 'pending')) {
        document.getElementById('reviewModalFooter').style.display = '';
    }
}

async function submitReview(action) {
    if (!currentReportId) return;
    const notes = document.getElementById('reviewNotes').value.trim();
    const btn = action === 'endorse' ? document.getElementById('endorseBtn') : document.getElementById('rejectBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + (action === 'endorse' ? 'Endorsing…' : 'Rejecting…');
    try {
        const res = await fetch('../../utility/chiefEndorseReport.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ report_id: currentReportId, action, notes })
        });
        const j = await res.json();
        bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
        if (j.success) {
            showToast(j.message, 'success');
            await loadReports();
        } else {
            showToast(j.message || 'Action failed', 'danger');
        }
    } catch (e) { showToast('Network error', 'danger'); }
    btn.disabled = false;
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.className = `alert alert-${type} position-fixed top-0 end-0 m-3 shadow`;
    t.style.zIndex = 9999;
    t.innerHTML = `<i class="fas fa-${type==='success'?'check':'exclamation'}-circle me-2"></i>${msg}`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}

document.addEventListener('DOMContentLoaded', loadReports);
</script>
</body>
</html>
