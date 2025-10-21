<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BFP Assigned Inspections</title>
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
    rel="stylesheet" />
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    rel="stylesheet" />
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"
    rel="stylesheet" />

  <link rel="stylesheet" href="../../assets/styles/components/sidebar.css">
  <link rel="stylesheet" href="../../assets/styles/layout/inspector-assigned-inspections.css">
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="logo-section">
      <div class="logo">
        <i
          class="fas fa-shield-alt"
          style="color: var(--bfp-red); font-size: 24px"></i>
      </div>
      <h5 class="mb-0">BFP SiteProfiler</h5>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-item">
        <a href="#" class="nav-link active">
          <i class="fas fa-tachometer-alt"></i>
          Dashboard
        </a>
      </div>
      <div class="nav-item">
        <a href="./assigned-inspections.php" class="nav-link">
          <i class="fas fa-building"></i>
          Assigned Inspections
        </a>
      </div>
      <div class="nav-item">
        <a href="./report-findings.php" class="nav-link">
          <i class="fas fa-calendar-check"></i>
          Report Findings
        </a>
      </div>
      <div class="nav-item">
        <a href="./gis-map.php" class="nav-link">
          <i class="fas fa-map-marker-alt"></i>
          GIS Map
        </a>
      </div>

    </nav>

    <div class="nav-item">
      <a href="../index.php" class="nav-link">
        <i class="fas fa-sign-out-alt"></i>
        Logout
      </a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header d-flex align-items-center justify-content-between">
      <div>
        <h2 class="text-dark mb-1">Assigned Inspections</h2>
        <h4 class="text-danger mb-0">Your Inspection Assignments</h4>
      </div>
      <button class="btn btn-export" onclick="exportData()">
        <i class="fas fa-download me-2"></i>Export
      </button>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
      <div class="row d-flex align-items-center justify-content-between">
        <div class="col-md-3">
          <select class="form-select" id="statusFilter">
            <option value="">All Status</option>
            <option value="overdue">Overdue</option>
            <option value="pending">Pending</option>
            <option value="scheduled">Scheduled</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <div class="col-md-2">
          <input
            type="date"
            class="form-control"
            id="fromDate"
            placeholder="mm/dd/yyyy" />
        </div>
        <div class="col-md-2">
          <input
            type="date"
            class="form-control"
            id="toDate"
            placeholder="mm/dd/yyyy" />
        </div>
        <div class="col-md-3">
          <select class="form-select" id="riskFilter">
            <option value="">All Risk Levels</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-filter w-100 d-flex align-items-center gap-2" onclick="applyFilters()">
            <i class="fas fa-filter"></i> Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="table-container">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead class="table-header">
            <tr>
              <th>Establishment</th>
              <th>Type</th>
              <th>Address</th>
              <th>Inspection Date</th>
              <th>Risk Level</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="inspectionTableBody">
            <!-- Table data will be populated by JavaScript -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Map Modal -->
  <div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div
          class="modal-header"
          style="background: var(--bfp-red); color: white">
          <h5 class="modal-title">
            <i class="fas fa-map-marker-alt me-2"></i>
            <span id="mapModalTitle">Inspection Location</span>
          </h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="inspectionMap"></div>
          <div class="mt-3" id="locationInfo">
            <!-- Location details will be populated here -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Report Modal -->
  <div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div
          class="modal-header"
          style="background: var(--bfp-red); color: white">
          <h5 class="modal-title">
            <i class="fas fa-file-alt me-2"></i>
            <span id="reportModalTitle">Inspection Report</span>
          </h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="reportContent">
            <!-- Report content will be populated here -->
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Close
          </button>
          <button
            type="button"
            class="btn btn-report"
            onclick="downloadReport()">
            <i class="fas fa-download me-2"></i>Download Report
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

  <script src="../../assets/scripts/inspector-assigned-inspections.js"></script>
</body>

</html>