<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP SiteProfiler - Reports</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.css"
      rel="stylesheet"
    />

    <link
      rel="stylesheet"
      href="../../assets/styles/layout/admin-reports.css"
    />
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
    <link rel="stylesheet" href="../../assets/styles/components/header.css" />
  </head>
  <body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
      <div class="logo-section">
        <div class="logo">
          <i
            class="fas fa-shield-alt"
            style="color: var(--bfp-red); font-size: 24px"
          ></i>
        </div>
        <h5 class="mb-0">BFP SiteProfiler</h5>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-item">
          <a href="./dashboard.php" class="nav-link">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
          </a>
        </div>
        <div class="nav-item">
          <a href="./establishments.php" class="nav-link">
            <i class="fas fa-building"></i>
            Establishments
          </a>
        </div>
        <div class="nav-item">
          <a href="#" class="nav-link active">
            <i class="fas fa-calendar-check"></i>
            Schedule Inspections
          </a>
        </div>
        <div class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-map-marker-alt"></i>
            GIS Map
          </a>
        </div>
        <div class="nav-item">
          <a href="./reports.php" class="nav-link">
            <i class="fas fa-file-alt"></i>
            Reports
          </a>
        </div>
        <div class="nav-item">
          <a href="./user-management.php" class="nav-link">
            <i class="fas fa-users"></i>
            User Management
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

    <div class="main-content">
      <div class="top-header">
        <div class="d-flex align-items-center">
          <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
          </button>
          <h4 class="mb-0">Reports</h4>
        </div>
        <div class="admin-info">
          <i class="fas fa-bell text-danger"></i>
          <div class="admin-avatar">AD</div>
          <span class="ms-2">Admin User</span>
        </div>
      </div>

      <div class="content-wrapper">
        <!-- Report Filters -->
        <!-- <div class="card mb-4">
          <div class="card-body">
            <div class="row"> -->
              <!-- <div class="col-md-3">
                <label class="form-label">Report Type</label>
                <select class="form-select" id="reportType">
                  <option>Compliance Summary</option>
                  <option>Detailed Inspection</option>
                  <option>Violation Analysis</option>
                  <option>Safety Metrics</option>
                </select>
              </div> -->
              <!-- <div class="col-md-3">
                <label class="form-label">Time Period</label>
                <select class="form-select" id="timePeriod">
                  <option>This Month</option>
                  <option>Last Month</option>
                  <option>This Quarter</option>
                  <option>Last Quarter</option>
                  <option>This Year</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Establishment Type</label>
                <select class="form-select" id="establishmentType">
                  <option>All Types</option>
                  <option>Commercial</option>
                  <option>Institutional</option>
                  <option>Government</option>
                  <option>Residential</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Compliance Status</label>
                <select class="form-select" id="complianceStatus">
                  <option>All Status</option>
                  <option>Compliant</option>
                  <option>Non-Compliant</option>
                  <option>Pending</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Inspector</label>
                <select class="form-select" id="inspector">
                  <option>All Inspectors</option>
                  <option>Juan Dela Cruz</option>
                  <option>Maria Santos</option>
                  <option>Roberto Pacquiao</option>
                </select>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-md-9 d-flex align-items-end">
                <button class="btn btn-primary me-2">
                  <i class="fas fa-file-alt me-1"></i> Generate Report
                </button>
                <button class="btn btn-secondary me-2">
                  <i class="fas fa-sync me-1"></i> Reset Filters
                </button>
                <button class="btn btn-success">
                  <i class="fas fa-file-pdf me-1"></i> Export to PDF
                </button>
              </div>
            </div>
          </div>
        </div> -->

        <!-- Dashboard Cards -->
        <div class="row mb-4">
          <!-- Compliance Overview -->
          <div class="col-lg-4 mb-3">
            <div class="card h-100">
              <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>Compliance Overview
              </div>
              <div class="card-body">
                <canvas id="complianceChart" class="chart-container"></canvas>
              </div>
            </div>
          </div>

          <!-- Inspection Activity -->
          <div class="col-lg-4 mb-3">
            <div class="card h-100">
              <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i>Inspection Activity
              </div>
              <div class="card-body">
                <canvas id="inspectionChart" class="chart-container"></canvas>
              </div>
            </div>
          </div>

          <!-- Violations by Type -->
          <div class="col-lg-4 mb-3">
            <div class="card h-100">
              <div class="card-header">
                <i class="fas fa-exclamation-triangle me-2"></i>Violations by
                Type
              </div>
              <div class="card-body">
                <canvas id="violationsChart" class="violations-chart"></canvas>
                <div class="mt-3">
                  <div class="row text-center">
                    <div class="col-4">
                      <div class="h5 text-danger">12</div>
                      <small class="text-muted">Fire Exit</small>
                    </div>
                    <div class="col-4">
                      <div class="h5 text-warning">8</div>
                      <small class="text-muted">Equipment</small>
                    </div>
                    <div class="col-4">
                      <div class="h5 text-info">5</div>
                      <small class="text-muted">Electrical</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Establishment Compliance Details
        <div class="card">
          <div class="card-header">
            <i class="fas fa-building me-2"></i>Establishment Compliance Details
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover mb-0" id="complianceTable">
                <thead class="table-light">
                  <tr>
                    <th>Establishment</th>
                    <th>Type</th>
                    <th>Last Inspection</th>
                    <th>Inspector</th>
                    <th>Status</th>
                    <th>Violations</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="tableBody">
                  
                </tbody>
              </table>
            </div>

            
            <div
              class="pagination-container mt-3 d-flex justify-content-between"
            >
              <div>
                <span class="text-muted"
                  >Showing <span id="showingStart">1</span> to
                  <span id="showingEnd">5</span> of
                  <span id="totalRecords">25</span> results</span
                >
              </div>
              <nav>
                <ul class="pagination pagination-sm mb-0" id="pagination">
                  
                </ul>
              </nav>
            </div>
          </div>
        </div> -->
      </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewDetailsModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Establishment Details</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <h6 class="text-primary">Basic Information</h6>
                <p>
                  <strong>Name:</strong>
                  <span id="modalEstablishmentName"></span>
                </p>
                <p>
                  <strong>Type:</strong>
                  <span id="modalEstablishmentType"></span>
                </p>
                <p><strong>Address:</strong> <span id="modalAddress"></span></p>
                <p><strong>Contact:</strong> <span id="modalContact"></span></p>
              </div>
              <div class="col-md-6">
                <h6 class="text-primary">Inspection Details</h6>
                <p>
                  <strong>Last Inspection:</strong>
                  <span id="modalLastInspection"></span>
                </p>
                <p>
                  <strong>Inspector:</strong> <span id="modalInspector"></span>
                </p>
                <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                <p>
                  <strong>Violations:</strong>
                  <span id="modalViolations"></span>
                </p>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-12">
                <h6 class="text-primary">Recent Violations</h6>
                <ul
                  id="modalViolationsList"
                  class="list-group list-group-flush"
                >
                  <!-- Violations will be populated here -->
                </ul>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
            <a href="./schedule-inspections.html" class="btn btn-primary">
              Schedule Inspection
            </a>
            <button type="button" class="btn btn-success">
              Generate Certificate
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap bundle (if needed for modals) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js must load before your code -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <script src="../../assets/scripts/reports.js"></script>
    <script src="../../assets/scripts/components/sidebar.js"></script>
  </body>
</html>
