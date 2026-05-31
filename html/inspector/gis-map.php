<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'inspector') {
    header('Location: ../index.php'); exit;
}
$userName = htmlspecialchars($_SESSION['fullname'] ?? 'Inspector');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>BFP Inspector — GIS Map</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
<style>
:root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; }
body { background:#f8f9fa; font-family:'Segoe UI',sans-serif; }
.main-container { padding:30px 20px; padding-left:270px; }
.page-header { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; padding:25px 30px; border-radius:10px; margin-bottom:20px; }
#gisMap { height:calc(100vh - 280px); min-height:500px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.1); }
.legend { background:#fff; padding:12px 16px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.15); line-height:1.8; }
.legend-dot { display:inline-block; width:14px; height:14px; border-radius:50%; margin-right:6px; vertical-align:middle; }
.controls { background:#fff; padding:15px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:15px; }
.stat-pill { display:inline-flex; align-items:center; gap:6px; background:#fff; padding:6px 14px; border-radius:20px; box-shadow:0 1px 4px rgba(0,0,0,.1); font-size:.85rem; font-weight:600; margin:4px; }
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
    <div class="nav-item"><a href="./assigned-inspections.php" class="nav-link"><i class="fas fa-building"></i> Assigned Inspections</a></div>
    <div class="nav-item"><a href="./report-findings.php" class="nav-link"><i class="fas fa-calendar-check"></i> Report Findings</a></div>
    <div class="nav-item"><a href="./gis-map.php" class="nav-link active"><i class="fas fa-map-marked-alt"></i> GIS Map</a></div>
  </nav>
  <div class="nav-item"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>

<div class="main-container">
  <div class="page-header">
    <h2><i class="fas fa-map-marked-alt me-2"></i>GIS Map — My Assigned Establishments</h2>
    <p class="mb-0">View your assigned establishment locations and compliance status</p>
  </div>

  <div class="controls d-flex flex-wrap align-items-center gap-3">
    <div>
      <label class="form-label mb-1 fw-bold">Filter by Compliance:</label>
      <select id="filterCompliance" class="form-select form-select-sm d-inline-block" style="width:auto" onchange="applyFilter()">
        <option value="all">All</option>
        <option value="compliant">Compliant</option>

        <option value="non_compliant">Non-Compliant</option>
        <option value="no_inspection">Pending</option>
      </select>
    </div>
    <div id="statsBar"></div>
  </div>

  <div id="gisMap"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="../../assets/scripts/components/sidebar.js"></script>
<script>
let map, markersLayer, allMarkers = [];

function initMap() {
  map = L.map('gisMap').setView([12.3685, 123.6174], 10);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);
  const legend = L.control({ position: 'bottomright' });
  legend.onAdd = () => {
    const div = L.DomUtil.create('div', 'legend');
    div.innerHTML = `<strong>Compliance Status</strong><br>
      <span class="legend-dot" style="background:#28a745"></span> Compliant<br>

      <span class="legend-dot" style="background:#dc3545"></span> Non-Compliant<br>
      <span class="legend-dot" style="background:#6c757d"></span> Pending`;
    return div;
  };
  legend.addTo(map);
  markersLayer = L.layerGroup().addTo(map);
}

async function loadMarkers() {
  try {
    const res  = await fetch('../../utility/getGISData.php');
    const data = await res.json();
    if (!data.success) return;
    allMarkers = data.markers;
    renderMarkers(data.markers);
    updateStats(data.markers);
  } catch (err) { console.error(err); }
}

function getIcon(color) {
  return L.divIcon({
    className: '',
    html: `<div style="background:${color};width:16px;height:16px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>`,
    iconSize: [16, 16], iconAnchor: [8, 8], popupAnchor: [0, -10]
  });
}

function renderMarkers(markers) {
  markersLayer.clearLayers();
  markers.forEach(m => {
    const marker = L.marker([m.lat, m.lng], { icon: getIcon(m.markerColor) });
    const compLabel = {
      compliant: '✔ Compliant',

      non_compliant: '✘ Non-Compliant',
    }[m.complianceStatus] || '— Pending';
    marker.bindPopup(`
      <div style="min-width:200px;">
        <h6 class="mb-1">${m.name}</h6>
        <small><strong>Type:</strong> ${m.type}</small><br>
        <small><strong>Address:</strong> ${m.address}</small><br>
        <small><strong>Last Inspection:</strong> ${m.lastInspectionDate || 'None'}</small><br>
        <small><strong>Status:</strong> ${compLabel}</small>
      </div>
    `, { maxWidth: 260 });
    markersLayer.addLayer(marker);
  });
  if (markers.length > 0) {
    map.fitBounds(L.latLngBounds(markers.map(m => [m.lat, m.lng])).pad(0.15));
  }
}

function applyFilter() {
  const comp = document.getElementById('filterCompliance').value;
  const filtered = allMarkers.filter(m => {
    if (comp === 'all') return true;
    if (comp === 'no_inspection') return !m.complianceStatus;
    return m.complianceStatus === comp;
  });
  renderMarkers(filtered);
  updateStats(filtered);
}

function updateStats(markers) {
  const c  = markers.filter(m => m.complianceStatus === 'compliant').length;

  const nc = markers.filter(m => m.complianceStatus === 'non_compliant').length;
  const ni = markers.filter(m => !m.complianceStatus).length;
  document.getElementById('statsBar').innerHTML = `
    <span class="stat-pill"><span style="color:#28a745">●</span> ${c} Compliant</span>

    <span class="stat-pill"><span style="color:#dc3545">●</span> ${nc} Non-Compliant</span>
    <span class="stat-pill"><span style="color:#6c757d">●</span> ${ni} Pending</span>
    <span class="stat-pill"><strong>${markers.length}</strong> Total</span>`;
}

initMap();
loadMarkers();
</script>
</body>
</html>
