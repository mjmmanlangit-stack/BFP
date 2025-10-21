<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP SiteProfiler - Admin Dashboard</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      rel="stylesheet"
    />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
    <link rel="stylesheet" href="../../assets/styles/components/header.css">
    <link rel="stylesheet" href="../../assets/styles/layout/admin-dashboard.css" />
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
          <a href="./schedule-inspections.php" class="nav-link active">
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
    <!-- Main Content -->
    <div class="main-content">
      <!-- Top Header -->
      <div class="top-header">
        <div class="d-flex align-items-center">
          <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
          </button>
          <h4 class="mb-0">Admin Dashboard</h4>
        </div>
        <div class="admin-info">
          <i class="fas fa-bell text-danger"></i>
          <div class="admin-avatar">AD</div>
          <span class="ms-2">Admin User</span>
        </div>
      </div>

      <!-- Content Area -->
      <div class="content-area">
        <!-- Overview Section -->
        <div class="overview-section">
          <h2>Overview</h2>
          <p>Welcome back! Here's what's happening today.</p>

          <div class="action-buttons">
            <button class="btn btn-new-inspection me-2">
              <i class="fas fa-plus"></i> New Inspection
            </button>
            <button class="btn btn-generate-report">
              <i class="fas fa-download"></i> Generate Report
            </button>
          </div>
        </div>

        <!-- Stats Cards Row 1 -->
        <div class="row">
          <div class="col-lg-3 col-md-6">
            <div class="stats-card pending">
              <h6>Pending Registrations</h6>
              <h3>8</h3>
              <div class="trend">2 new today</div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stats-card approved">
              <h6>Approved Establishments</h6>
              <h3>124</h3>
              <div class="trend">5 this week</div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stats-card ongoing">
              <h6>Ongoing Inspections</h6>
              <h3>12</h3>
              <div class="trend">2 overdue</div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stats-card reports">
              <h6>Reports Generated</h6>
              <h3>15</h3>
              <div class="trend">5 this month</div>
            </div>
          </div>
        </div>

        <!-- Stats Cards Row 2 -->
        <div class="row">
          <div class="col-lg-6 col-md-6">
            <div class="stats-card compliant">
              <div class="card-icon"><i class="fas fa-check"></i></div>
              <h6>Compliant</h6>
              <h3>110</h3>
              <div class="trend">96% compliance</div>
            </div>
          </div>
          <div class="col-lg-6 col-md-6">
            <div class="stats-card non-compliant">
              <div class="card-icon">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
              <h6>Non-Compliant</h6>
              <h3>14</h3>
              <div class="trend">Needs attention</div>
            </div>
          </div>
        </div>

        <!-- System Notice -->
        <div class="system-notice">
          <div class="d-flex align-items-center">
            <i class="fas fa-star notice-icon"></i>
            <strong>System Notice</strong>
          </div>
          <p class="mb-0 mt-2">
            Welcome, Admin! Please review pending registrations and assign
            inspectors for this week's schedule. The deadline for monthly
            compliance reports is approaching on June 30.
          </p>
          <div class="notice-actions">
            <a href="./schedule-inspections.php" class="btn btn-sm btn-danger me-2">
               Review Registrations
            </a>
            <a href="./schedule-inspections.php" class="btn btn-sm btn-dark">
               Schedule Inspections
            </a>
          </div>
        </div>

        <!-- Charts and Activity Section -->
        <div class="row mb-4" style="height: 60vh">
          <div class="col-lg-8" style="max-height: 100%">
            <div class="chart-container">
              <div class="chart-header">
                <h5>Compliance Trend</h5>
                <a href="#" class="text-decoration-none"
                  >View Full Report <i class="fas fa-arrow-right"></i
                ></a>
              </div>
              <canvas id="complianceChart"></canvas>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="activity-feed">
              <div
                class="d-flex justify-content-between align-items-center mb-3"
              >
                <h5>Recent Activity</h5>
              </div>

              <div class="activities-cont">
                <div class="activity-item">
                  <div class="activity-icon success">
                    <i class="fas fa-building"></i>
                  </div>
                  <div class="activity-content">
                    <h6>New establishment registered</h6>
                    <p>Vista Town Center</p>
                    <div class="activity-time">2 hours ago</div>
                  </div>
                </div>

                <div class="activity-item">
                  <div class="activity-icon info">
                    <i class="fas fa-clipboard-check"></i>
                  </div>
                  <div class="activity-content">
                    <h6>Inspection completed</h6>
                    <p>Cabanatuan State University</p>
                    <div class="activity-time">1 day ago</div>
                  </div>
                </div>

                <div class="activity-item">
                  <div class="activity-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                  </div>
                  <div class="activity-content">
                    <h6>Non-compliance detected</h6>
                    <p>West Public Market</p>
                    <div class="activity-time">1 week ago</div>
                  </div>
                </div>

                <div class="activity-item">
                  <div class="activity-icon danger">
                    <i class="fas fa-user-plus"></i>
                  </div>
                  <div class="activity-content">
                    <h6>New inspector assigned</h6>
                    <p>Inspector Juan Dela Cruz</p>
                    <div class="activity-time">2 weeks ago</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="map-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5>
                <i class="fas fa-map me-2"></i>GIS Map: Establishment Locations
              </h5>
              <div class="map-controls">
                <button class="btn btn-sm btn-bfp me-2">
                  <i class="fas fa-layer-group"></i> Layers
                </button>
                <button class="btn btn-sm btn-secondary-bfp">
                  <i class="fas fa-filter"></i> Filter
                </button>
              </div>
            </div>
            <div id="map"></div>
          </div>
        </div>

      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    
    <script src="../../assets/scripts/admin-dashboard.js"></script>
    <script src="../../assets/scripts/components/sidebar.js"></script>
  </body>
</html>
