<?php
 include '../../utility/checkingUser.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP Site Profiler - Establishment Owner Portal</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
    <link rel="stylesheet" href="../../assets/styles/components/header.css" />

    <link
      rel="stylesheet"
      href="../../assets/styles/layout/user-dashboard.css"
    />
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
          <a href="./dashboard.php" class="nav-link active">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
          </a>
        </div>
        <div class="nav-item">
          <a href="./my-establishments.php" class="nav-link">
            <i class="fas fa-building"></i>
            My Establishments
          </a>
        </div>
        <div class="nav-item">
          <a href="./certificates.php" class="nav-link ">
            <i class="fas fa-calendar-check"></i>
            Certificates
          </a>
        </div>
        <!-- <div class="nav-item">
          <a href="./gis-map.html" class="nav-link">
            <i class="fas fa-map-marker-alt"></i>
            Documents
          </a>
        </div> -->
      </nav>

      <div class="nav-item">
        <a href="../../utility/logout.php" class="nav-link">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </div>
    </div>


    <!-- Main Content -->
    <div class="main-content" id="mainContent">
      <div class="top-header">
        <div class="d-flex align-items-center">
          <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
          </button>
          <h4 class="mb-0">Establishment Owner Portal</h4>
        </div>
        <div class="admin-info">
          <div class="admin-avatar">JD</div>
          <span class="ms-2">Juan Dela Cruz</span>
        </div>
      </div>

      <!-- Dashboard Content -->
      <main class="dashboard-content">
        <div class="welcome-section">
          <h2 class="welcome-title">Dashboard Overview</h2>
          <p class="welcome-subtitle">
            Welcome back! Here's your establishment's current status.
          </p>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
          <!-- Compliance Status -->
          <div class="stat-card">
            <div class="stat-header">
              <div>
                <div class="stat-title">Compliance Status</div>
                <div class="stat-icon red">
                  <i class="fas fa-shield-alt"></i>
                </div>
              </div>
            </div>
            <div class="stat-value">Compliant</div>
            <div class="stat-subtitle">Last inspection: May 15, 2023</div>
          </div>

          <!-- Upcoming Inspection -->
          <div class="stat-card">
            <div class="stat-header">
              <div>
                <div class="stat-title">Upcoming Inspection</div>
                <div class="stat-icon yellow">
                  <i class="fas fa-calendar-alt"></i>
                </div>
              </div>
            </div>
            <div class="stat-value">June 15, 2025</div>
            <div class="stat-subtitle">Scheduled in 2 months</div>
          </div>

          <!-- Active Certificates -->
          <div class="stat-card">
            <div class="stat-header">
              <div>
                <div class="stat-title">Active Certificates</div>
                <div class="stat-icon green">
                  <i class="fas fa-certificate"></i>
                </div>
              </div>
            </div>
            <div class="stat-value">2</div>
            <div class="stat-subtitle">FSIC valid until May 2024</div>
          </div>

          <!-- Pending Actions -->
          <div class="stat-card">
            <div class="stat-header">
              <div>
                <div class="stat-title">Pending Actions</div>
                <div class="stat-icon blue">
                  <i class="fas fa-tasks"></i>
                </div>
              </div>
            </div>
            <div class="stat-value">1</div>
            <div class="stat-subtitle">Fire extinguisher maintenance due</div>
          </div>
        </div>

        <!-- Establishment Profile Section -->
        <div class="profile-section">
          <div class="profile-header">
            <h3 class="profile-title">Establishment Profile</h3>
            <span class="compliant-badge">Compliant</span>
          </div>
          <p class="text-muted">
            Your establishment profile is complete and up to date. All required
            documents and certificates are valid.
          </p>
        </div>
      </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script src="../../assets/scripts/user-dashboard.js"></script>
  </body>
</html>
