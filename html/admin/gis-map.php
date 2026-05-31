<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: ../index.php');
    exit;
}
$adminName = htmlspecialchars($_SESSION['fullname'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BFP Admin – GIS Map</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; }
        body { background:#f8f9fa; font-family:'Segoe UI',sans-serif; }
        .main-content { margin-left:250px; padding:20px; }
        .page-header { background:#fff; padding:15px 20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.07); margin-bottom:20px; }
        #gisMap { height:520px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.1); }
        .controls-card { background:#fff; border-radius:10px; padding:15px; box-shadow:0 2px 8px rgba(0,0,0,.07); margin-bottom:15px; }
        .stats-bar { background:#fff; border-radius:10px; padding:12px 20px; box-shadow:0 2px 8px rgba(0,0,0,.07); margin-bottom:15px; display:flex; gap:30px; flex-wrap:wrap; }
        .stat-item { text-align:center; }
        .stat-item .val { font-size:1.4rem; font-weight:700; }
        .legend { background:#fff; padding:10px 14px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.15); line-height:1.9; }
        .legend-dot { display:inline-block; width:12px; height:12px; border-radius:50%; margin-right:6px; }
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
        <div class="nav-item"><a href="./establishments.php" class="nav-link"><i class="fas fa-building"></i> Establishments</a></div>
        <div class="nav-item"><a href="./schedule-inspections.php" class="nav-link"><i class="fas fa-calendar-check"></i> Schedule Inspections</a></div>
        <div class="nav-item"><a href="./certificate-authorization.php" class="nav-link"><i class="fas fa-certificate"></i> Certificate Authorization</a></div>
        <div class="nav-item"><a href="./gis-map.php" class="nav-link active"><i class="fas fa-map-marker-alt"></i> GIS Map</a></div>
        <div class="nav-item"><a href="./reports.php" class="nav-link"><i class="fas fa-file-alt"></i> Reports</a></div>
        <div class="nav-item"><a href="./user-management.php" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-item"><a href="./activity-logs.php" class="nav-link"><i class="fas fa-history"></i> Activity Logs</a></div>
    </nav>

    <div class="sidebar-logout">
        <a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-map-marked-alt text-danger me-2"></i>GIS Compliance Map</h4>
            <small class="text-muted">All establishments — color-coded by compliance status</small>
        </div>
        <span class="badge bg-danger">Admin View</span>
    </div>

    <!-- Stats bar -->
    <div class="stats-bar" id="statsBar">
        <div class="stat-item"><div class="val text-primary" id="statAll">—</div><div class="text-muted small">Total</div></div>
        <div class="stat-item"><div class="val text-success" id="statCompliant">—</div><div class="text-muted small">Compliant</div></div>
        <div class="stat-item"><div class="val text-warning" id="statPartial">—</div><div class="text-muted small">Partial</div></div>
        <div class="stat-item"><div class="val text-danger" id="statNonCompliant">—</div><div class="text-muted small">Non-Compliant</div></div>
        <div class="stat-item"><div class="val text-secondary" id="statNoInspection">—</div><div class="text-muted small">No Inspection</div></div>
    </div>

    <!-- Filters -->
    <div class="controls-card d-flex flex-wrap gap-2 align-items-center">
        <select class="form-select form-select-sm w-auto" id="filterCompliance" onchange="applyFilter()">
            <option value="">All Compliance</option>
            <option value="compliant">Compliant</option>
            <option value="partial">Partially Compliant</option>
            <option value="non_compliant">Non-Compliant</option>
            <option value="no_inspection">No Inspection</option>
        </select>
        <select class="form-select form-select-sm w-auto" id="filterType" onchange="applyFilter()">
            <option value="">All Types</option>
        </select>
        <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()"><i class="fas fa-undo me-1"></i>Reset</button>
        <span class="ms-auto text-muted small" id="markerCount"></span>
    </div>

    <!-- Map -->
    <div id="gisMap"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../../assets/scripts/components/sidebar.js"></script>
<script>
let map, markersLayer, allMarkers = [];

function initMap() {
    map = L.map('gisMap').setView([13.5766, 124.2422], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 19
    }).addTo(map);
    markersLayer = L.layerGroup().addTo(map);

    // Legend
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = () => {
        const div = L.DomUtil.create('div', 'legend');
        div.innerHTML = `<strong>Compliance</strong><br>
            <span class="legend-dot" style="background:#28a745"></span>Compliant<br>
            <span class="legend-dot" style="background:#ffc107"></span>Partially Compliant<br>
            <span class="legend-dot" style="background:#dc3545"></span>Non-Compliant<br>
            <span class="legend-dot" style="background:#6c757d"></span>No Inspection`;
        return div;
    };
    legend.addTo(map);
}

function getIcon(color) {
    return L.divIcon({
        html: `<div style="width:16px;height:16px;background:${color};border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>`,
        className: '', iconSize: [16,16], iconAnchor: [8,8]
    });
}

async function loadMarkers() {
    try {
        const res  = await fetch('../../utility/getGISData.php');
        const data = await res.json();
        if (!data.success) { console.error(data.message); return; }

        allMarkers = data.establishments || [];

        // Populate type filter
        const types = [...new Set(allMarkers.map(e => e.type).filter(Boolean))];
        const sel   = document.getElementById('filterType');
        types.forEach(t => { const o = document.createElement('option'); o.value=t; o.textContent=t; sel.appendChild(o); });

        renderMarkers(allMarkers);
    } catch(e) { console.error(e); }
}

function renderMarkers(markers) {
    markersLayer.clearLayers();
    markers.forEach(e => {
        const lat = parseFloat(e.latitude);
        const lng = parseFloat(e.longitude);
        if (!lat || !lng) return;
        const m = L.marker([lat,lng], { icon: getIcon(e.markerColor || '#6c757d') });
        const comp = (e.compliance_status || 'no inspection').replace(/_/g,' ');
        m.bindPopup(`<strong>${e.name}</strong><br>
            <span class="text-muted">${e.type || ''}</span><br>
            Owner: ${e.owner_name || '—'}<br>
            Compliance: <strong>${comp}</strong><br>
            Last Inspection: ${e.last_inspection_date ? new Date(e.last_inspection_date).toLocaleDateString() : 'None'}<br>
            Certificate: ${e.certificate_number || 'None'}`);
        markersLayer.addLayer(m);
    });
    updateStats(markers);
    document.getElementById('markerCount').textContent = markers.length + ' establishments shown';
}

function updateStats(markers) {
    document.getElementById('statAll').textContent         = markers.length;
    document.getElementById('statCompliant').textContent   = markers.filter(m=>m.markerColor==='#28a745').length;
    document.getElementById('statPartial').textContent     = markers.filter(m=>m.markerColor==='#ffc107').length;
    document.getElementById('statNonCompliant').textContent= markers.filter(m=>m.markerColor==='#dc3545').length;
    document.getElementById('statNoInspection').textContent= markers.filter(m=>m.markerColor==='#6c757d').length;
}

function applyFilter() {
    const comp = document.getElementById('filterCompliance').value;
    const type = document.getElementById('filterType').value;
    const colorMap = { compliant:'#28a745', partial:'#ffc107', non_compliant:'#dc3545', no_inspection:'#6c757d' };

    const filtered = allMarkers.filter(e => {
        const matchComp = !comp || e.markerColor === colorMap[comp];
        const matchType = !type || e.type === type;
        return matchComp && matchType;
    });
    renderMarkers(filtered);
}

function resetFilters() {
    document.getElementById('filterCompliance').value = '';
    document.getElementById('filterType').value = '';
    renderMarkers(allMarkers);
}

initMap();
loadMarkers();
</script>
</body>
</html>
