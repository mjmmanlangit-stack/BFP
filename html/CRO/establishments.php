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
    <title>BFP CRO - Establishments</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; --bfp-gold:#ffc107; --bfp-dark:#1a1a1a; --bfp-light:#f8f9fa; }
        body { background:var(--bfp-light); font-family:'Segoe UI',sans-serif; }
        .main-container { padding:30px 20px; padding-left:270px; }
        .welcome-section { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; padding:30px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,.1); margin-bottom:30px; }
        .table-container { background:#fff; padding:25px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .table thead { background:var(--bfp-red); color:#fff; }
        .modal-header { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; }
        .modal-header .btn-close { filter:brightness(0) invert(1); }
        .info-label { font-weight:600; color:var(--bfp-dark-red); margin-bottom:5px; display:block; }
        .info-value { padding:10px; background:var(--bfp-light); border-radius:5px; }
        #map { height:250px; border-radius:5px; margin-top:10px; }
        .filter-section { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:20px; }
        .doc-badge { font-size:.75rem; }
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
        <div class="nav-item"><a href="./establishments.php" class="nav-link active"><i class="fas fa-building"></i> Establishments</a></div>
        <div class="nav-item"><a href="./payment-verification.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Fee Management</a></div>
        <div class="nav-item"><a href="./user-management.php" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-item"><a href="./inspection-history.php" class="nav-link"><i class="fas fa-history"></i> Inspection History</a></div>
        <div class="nav-item"><a href="./reports.php" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <div class="nav-item"><a href="./gis-map.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> GIS Map</a></div>
    </nav>
    <div class="sidebar-logout"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>

<div class="container-fluid main-container">
    <div class="welcome-section">
        <h1><i class="fas fa-building"></i> Establishment Verification</h1>
        <p>Review payments, verify documents, and manage establishment records.</p>
    </div>

    <div class="table-container">
        <!-- Filters -->
        <div class="filter-section">
            <input type="text" id="searchInput" class="form-control" style="max-width:250px" placeholder="Search name, reg no, owner…"/>
            <select id="statusFilter" class="form-select" style="max-width:200px">
                <option value="all">All Establishments</option>
                <option value="pending">Pending Payment</option>
                <option value="paid">Payment Confirmed</option>
                <option value="no_inspection">No Inspection Yet</option>
            </select>
            <button class="btn btn-danger" onclick="loadEstablishments()"><i class="fas fa-search"></i> Filter</button>
            <button class="btn btn-outline-secondary ms-auto" onclick="loadEstablishments()"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Reg No.</th>
                        <th>Business Name</th>
                        <th>Type</th>
                        <th>Owner</th>
                        <th>Payment</th>
                        <th>Documents</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="establishmentTable">
                    <tr><td colspan="7" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View / Action Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-building"></i> Establishment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3"><span class="info-label">Business Name</span><div class="info-value" id="m_name"></div></div>
                        <div class="mb-3"><span class="info-label">BFP Reg No.</span><div class="info-value" id="m_regno"></div></div>
                        <div class="mb-3"><span class="info-label">Business Type</span><div class="info-value" id="m_type"></div></div>
                        <div class="mb-3"><span class="info-label">Address</span><div class="info-value" id="m_address"></div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3"><span class="info-label">Owner</span><div class="info-value" id="m_owner"></div></div>
                        <div class="mb-3"><span class="info-label">Contact</span><div class="info-value" id="m_phone"></div></div>
                        <div class="mb-3"><span class="info-label">Payment Status</span><div id="m_payment"></div></div>
                        <div class="mb-3"><span class="info-label">Inspection Status</span><div id="m_status"></div></div>
                    </div>
                </div>
                <!-- Map -->
                <div id="map"></div>

                <hr class="my-3"/>
                <!-- Documents Section -->
                <div class="mt-4">
                    <h6><i class="fas fa-file-alt text-danger me-2"></i>Submitted Documents
                        <span id="docsCount" class="badge bg-secondary ms-2">…</span>
                    </h6>
                    <p class="text-muted small mb-2">Review each document below. Click <strong>View</strong> to open it in a new tab or <strong>Download</strong> to save it.</p>
                    <div id="docsList" class="mt-2">
                        <p class="text-muted small">Loading documents…</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Document Modal -->
<div class="modal fade" id="rejectDocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle"></i> Reject Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectDocId"/>
                <label class="form-label">Reason for rejection <span class="text-danger">*</span></label>
                <textarea id="rejectDocNotes" class="form-control" rows="4" placeholder="Please provide the reason…"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" onclick="submitRejectDoc()"><i class="fas fa-paper-plane me-1"></i>Submit Rejection</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script>
let currentEst = null;
let mapInstance = null;

async function loadEstablishments() {
    const search = document.getElementById('searchInput').value.trim();
    const status = document.getElementById('statusFilter').value;
    const tbody  = document.getElementById('establishmentTable');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading…</td></tr>';

    try {
        const res = await fetch(`../../utility/getCROEstablishments.php?status=${status}&search=${encodeURIComponent(search)}`);
        const d   = await res.json();

        if (!d.success) { tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">${d.message}</td></tr>`; return; }
        if (!d.establishments.length) { tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No establishments found.</td></tr>'; return; }

        tbody.innerHTML = d.establishments.map(e => {
            // Payment status: use inspection.payment as single source of truth
            // 0/null = Unpaid, 1 = Paid
            const paid = e.payment == 1;
            const payBadge = paid 
                ? '<span class="badge bg-success">Paid</span>'
                : '<span class="badge bg-warning text-dark">Unpaid</span>';
            const docBadge = e.pending_docs > 0
                ? `<span class="badge bg-danger doc-badge">${e.pending_docs} Pending</span>`
                : (e.total_docs > 0 ? `<span class="badge bg-success doc-badge">All Reviewed</span>` : '<span class="badge bg-secondary doc-badge">None</span>');
            return `<tr>
                <td>${e.registration_no || '—'}</td>
                <td><strong>${e.name}</strong></td>
                <td>${e.type || '—'}</td>
                <td>${e.owner_name}<br><small class="text-muted">${e.owner_email || ''}</small></td>
                <td>${payBadge}</td>
                <td>${docBadge}</td>
                <td><button class="btn btn-sm btn-danger" onclick='viewEstablishment(${JSON.stringify(e).replace(/'/g,"&#39;")})'>
                    <i class="fas fa-eye"></i> View</button></td>
            </tr>`;
        }).join('');
    } catch(err) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error loading data.</td></tr>`;
        console.error(err);
    }
}

function viewEstablishment(est) {
    currentEst = est;
    document.getElementById('m_name').textContent    = est.name;
    document.getElementById('m_regno').textContent   = est.registration_no || '—';
    document.getElementById('m_type').textContent    = est.type || '—';
    document.getElementById('m_address').textContent = est.address || '—';
    document.getElementById('m_owner').textContent   = `${est.owner_name} (${est.owner_email || '—'})`;
    document.getElementById('m_phone').textContent   = est.owner_phone || '—';

    // Payment status display: use inspection.payment field as single source of truth
    const paid = est.payment == 1;
    document.getElementById('m_payment').innerHTML = paid
        ? '<span class="badge bg-success">Paid</span>'
        : '<span class="badge bg-warning text-dark">Unpaid</span>';

    document.getElementById('m_status').innerHTML = est.inspection_status
        ? `<span class="badge bg-info">${est.inspection_status}</span>` : '<span class="text-muted">—</span>';

    new bootstrap.Modal(document.getElementById('viewModal')).show();
    setTimeout(() => initMap(est.y_coordinate, est.x_coordinate, est.name, est.address), 300);
    loadDocuments(est.id);
}

function initMap(lat, lng, name, address) {
    const mapEl = document.getElementById('map');
    if (mapInstance) { mapInstance.remove(); mapInstance = null; }
    if (!lat || !lng) { mapEl.innerHTML = '<p class="text-muted text-center py-3">No coordinates available.</p>'; return; }
    mapEl.style.height = '250px';
    mapInstance = L.map('map').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(mapInstance);
    L.marker([lat, lng]).addTo(mapInstance).bindPopup(`<b>${name}</b><br>${address}`).openPopup();
}

async function loadDocuments(estId) {
    const docsEl  = document.getElementById('docsList');
    const countEl = document.getElementById('docsCount');
    docsEl.innerHTML = '<p class="text-muted small"><span class="spinner-border spinner-border-sm me-1"></span>Loading documents…</p>';
    try {
        const res = await fetch(`../../utility/getCRODocuments.php?establishment_id=${estId}`);
        const d   = await res.json();
        
        if (!d || typeof d !== 'object' || !d.success) {
            if (countEl) countEl.textContent = '0';
            docsEl.innerHTML = '<p class="text-muted small">No documents submitted yet.</p>';
            return;
        }
        
        const documents = d.documents || [];
        if (!documents || documents.length === 0) {
            if (countEl) countEl.textContent = '0';
            docsEl.innerHTML = '<p class="text-muted small">No documents submitted yet.</p>';
            return;
        }
        
        if (countEl) countEl.textContent = documents.length;
        const BASE = '../../utility/serveDocument.php';
        docsEl.innerHTML = `<div class="table-responsive"><table class="table table-sm table-bordered align-middle">
            <thead class="table-danger"><tr>
                <th>Document Type</th>
                <th>File Name</th>
                <th>Uploaded</th>
                <th style="min-width:90px">Status</th>
                <th style="min-width:220px">Actions</th>
            </tr></thead>
            <tbody>${documents.map(doc => {
                const statusBadge = doc.status === 'approved'
                    ? '<span class="badge bg-success">Approved</span>'
                    : doc.status === 'rejected'
                    ? '<span class="badge bg-danger">Rejected</span>'
                    : '<span class="badge bg-warning text-dark">Pending</span>';
                const reviewInfo = (doc.status === 'rejected' && doc.review_notes)
                    ? `<div class="text-danger small mt-1"><i class="fas fa-comment me-1"></i>${doc.review_notes}</div>` : '';
                const reviewActions = doc.status === 'pending'
                    ? `<button class="btn btn-success btn-sm me-1" onclick="reviewDoc(${doc.id},'approve','')"><i class="fas fa-check me-1"></i>Approve</button>
                       <button class="btn btn-danger btn-sm" onclick="openRejectDoc(${doc.id})"><i class="fas fa-times me-1"></i>Reject</button>`
                    : '<span class="text-muted small">Reviewed</span>';
                return `<tr>
                    <td><i class="fas fa-file-alt text-secondary me-1"></i>${doc.document_type || '—'}</td>
                    <td><span class="text-break">${doc.original_name || doc.filename}</span></td>
                    <td class="text-nowrap">${new Date(doc.createdAt).toLocaleDateString()}</td>
                    <td>${statusBadge}${reviewInfo}</td>
                    <td>
                        <a href="${BASE}?id=${doc.id}" target="_blank" class="btn btn-outline-primary btn-sm me-1">
                            <i class="fas fa-eye me-1"></i>View</a>
                        <a href="${BASE}?id=${doc.id}&download=1" class="btn btn-outline-secondary btn-sm me-1">
                            <i class="fas fa-download me-1"></i>Download</a>
                        ${reviewActions}
                    </td>
                </tr>`;
            }).join('')}
            </tbody></table></div>`;
    } catch(e) {
        docsEl.innerHTML = '<p class="text-danger small">Error loading documents. Please try again.</p>';
        console.error(e);
    }
}

function openRejectDoc(docId) {
    document.getElementById('rejectDocId').value = docId;
    document.getElementById('rejectDocNotes').value = '';
    new bootstrap.Modal(document.getElementById('rejectDocModal')).show();
}

async function submitRejectDoc() {
    const docId = parseInt(document.getElementById('rejectDocId').value);
    const notes = document.getElementById('rejectDocNotes').value.trim();
    if (!notes) { alert('Please provide a reason.'); return; }
    await reviewDoc(docId, 'reject', notes);
    bootstrap.Modal.getInstance(document.getElementById('rejectDocModal')).hide();
}

async function reviewDoc(docId, action, notes) {
    try {
        const res = await fetch('../../utility/reviewDocument.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ document_id: docId, action, notes })
        });
        const d = await res.json();
        if (d.success) {
            showToast(`Document ${action}d.`, 'success');
            if (currentEst) loadDocuments(currentEst.id);
            loadEstablishments();
        } else { showToast(d.message, 'danger'); }
    } catch(e) { showToast('Network error.', 'danger'); }
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3`;
    t.style.zIndex = '9999';
    t.innerHTML = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

document.getElementById('searchInput').addEventListener('keydown', e => { if (e.key === 'Enter') loadEstablishments(); });
document.addEventListener('DOMContentLoaded', loadEstablishments);
</script>
</body>
</html>
