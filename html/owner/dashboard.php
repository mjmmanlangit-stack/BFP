<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BFP Site Profiler - Owner Dashboard</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <link rel="stylesheet" href="../../assets/styles/components/sidebar.css">
  <style>
    :root {
      --bfp-red: #dc3545;
      --bfp-dark-red: #a02834;
      --bfp-gold: #ffc107;
      --bfp-dark: #1a1a1a;
      --bfp-light: #f8f9fa;
    }

    body {
      background-color: var(--bfp-light);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-content {
      padding: 0;
      padding-left: 250px;
    }

    .navbar {
      background: linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
      font-weight: bold;
      color: white !important;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .notification-icon {
      position: relative;
      cursor: pointer;
      color: white;
      font-size: 1.3rem;
      transition: transform 0.2s;
    }

    .notification-icon:hover {
      transform: scale(1.1);
    }

    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: var(--bfp-gold);
      color: var(--bfp-dark);
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: bold;
    }

    .notification-dropdown {
      position: absolute;
      top: 60px;
      right: 20px;
      width: 350px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
      display: none;
      z-index: 1000;
      max-height: 400px;
      overflow-y: auto;
    }

    .notification-dropdown.show {
      display: block;
      animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .notification-header {
      padding: 15px;
      border-bottom: 1px solid #eee;
      font-weight: bold;
      color: var(--bfp-dark);
    }

    .notification-item {
      padding: 15px;
      border-bottom: 1px solid #eee;
      cursor: pointer;
      transition: background 0.2s;
    }

    .notification-item:hover {
      background-color: var(--bfp-light);
    }

    .notification-item.unread {
      background-color: #fff3cd;
    }

    .dashboard-header {
      background: linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%);
      color: white;
      padding: 30px 0;
      margin-bottom: 30px;
    }

    .stat-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s, box-shadow 0.3s;
      overflow: hidden;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stat-card .card-body {
      padding: 25px;
    }

    .stat-icon {
      font-size: 2.5rem;
      opacity: 0.8;
    }

    .compliance-good {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
    }

    .compliance-warning {
      background: linear-gradient(135deg, var(--bfp-gold) 0%, #fd7e14 100%);
      color: var(--bfp-dark);
    }

    .compliance-danger {
      background: linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%);
      color: white;
    }

    .info-card {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
      color: white;
    }

    .section-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .section-header {
      background-color: var(--bfp-red);
      color: white;
      padding: 15px 20px;
      border-radius: 15px 15px 0 0;
      font-weight: bold;
    }

    .table-hover tbody tr:hover {
      background-color: rgba(220, 53, 69, 0.05);
    }

    .badge-status {
      padding: 8px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
    }

    .btn-bfp {
      background-color: var(--bfp-red);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 8px 20px;
      transition: background 0.3s;
    }

    .btn-bfp:hover {
      background-color: var(--bfp-dark-red);
      color: white;
    }

    .certificate-card {
      border-left: 4px solid var(--bfp-gold);
      transition: all 0.3s;
    }

    .certificate-card:hover {
      border-left-color: var(--bfp-red);
    }

    @media (max-width: 768px) {
      .notification-dropdown {
        width: 90vw;
        right: 5vw;
      }
    }
  </style>
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
        <a href="./dashboard.php" class="nav-link active">
          <i class="fas fa-tachometer-alt"></i>
          Dashboard
        </a>
      </div>
      <div class="nav-item">
        <a href="./my-establishments.php" class="nav-link ">
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

  <div class="main-content">
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">
          <i class="fas fa-fire"></i>
          BFP Site Profiler
        </a>
        <div class="d-flex align-items-center">
          <div class="notification-icon" id="notificationBell">
            <i class="fas fa-bell"></i>
            <span class="notification-badge" id="notificationCount">3</span>
          </div>
          
        </div>
      </div>
    </nav>
  
    <!-- Notification Dropdown -->
    <div class="notification-dropdown" id="notificationDropdown">
      <div class="notification-header">
        <i class="fas fa-bell"></i> Payment Notifications
      </div>
      <div id="notificationList">
        <div class="notification-item unread" data-id="1">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1"><i class="fas fa-money-bill text-success"></i> Payment Approved</h6>
              <p class="mb-1 small">Your FSIC application payment has been approved by the assessor.</p>
              <small class="text-muted">2 hours ago</small>
            </div>
          </div>
        </div>
        <div class="notification-item unread" data-id="2">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1"><i class="fas fa-exclamation-circle text-warning"></i> Payment Pending</h6>
              <p class="mb-1 small">Assessment fee payment is under review by the assessor.</p>
              <small class="text-muted">1 day ago</small>
            </div>
          </div>
        </div>
        <div class="notification-item unread" data-id="3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1"><i class="fas fa-check-circle text-success"></i> Payment Confirmed</h6>
              <p class="mb-1 small">Renewal fee payment has been confirmed. Certificate processing initiated.</p>
              <small class="text-muted">3 days ago</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="container">
        <h2>Dashboard</h2>
        <p class="mb-0">Welcome back! Here's your establishment's fire safety overview.</p>
      </div>
    </div>
  
    <!-- Main Content -->
    <div class="container mb-5">
      <!-- Statistics Cards -->
      <div class="row g-4 mb-4">
        <div class="col-md-3">
          <div class="card stat-card compliance-good">
            <div class="card-body text-center">
              <i class="fas fa-check-circle stat-icon"></i>
              <h3 class="mt-3 mb-1">95%</h3>
              <p class="mb-0">Compliance Status</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stat-card info-card">
            <div class="card-body text-center">
              <i class="fas fa-calendar-check stat-icon"></i>
              <h3 class="mt-3 mb-1">2</h3>
              <p class="mb-0">Upcoming Inspections</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stat-card compliance-warning">
            <div class="card-body text-center">
              <i class="fas fa-certificate stat-icon"></i>
              <h3 class="mt-3 mb-1">3</h3>
              <p class="mb-0">Active Certificates</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stat-card compliance-danger">
            <div class="card-body text-center">
              <i class="fas fa-exclamation-triangle stat-icon"></i>
              <h3 class="mt-3 mb-1">1</h3>
              <p class="mb-0">Action Required</p>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Upcoming Inspections -->
      <div class="section-card">
        <div class="section-header">
          <i class="fas fa-clipboard-list"></i> Upcoming Inspections
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Inspection Type</th>
                  <th>Scheduled Date</th>
                  <th>Inspector</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Fire Safety Inspection</td>
                  <td>October 28, 2025</td>
                  <td>Insp. Rodriguez</td>
                  <td><span class="badge bg-warning badge-status">Scheduled</span></td>
                </tr>
                <tr>
                  <td>Annual Compliance Check</td>
                  <td>November 5, 2025</td>
                  <td>Insp. Santos</td>
                  <td><span class="badge bg-info badge-status">Pending</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
  
      
  
      
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script>
    // Notification System
    const notificationBell = document.getElementById('notificationBell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationCount = document.getElementById('notificationCount');
    let unreadCount = 3;

    notificationBell.addEventListener('click', function(e) {
      e.stopPropagation();
      notificationDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!notificationDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
        notificationDropdown.classList.remove('show');
      }
    });

    // Mark notification as read when clicked
    document.querySelectorAll('.notification-item').forEach(item => {
      item.addEventListener('click', function() {
        if (this.classList.contains('unread')) {
          this.classList.remove('unread');
          unreadCount--;
          updateNotificationCount();
        }
      });
    });

    function updateNotificationCount() {
      if (unreadCount > 0) {
        notificationCount.textContent = unreadCount;
        notificationCount.style.display = 'flex';
      } else {
        notificationCount.style.display = 'none';
      }
    }

    // Simulate new notification
    function addNotification(title, message, icon, iconClass) {
      const notificationList = document.getElementById('notificationList');
      const newNotification = document.createElement('div');
      newNotification.className = 'notification-item unread';
      newNotification.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1"><i class="${icon} ${iconClass}"></i> ${title}</h6>
                        <p class="mb-1 small">${message}</p>
                        <small class="text-muted">Just now</small>
                    </div>
                </div>
            `;
      notificationList.insertBefore(newNotification, notificationList.firstChild);

      unreadCount++;
      updateNotificationCount();

      newNotification.addEventListener('click', function() {
        if (this.classList.contains('unread')) {
          this.classList.remove('unread');
          unreadCount--;
          updateNotificationCount();
        }
      });
    }

    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth'
          });
        }
      });
    });
  </script>
</body>

</html>