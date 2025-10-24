<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - BFP Profiler</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/styles/index.css">
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css">
    <link rel="stylesheet" href="../../assets/styles/components/header.css">
    <link rel="stylesheet" href="../../assets/styles/layout/admin-dashboard.css">
    
    <style>
        .activity-logs-container {
            padding: 2rem;
        }
        
        .filters-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stats-row {
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .stat-card .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .stat-card .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .activity-table-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .activity-row {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .activity-row:hover {
            background-color: #f8f9fa;
        }
        
        .activity-row:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }
        
        .activity-icon.login { background: #28a745; }
        .activity-icon.logout { background: #6c757d; }
        .activity-icon.create { background: #007bff; }
        .activity-icon.update { background: #ffc107; }
        .activity-icon.delete { background: #dc3545; }
        
        .activity-user {
            font-weight: 600;
            color: #333;
        }
        
        .activity-action {
            color: #666;
            font-size: 0.9rem;
        }
        
        .activity-time {
            color: #999;
            font-size: 0.85rem;
        }
        
        .badge-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .badge-success { background: #28a745; color: white; }
        .badge-failed { background: #dc3545; color: white; }
        
        .badge-module {
            background: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }
        
        /* Modal Styles */
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .detail-row {
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 0.25rem;
        }
        
        .detail-value {
            color: #333;
        }
        
        .json-viewer {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .json-viewer pre {
            margin: 0;
            font-size: 0.85rem;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        
        .loading {
            text-align: center;
            padding: 3rem;
        }
        
        .pagination-container {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
      <div class="logo-section">
        <div class="logo">
          <i class="fas fa-shield-alt" style="color: var(--bfp-red); font-size: 24px"></i>
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
          <a href="./user-management.php" class="nav-link">
            <i class="fas fa-user"></i>
            User Management
          </a>
        </div>
        <div class="nav-item">
          <a href="./activity-logs.php" class="nav-link active">
            <i class="fas fa-history"></i>
            Activity Logs
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
        <header class="header">
          <div class="container-fluid">
            <div class="row align-items-center">
              <div class="col">
                <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
                  <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0 d-inline-block">Activity Logs</h4>
              </div>
              <div class="col-auto">
                <div class="user-info">
                  <i class="fas fa-user-circle"></i>
                  <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                </div>
              </div>
            </div>
          </div>
        </header>

        <!-- Activity Logs Container -->
        <div class="activity-logs-container">
            <h2 class="mb-4">
                <i class="fas fa-history"></i> Activity Logs
            </h2>

            <!-- Statistics Cards -->
            <div class="row stats-row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #667eea;">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="stat-value" id="totalActivities">0</div>
                        <div class="stat-label">Total Activities</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #28a745;">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <div class="stat-value" id="totalLogins">0</div>
                        <div class="stat-label">Logins Today</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #dc3545;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-value" id="failedLogins">0</div>
                        <div class="stat-label">Failed Logins</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #007bff;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value" id="activeUsers">0</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-card">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Action Type</label>
                        <select class="form-select" id="filterActionType">
                            <option value="">All Types</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="create">Create</option>
                            <option value="update">Update</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Module</label>
                        <select class="form-select" id="filterModule">
                            <option value="">All Modules</option>
                            <option value="authentication">Authentication</option>
                            <option value="inspection">Inspection</option>
                            <option value="report">Report</option>
                            <option value="defect">Defect</option>
                            <option value="user">User</option>
                            <option value="establishment">Establishment</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">User</label>
                        <select class="form-select" id="filterUser">
                            <option value="">All Users</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button class="btn btn-primary" id="applyFilters">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <button class="btn btn-secondary" id="resetFilters">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                        <button class="btn btn-success float-end" id="exportLogs">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity Logs Table -->
            <div class="activity-table-card">
                <h5 class="mb-3">Recent Activities</h5>
                <div id="activityLogsContainer">
                    <div class="loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading activities...</p>
                    </div>
                </div>
                <div class="pagination-container" id="paginationContainer"></div>
            </div>
        </div>
    </div>

    <!-- Activity Detail Modal -->
    <div class="modal fade" id="activityDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i> Activity Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Sidebar Toggle Script -->
    <script src="../../assets/scripts/components/sidebar.js"></script>

    <!-- Activity Logs Script -->
    <script src="../../assets/scripts/admin-activity-logs.js"></script>
</body>
</html>
