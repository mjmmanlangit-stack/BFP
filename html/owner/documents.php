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
    <title>BFP Site Profiler - My Documents</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; --bfp-gold:#ffc107; --bfp-light:#f8f9fa; }
        body { background:#f0f2f5; font-family:'Segoe UI',sans-serif; }
        .main-content { padding-left:250px; min-height:100vh; }

        /* Page Header */
        .page-header { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; padding:28px 32px; margin-bottom:28px; box-shadow:0 4px 12px rgba(0,0,0,.15); }
        .page-header h2 { font-weight:700; margin:0; font-size:1.6rem; }
        .page-header p  { margin:4px 0 0; opacity:.85; font-size:.92rem; }

        /* Cards */
        .bfp-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.08); overflow:hidden; margin-bottom:24px; }
        .bfp-card-header { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; padding:14px 20px; font-weight:600; font-size:.95rem; display:flex; align-items:center; gap:8px; }
        .bfp-card-body { padding:24px; }

        /* Step Indicators */
        .steps-row { display:flex; align-items:center; margin-bottom:24px; }
        .step-item  { display:flex; align-items:center; gap:10px; flex:1; }
        .step-num   { width:32px; height:32px; border-radius:50%; background:#e9ecef; color:#6c757d; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0; transition:all .3s; }
        .step-num.active { background:var(--bfp-red); color:#fff; }
        .step-num.done   { background:#28a745; color:#fff; }
        .step-label { font-size:.82rem; color:#6c757d; line-height:1.2; }
        .step-label strong { display:block; color:#333; font-size:.88rem; }
        .step-divider { height:2px; flex:1; background:#e9ecef; margin:0 8px; min-width:20px; }

        /* Upload Zone */
        .upload-zone { border:2.5px dashed #ccc; border-radius:10px; padding:36px 24px; text-align:center; cursor:pointer; background:#fafafa; transition:all .25s; }
        .upload-zone:hover,.upload-zone.drag-over { border-color:var(--bfp-red); background:#fff8f8; }
        .upload-zone.has-file { border-color:#28a745; background:#f0fff4; }
        .upload-zone .upload-icon { font-size:2.4rem; color:#ccc; transition:color .25s; }
        .upload-zone:hover .upload-icon,.upload-zone.drag-over .upload-icon { color:var(--bfp-red); }
        .upload-zone.has-file .upload-icon { color:#28a745; }
        .upload-zone .upload-hint { color:#888; font-size:.82rem; margin-top:4px; }

        /* File chip */
        .file-chip { display:inline-flex; align-items:center; gap:8px; background:#e8f5e9; border:1.5px solid #81c784; border-radius:50px; padding:6px 14px; font-size:.83rem; font-weight:600; color:#2e7d32; margin-top:10px; max-width:100%; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
        .file-chip .remove-file { cursor:pointer; color:#c62828; font-size:.75rem; margin-left:4px; }

        /* Submit button */
        .btn-submit-doc { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; border:none; padding:12px 36px; border-radius:8px; font-size:1rem; font-weight:700; letter-spacing:.4px; transition:all .2s; box-shadow:0 4px 12px rgba(220,53,69,.35); width:100%; }
        .btn-submit-doc:hover:not(:disabled) { background:linear-gradient(135deg,var(--bfp-dark-red),#7a1f2a); color:#fff; transform:translateY(-1px); box-shadow:0 6px 16px rgba(220,53,69,.4); }
        .btn-submit-doc:disabled { opacity:.55; cursor:not-allowed; transform:none; }

        /* Progress */
        .upload-progress-wrap { margin-top:16px; }
        .upload-progress-wrap .progress { height:8px; border-radius:8px; }

        /* Forms */
        .form-label { font-weight:600; font-size:.88rem; color:#444; margin-bottom:5px; }
        .form-select,.form-control { border-radius:8px; border:1.5px solid #dee2e6; font-size:.9rem; padding:9px 12px; }
        .form-select:focus,.form-control:focus { border-color:var(--bfp-red); box-shadow:0 0 0 3px rgba(220,53,69,.12); }

        /* Documents table */
        .docs-table thead th { background:var(--bfp-dark-red); color:#fff; font-size:.82rem; font-weight:600; letter-spacing:.3px; border:none; padding:10px 14px; }
        .docs-table tbody td { vertical-align:middle; font-size:.87rem; padding:10px 14px; border-bottom:1px solid #f0f0f0; }
        .docs-table tbody tr:last-child td { border-bottom:none; }
        .docs-table tbody tr:hover { background:#fff8f8; }

        /* Badges */
        .badge-pending  { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
        .badge-approved { background:#d1fae5; color:#065f46; border:1px solid #34d399; }
        .badge-rejected { background:#fee2e2; color:#991b1b; border:1px solid #f87171; }
        .status-badge   { padding:4px 10px; border-radius:50px; font-size:.75rem; font-weight:700; }

        /* Doc type icons */
        .doc-type-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
        .doc-type-icon.pdf  { background:#fee2e2; color:#dc3545; }
        .doc-type-icon.img  { background:#dbeafe; color:#2563eb; }
        .doc-type-icon.doc  { background:#ede9fe; color:#7c3aed; }
        .doc-type-icon.file { background:#f3f4f6; color:#6b7280; }

        /* Alert */
        .upload-alert { border-radius:8px; font-size:.88rem; }

        /* Empty state */
        .empty-state { padding:48px 24px; text-align:center; color:#9ca3af; }
        .empty-state i { font-size:3rem; margin-bottom:12px; }
        .empty-state p { font-size:.9rem; margin:0; }

        /* Filter bar */
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; align-items:center; }
        .filter-bar .form-select { max-width:180px; font-size:.84rem; padding:6px 10px; }
        .filter-bar input { max-width:200px; font-size:.84rem; padding:6px 10px; }

        /* Image Viewer Modal */
        .image-viewer-modal { display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,.8); align-items:center; justify-content:center; }
        .image-viewer-modal.active { display:flex; }
        .image-viewer-content { background:white; border-radius:12px; max-width:90%; max-height:90%; padding:0; position:relative; box-shadow:0 8px 32px rgba(0,0,0,.3); }
        .image-viewer-content img { max-width:100%; max-height:85vh; display:block; border-radius:12px 12px 0 0; }
        .image-viewer-footer { padding:12px 16px; background:#f8f9fa; border-top:1px solid #dee2e6; border-radius:0 0 12px 12px; display:flex; justify-content:space-between; align-items:center; font-size:.85rem; color:#666; }
        .image-viewer-close { position:absolute; top:12px; right:12px; width:36px; height:36px; background:rgba(255,255,255,.95); border:none; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#333; font-size:1.2rem; transition:all .2s; z-index:10000; }
        .image-viewer-close:hover { background:rgba(255,255,255,1); transform:scale(1.1); }

        /* Clickable document row */
        .document-row { cursor:pointer; transition:background .15s; }
        .document-row:hover { background:#f8f8ff !important; }
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
        <div class="nav-item"><a href="./documents.php" class="nav-link active"><i class="fas fa-file-alt"></i> Documents</a></div>
        <div class="nav-item"><a href="./inspection-history.php" class="nav-link"><i class="fas fa-history"></i> Inspection History</a></div>
    </nav>
    <div class="nav-item"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>

<div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <div style="width:52px;height:52px;background:rgba(255,255,255,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                <i class="fas fa-folder-open"></i>
            </div>
            <div>
                <h2><i class="fas fa-file-alt me-2"></i>My Documents</h2>
                <p>Upload and track your fire safety compliance documents for BFP review.</p>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 pb-5">
        <div class="row g-4">

            <!-- LEFT: Upload Panel -->
            <div class="col-xl-5 col-lg-5">
                <div class="bfp-card">
                    <div class="bfp-card-header">
                        <i class="fas fa-cloud-upload-alt"></i> Upload New Document
                    </div>
                    <div class="bfp-card-body">

                        <!-- Step indicators -->
                        <div class="steps-row">
                            <div class="step-item">
                                <div class="step-num active" id="snum1">1</div>
                                <div class="step-label"><strong>Establishment</strong>Select your property</div>
                            </div>
                            <div class="step-divider"></div>
                            <div class="step-item">
                                <div class="step-num" id="snum2">2</div>
                                <div class="step-label"><strong>Document Type</strong>Choose category</div>
                            </div>
                            <div class="step-divider"></div>
                            <div class="step-item">
                                <div class="step-num" id="snum3">3</div>
                                <div class="step-label"><strong>File</strong>Attach &amp; submit</div>
                            </div>
                        </div>

                        <!-- Alert -->
                        <div id="uploadAlert" class="alert upload-alert d-none mb-3"></div>

                        <!-- Step 1: Establishment -->
                        <div class="mb-4">
                            <label class="form-label"><span class="text-danger me-1">*</span>Establishment</label>
                            <select id="estSelect" class="form-select" onchange="onEstChange()">
                                <option value="">— Select Establishment —</option>
                            </select>
                        </div>

                        <!-- Step 2: Document Type -->
                        <div class="mb-4">
                            <label class="form-label"><span class="text-danger me-1">*</span>Document Type</label>
                            <select id="docType" class="form-select" onchange="onDocTypeChange()">
                                <option value="">— Select Document Type —</option>
                                <option value="Business Permit">Business Permit</option>
                                <option value="Fire Safety Inspection Certificate">Fire Safety Inspection Certificate</option>
                                <option value="Electrical Inspection Certificate">Electrical Inspection Certificate</option>
                                <option value="Building Plan">Building Plan</option>
                                <option value="Occupancy Permit">Occupancy Permit</option>
                                <option value="Sanitary Permit">Sanitary Permit</option>
                                <option value="Mayor's Permit">Mayor's Permit</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- Step 3: Drop Zone -->
                        <div class="mb-4">
                            <label class="form-label"><span class="text-danger me-1">*</span>Document File</label>
                            <div class="upload-zone" id="uploadZone"
                                 onclick="triggerFileInput()"
                                 ondragover="handleDragOver(event)"
                                 ondrop="handleDrop(event)"
                                 ondragleave="document.getElementById('uploadZone').classList.remove('drag-over')">
                                <i class="fas fa-cloud-upload-alt upload-icon" id="uploadZoneIcon"></i>
                                <p class="mb-1 mt-2 fw-semibold text-secondary" id="uploadZoneText">Click or drag &amp; drop your file here</p>
                                <p class="upload-hint">Supported: PDF, JPEG, PNG, DOC, DOCX &nbsp;&middot;&nbsp; Max 10 MB</p>
                            </div>
                            <div id="fileChipWrap" class="text-center"></div>
                            <input type="file" id="fileInput" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                   style="display:none" onchange="onFileSelected(this)"/>
                        </div>

                        <!-- Progress -->
                        <div id="uploadProgress" class="upload-progress-wrap d-none">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Uploading…</small>
                                <small class="text-muted" id="progressPct">0%</small>
                            </div>
                            <div class="progress">
                                <div id="progressBar" class="progress-bar bg-danger progress-bar-striped progress-bar-animated" style="width:0%"></div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mt-3">
                            <button class="btn-submit-doc" id="submitBtn" onclick="submitDocument()" disabled>
                                <i class="fas fa-paper-plane me-2"></i>Submit Document
                            </button>
                        </div>

                        <p class="text-muted text-center mt-3 mb-0" style="font-size:.78rem;">
                            <i class="fas fa-info-circle me-1 text-danger"></i>
                            Documents will be reviewed by the CRO. Status updates appear in the list.
                        </p>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Documents List -->
            <div class="col-xl-7 col-lg-7">
                <div class="bfp-card">
                    <div class="bfp-card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-list-alt me-2"></i>Submitted Documents</span>
                        <button class="btn btn-sm btn-light" onclick="loadDocuments()" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="bfp-card-body">

                        <!-- Filter bar -->
                        <div class="filter-bar">
                            <select id="filterEst" class="form-select" onchange="filterDocs()">
                                <option value="">All Establishments</option>
                            </select>
                            <select id="filterStatus" class="form-select" onchange="filterDocs()">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <input type="text" id="filterSearch" class="form-control" placeholder="Search…" oninput="filterDocs()"/>
                        </div>

                        <div id="docsContainer">
                            <div class="empty-state">
                                <i class="fas fa-folder-open d-block"></i>
                                <p>Your submitted documents will appear here.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /row -->
    </div>
</div>

<!-- Image Viewer Modal -->
<div class="image-viewer-modal" id="imageViewerModal">
    <div class="image-viewer-content">
        <button class="image-viewer-close" onclick="closeImageViewer()"><i class="fas fa-times"></i></button>
        <img id="viewerImage" src="" alt="Document Image" />
        <div class="image-viewer-footer">
            <span id="viewerFileName"></span>
            <span id="viewerFileSize"></span>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
let selectedFile = null;
let allDocs = [];

// Load establishments
async function loadEstablishments() {
    try {
        const res  = await fetch('../../utility/getMyEstablishment.php');
        const d    = await res.json();
        const list = Array.isArray(d) ? d : (d.data || d.establishments || []);

        const sel       = document.getElementById('estSelect');
        const filterSel = document.getElementById('filterEst');

        list.forEach(e => {
            const name = e.name || e.business_name || ('Establishment #' + e.id);
            [sel, filterSel].forEach(target => {
                const opt = document.createElement('option');
                opt.value = e.id;
                opt.textContent = name;
                target.appendChild(opt);
            });
        });

        if (list.length === 1) { sel.value = list[0].id; onEstChange(); }
    } catch(err) { console.error('Failed to load establishments:', err); }
}

// Update step indicators
function updateSteps() {
    const est  = document.getElementById('estSelect').value;
    const type = document.getElementById('docType').value;

    setStep(1, est               ? 'done'   : 'active');
    setStep(2, est && type       ? 'done'   : est ? 'active' : '');
    setStep(3, est && type && selectedFile ? 'done' : est && type ? 'active' : '');

    document.getElementById('submitBtn').disabled = !(est && type && selectedFile);
}

function setStep(n, state) {
    const el = document.getElementById('snum' + n);
    el.className = 'step-num' + (state === 'done' ? ' done' : state === 'active' ? ' active' : '');
}

function onEstChange()     { loadDocuments(); updateSteps(); }
function onDocTypeChange() { updateSteps(); }

// File selection
function triggerFileInput() {
    const estId  = document.getElementById('estSelect').value;
    const docTyp = document.getElementById('docType').value;
    if (!estId)  { showAlert('Please select an establishment first.', 'warning'); return; }
    if (!docTyp) { showAlert('Please select a document type first.', 'warning'); return; }
    document.getElementById('fileInput').click();
}

function onFileSelected(input) {
    if (input.files && input.files[0]) setSelectedFile(input.files[0]);
}

function setSelectedFile(file) {
    selectedFile = file;
    const zone = document.getElementById('uploadZone');
    zone.classList.add('has-file');
    document.getElementById('uploadZoneIcon').className = 'fas fa-check-circle upload-icon';
    document.getElementById('uploadZoneText').textContent = 'File selected — ready to submit';
    const sizeLabel = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    document.getElementById('fileChipWrap').innerHTML =
        '<div class="file-chip" title="' + file.name + '">' +
        '<i class="fas fa-paperclip"></i>' +
        '<span style="max-width:220px;overflow:hidden;text-overflow:ellipsis">' + file.name + '</span>' +
        '<span style="color:#888;font-weight:400">(' + sizeLabel + ')</span>' +
        '<span class="remove-file" onclick="removeFile(event)" title="Remove"><i class="fas fa-times-circle"></i></span>' +
        '</div>';
    updateSteps();
}

function removeFile(e) {
    e.stopPropagation();
    selectedFile = null;
    document.getElementById('fileInput').value = '';
    document.getElementById('fileChipWrap').innerHTML = '';
    const zone = document.getElementById('uploadZone');
    zone.classList.remove('has-file');
    document.getElementById('uploadZoneIcon').className = 'fas fa-cloud-upload-alt upload-icon';
    document.getElementById('uploadZoneText').textContent = 'Click or drag & drop your file here';
    updateSteps();
}

function handleDragOver(e) {
    e.preventDefault();
    document.getElementById('uploadZone').classList.add('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('uploadZone').classList.remove('drag-over');
    const estId  = document.getElementById('estSelect').value;
    const docTyp = document.getElementById('docType').value;
    if (!estId)  { showAlert('Please select an establishment first.', 'warning'); return; }
    if (!docTyp) { showAlert('Please select a document type first.', 'warning'); return; }
    if (e.dataTransfer.files.length) setSelectedFile(e.dataTransfer.files[0]);
}

// Submit
async function submitDocument() {
    if (!selectedFile) return;
    const estId  = document.getElementById('estSelect').value;
    const docTyp = document.getElementById('docType').value;
    if (!estId || !docTyp) return;

    const formData = new FormData();
    formData.append('establishment_id', estId);
    formData.append('document_type', docTyp);
    formData.append('document', selectedFile);   // must match $_FILES['document'] in PHP

    const btn      = document.getElementById('submitBtn');
    const progWrap = document.getElementById('uploadProgress');
    const progBar  = document.getElementById('progressBar');
    const progPct  = document.getElementById('progressPct');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading…';
    progWrap.classList.remove('d-none');

    let pct = 0;
    const ticker = setInterval(() => {
        pct = pct < 85 ? pct + 5 : pct;
        progBar.style.width = pct + '%';
        progPct.textContent = pct + '%';
    }, 120);

    try {
        const res = await fetch('../../utility/uploadDocument.php', { method: 'POST', body: formData });
        const d   = await res.json();

        clearInterval(ticker);
        progBar.style.width = '100%';
        progPct.textContent = '100%';
        setTimeout(() => { progWrap.classList.add('d-none'); progBar.style.width = '0%'; }, 600);

        if (d.success) {
            showAlert('Document submitted successfully! It is now pending CRO review.', 'success');
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Document';
            removeFile({ stopPropagation: () => {} });
            document.getElementById('docType').value = '';
            updateSteps();
            loadDocuments();
        } else {
            showAlert(d.message || 'Upload failed. Please try again.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Document';
            updateSteps();
        }
    } catch(e) {
        clearInterval(ticker);
        progWrap.classList.add('d-none');
        showAlert('Network error. Please check your connection and try again.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Document';
        updateSteps();
    }
}

// Load all documents
async function loadDocuments() {
    const container = document.getElementById('docsContainer');
    container.innerHTML = '<p class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading documents…</p>';
    try {
        const res = await fetch('../../utility/uploadDocument.php');
        const d   = await res.json();
        allDocs = (d.success && d.documents) ? d.documents : [];
        filterDocs();
    } catch(e) {
        container.innerHTML = '<p class="text-danger text-center py-3"><i class="fas fa-exclamation-triangle me-1"></i>Error loading documents.</p>';
    }
}

function filterDocs() {
    const estF    = document.getElementById('filterEst').value;
    const statF   = document.getElementById('filterStatus').value;
    const search  = document.getElementById('filterSearch').value.toLowerCase();

    let rows = allDocs;
    if (estF)   rows = rows.filter(d => String(d.establishment_id) === estF);
    if (statF)  rows = rows.filter(d => (d.status || '').toLowerCase() === statF);
    if (search) rows = rows.filter(d =>
        (d.document_type  || '').toLowerCase().includes(search) ||
        (d.original_name  || d.filename || '').toLowerCase().includes(search) ||
        (d.establishment_name || '').toLowerCase().includes(search));

    renderDocs(rows);
}

function renderDocs(docs) {
    const container = document.getElementById('docsContainer');

    if (!docs.length) {
        container.innerHTML =
            '<div class="empty-state"><i class="fas fa-folder-open d-block"></i><p>' +
            (allDocs.length ? 'No documents match your filter.' : 'No documents submitted yet. Use the upload form to get started.') +
            '</p></div>';
        return;
    }

    const total    = docs.length;
    const pending  = docs.filter(d => d.status === 'pending').length;
    const approved = docs.filter(d => d.status === 'approved').length;
    const rejected = docs.filter(d => d.status === 'rejected').length;

    var rows = docs.map(function(doc) {
        var ext = (doc.original_name || doc.filename || '').split('.').pop().toLowerCase();
        var iconCls = ext === 'pdf' ? 'pdf' : ['jpg','jpeg','png'].includes(ext) ? 'img' : ['doc','docx'].includes(ext) ? 'doc' : 'file';
        var iconFa  = ext === 'pdf' ? 'fa-file-pdf' : ['jpg','jpeg','png'].includes(ext) ? 'fa-file-image' : ['doc','docx'].includes(ext) ? 'fa-file-word' : 'fa-file-alt';
        var badge   = doc.status === 'approved' ? 'badge-approved' : doc.status === 'rejected' ? 'badge-rejected' : 'badge-pending';
        var statLbl = doc.status ? (doc.status.charAt(0).toUpperCase() + doc.status.slice(1)) : 'Pending';
        var uploaded = doc.createdAt ? new Date(doc.createdAt).toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'}) : '—';
        var size     = doc.file_size ? (doc.file_size/1024/1024).toFixed(2)+' MB' : '';
        var isImage  = ['jpg','jpeg','png'].includes(ext);
        var docId    = doc.id;
        var cursorStyle = isImage ? 'style="cursor:pointer"' : '';
        return '<tr class="document-row' + (isImage ? ' image-doc' : '') + '" ' + cursorStyle + ' data-doc-id="' + docId + '" data-filename="' + (doc.original_name||doc.filename||'') + '" data-filesize="' + size + '" data-is-image="' + (isImage ? 'true' : 'false') + '">' +
            '<td><div class="doc-type-icon ' + iconCls + '"><i class="fas ' + iconFa + '"></i></div></td>' +
            '<td><div class="fw-semibold" style="font-size:.87rem">' + (doc.document_type||'—') + '</div>' +
                '<div style="font-size:.75rem;color:#888">' + (doc.original_name||doc.filename||'') + (size ? ' · '+size : '') + '</div></td>' +
            '<td style="font-size:.84rem">' + (doc.establishment_name||'—') + '</td>' +
            '<td style="font-size:.82rem;white-space:nowrap">' + uploaded + '</td>' +
            '<td><span class="status-badge ' + badge + '">' + statLbl + '</span></td>' +
            '<td><small class="text-muted">' + (doc.review_notes||'—') + '</small></td>' +
            '</tr>';
    }).join('');

    container.innerHTML =
        '<div class="row g-2 mb-3">' +
            '<div class="col-3"><div style="background:#f0f2f5;border-radius:8px;padding:10px;text-align:center"><div style="font-size:1.3rem;font-weight:700;color:#333">' + total + '</div><div style="font-size:.72rem;color:#888">Total</div></div></div>' +
            '<div class="col-3"><div style="background:#fff3cd;border-radius:8px;padding:10px;text-align:center"><div style="font-size:1.3rem;font-weight:700;color:#856404">' + pending + '</div><div style="font-size:.72rem;color:#856404">Pending</div></div></div>' +
            '<div class="col-3"><div style="background:#d1fae5;border-radius:8px;padding:10px;text-align:center"><div style="font-size:1.3rem;font-weight:700;color:#065f46">' + approved + '</div><div style="font-size:.72rem;color:#065f46">Approved</div></div></div>' +
            '<div class="col-3"><div style="background:#fee2e2;border-radius:8px;padding:10px;text-align:center"><div style="font-size:1.3rem;font-weight:700;color:#991b1b">' + rejected + '</div><div style="font-size:.72rem;color:#991b1b">Rejected</div></div></div>' +
        '</div>' +
        '<div class="table-responsive">' +
        '<table class="table docs-table mb-0">' +
            '<thead><tr>' +
                '<th style="width:36px"></th>' +
                '<th>Document</th>' +
                '<th>Establishment</th>' +
                '<th>Uploaded</th>' +
                '<th>Status</th>' +
                '<th>Reviewer Notes</th>' +
            '</tr></thead>' +
            '<tbody>' + rows + '</tbody>' +
        '</table></div>';
    
    // Attach event delegation after rendering
    setTimeout(function() {
        attachDocumentRowListeners();
    }, 0);
}

function showAlert(msg, type) {
    var icons = { success:'fa-check-circle', danger:'fa-times-circle', warning:'fa-exclamation-triangle', info:'fa-info-circle' };
    var el = document.getElementById('uploadAlert');
    el.className = 'alert alert-' + type + ' upload-alert d-flex align-items-center gap-2';
    el.innerHTML = '<i class="fas ' + (icons[type]||'fa-info-circle') + '"></i><span>' + msg + '</span>';
    el.classList.remove('d-none');
    if (type !== 'danger') setTimeout(function(){ el.classList.add('d-none'); }, 6000);
}

// Image Viewer Functions
function attachDocumentRowListeners() {
    var rows = document.querySelectorAll('.document-row.image-doc');
    rows.forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.closest('.text-muted')) return; // Don't trigger on notes column
            var docId = parseInt(this.getAttribute('data-doc-id'));
            viewImage(docId);
        });
    });
}

function viewImage(docId) {
    var doc = allDocs.find(d => d.id === docId);
    if (!doc) return;
    
    var filename = doc.filename || 'document';
    var ext = filename.split('.').pop().toLowerCase();
    var isImage = ['jpg','jpeg','png'].includes(ext);
    
    if (!isImage) return; // Only images can be viewed
    
    // Construct image URL
    var imageUrl = '../../uploads/documents/' + filename;
    
    // Display in modal
    document.getElementById('viewerImage').src = imageUrl;
    document.getElementById('viewerFileName').textContent = doc.original_name || filename;
    document.getElementById('viewerFileSize').textContent = (doc.file_size ? (doc.file_size/1024/1024).toFixed(2)+' MB' : '');
    document.getElementById('imageViewerModal').classList.add('active');
}

function closeImageViewer() {
    document.getElementById('imageViewerModal').classList.remove('active');
    document.getElementById('viewerImage').src = '';
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageViewer();
    }
});

// Close modal on background click
document.addEventListener('click', function(e) {
    var modal = document.getElementById('imageViewerModal');
    if (e.target === modal) {
        closeImageViewer();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    loadEstablishments();
    loadDocuments();
});
</script>
</body>
</html>
