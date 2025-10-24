<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP Site Profiler - Admin Dashboard</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
    <link rel="stylesheet" href="../../assets/styles/components/header.css">
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
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      }

      .main-content {
        margin-left: 250px;
        padding: 20px;
      }

      
      .stat-card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        overflow: hidden;
      }

      .stat-card:hover {
        transform: translateY(-5px);
      }

      .stat-card .card-body {
        padding: 1.5rem;
      }

      .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
      }

      .bg-bfp-red {
        background-color: var(--bfp-red);
      }

      .bg-bfp-gold {
        background-color: var(--bfp-gold);
      }

      .bg-bfp-dark {
        background-color: var(--bfp-dark);
      }

      .btn-bfp-red {
        background-color: var(--bfp-red);
        border-color: var(--bfp-red);
        color: white;
      }

      .btn-bfp-red:hover {
        background-color: var(--bfp-dark-red);
        border-color: var(--bfp-dark-red);
        color: white;
      }

      .table-container {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      }

      .activity-log {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        max-height: 50vh;
        overflow-y: auto;
      }

      .activity-item {
        padding: 1rem;
        border-left: 3px solid var(--bfp-red);
        margin-bottom: 1rem;
        background-color: var(--bfp-light);
        border-radius: 5px;
      }

      .activity-item:hover {
        background-color: #e9ecef;
      }

      .badge-role {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
      }

      .modal-header {
        background: linear-gradient(
          135deg,
          var(--bfp-red) 0%,
          var(--bfp-dark-red) 100%
        );
        color: white;
      }

      .search-filter-bar {
        background: white;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
      }

      .table thead {
        background-color: var(--bfp-red);
        color: white;
      }

      .action-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
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
          <a href="./user-management.php" class="nav-link">
            <i class="fas fa-user"></i>
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

    <div class="main-content mt-2">
      <!-- Top Header -->
      <div class="top-header mb-4">
        <div class="d-flex align-items-center">
          <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
          </button>
          <h4 class="mb-0">Admin Dashboard</h4>
        </div>
      </div>

      <div class="row">
        <!-- Statistics Cards -->
        <div class="col-md-12">
          <div class="row mb-4">
            <div class="col-md-4 mb-3">
              <div class="card stat-card">
                <div
                  class="card-body d-flex justify-content-between align-items-center"
                >
                  <div>
                    <h6 class="text-muted mb-1">Inspectors</h6>
                    <h3 class="mb-0" id="inspectorCount">0</h3>
                  </div>
                  <div class="stat-icon bg-bfp-red">
                    <i class="fas fa-search"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <div class="card stat-card">
                <div
                  class="card-body d-flex justify-content-between align-items-center"
                >
                  <div>
                    <h6 class="text-muted mb-1">Fire Chiefs</h6>
                    <h3 class="mb-0" id="chiefCount">0</h3>
                  </div>
                  <div class="stat-icon bg-bfp-gold">
                    <i class="fas fa-user-tie"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <div class="card stat-card">
                <div
                  class="card-body d-flex justify-content-between align-items-center"
                >
                  <div>
                    <h6 class="text-muted mb-1">Fire Marshals</h6>
                    <h3 class="mb-0" id="marshalCount">0</h3>
                  </div>
                  <div class="stat-icon bg-bfp-dark">
                    <i class="fas fa-shield-alt"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="card stat-card">
                <div
                  class="card-body d-flex justify-content-between align-items-center"
                >
                  <div>
                    <h6 class="text-muted mb-1">CRO</h6>
                    <h3 class="mb-0" id="croCount">0</h3>
                  </div>
                  <div class="stat-icon" style="background-color: #17a2b8">
                    <i class="fas fa-headset"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="card stat-card">
                <div
                  class="card-body d-flex justify-content-between align-items-center"
                >
                  <div>
                    <h6 class="text-muted mb-1">Accessors</h6>
                    <h3 class="mb-0" id="accessorCount">0</h3>
                  </div>
                  <div class="stat-icon" style="background-color: #28a745">
                    <i class="fas fa-clipboard-check"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity Logs -->
        <div class="col-md-12">
          <div class="activity-log">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">
                <i class="fas fa-history"></i> Recent Activity Logs
              </h5>
              <div>
                <button class="btn btn-sm btn-outline-secondary" id="refreshLogs">
                  <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button class="btn btn-sm btn-bfp-red" id="viewAllLogs">
                  <i class="fas fa-eye"></i> View All
                </button>
              </div>
            </div>
            
            <!-- Filters -->
            <div class="row mb-3">
              <div class="col-md-3">
                <select class="form-select form-select-sm" id="filterActionType">
                  <option value="">All Action Types</option>
                  <option value="login">Login</option>
                  <option value="logout">Logout</option>
                  <option value="create">Create</option>
                  <option value="update">Update</option>
                  <option value="delete">Delete</option>
                </select>
              </div>
              <div class="col-md-3">
                <select class="form-select form-select-sm" id="filterModule">
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
                <select class="form-select form-select-sm" id="filterStatus">
                  <option value="">All Status</option>
                  <option value="success">Success</option>
                  <option value="failed">Failed</option>
                </select>
              </div>
              <div class="col-md-3">
                <button class="btn btn-sm btn-primary w-100" id="applyFilters">
                  <i class="fas fa-filter"></i> Apply Filters
                </button>
              </div>
            </div>
            
            <div id="activityLogContainer">
              <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading activity logs...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-user-plus"></i> Add New User
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="addUserForm">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input
                  type="text"
                  class="form-control"
                  id="addFullName"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Address</label>
                <input
                  type="text"
                  class="form-control"
                  id="addAddress"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Contact No.</label>
                <input
                  type="tel"
                  class="form-control"
                  id="addContact"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="addEmail"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select class="form-select" id="addRole" required>
                  <option value="">Select Role</option>
                  <option value="Inspector">Inspector</option>
                  <option value="Chief">Chief</option>
                  <option value="Fire Marshal">Fire Marshal</option>
                  <option value="CRO">Customer Relationship Officer</option>
                  <option value="Accessor">Accessor</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="addStatus" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancel
            </button>
            <button type="button" class="btn btn-bfp-red" onclick="addUser()">
              Add User
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-edit"></i> Edit User</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="editUserForm">
              <input type="hidden" id="editUserId" />
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input
                  type="text"
                  class="form-control"
                  id="editFullName"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Address</label>
                <input
                  type="text"
                  class="form-control"
                  id="editAddress"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Contact No.</label>
                <input
                  type="tel"
                  class="form-control"
                  id="editContact"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="editEmail"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select class="form-select" id="editRole" required>
                  <option value="Inspector">Inspector</option>
                  <option value="Chief">Chief</option>
                  <option value="Fire Marshal">Fire Marshal</option>
                  <option value="CRO">Customer Relationship Officer</option>
                  <option value="Accessor">Accessor</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="editStatus" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancel
            </button>
            <button
              type="button"
              class="btn btn-bfp-red"
              onclick="saveEditUser()"
            >
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-eye"></i> User Details</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <table class="table">
              <tr>
                <th>Full Name:</th>
                <td id="viewFullName"></td>
              </tr>
              <tr>
                <th>Address:</th>
                <td id="viewAddress"></td>
              </tr>
              <tr>
                <th>Contact No.:</th>
                <td id="viewContact"></td>
              </tr>
              <tr>
                <th>Email:</th>
                <td id="viewEmail"></td>
              </tr>
              <tr>
                <th>Role:</th>
                <td id="viewRole"></td>
              </tr>
              <tr>
                <th>Status:</th>
                <td id="viewStatus"></td>
              </tr>
            </table>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
          </div>
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
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body" id="activityModalBody">
            <!-- Content will be populated by JavaScript -->
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- View All Logs Modal -->
    <div class="modal fade" id="viewAllLogsModal" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-history"></i> All Activity Logs
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <div id="allLogsContainer" style="max-height: 500px; overflow-y: auto;">
              <!-- All logs will be populated here -->
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success" id="exportLogsBtn">
              <i class="fas fa-download"></i> Export CSV
            </button>
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
      let allActivities = [];
      let filteredActivities = [];

      // Initialize
      document.addEventListener("DOMContentLoaded", function () {
        loadUserStatistics();
        loadActivityLogs();
        
        // Event listeners
        document.getElementById('refreshLogs').addEventListener('click', loadActivityLogs);
        document.getElementById('viewAllLogs').addEventListener('click', showAllLogs);
        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('exportLogsBtn').addEventListener('click', exportToCSV);
      });

      // Load user statistics from database
      async function loadUserStatistics() {
        try {
          const response = await fetch('../../utility/getUserList.php');
          const users = await response.json();
          
          if (users && Array.isArray(users)) {
            const inspectors = users.filter(u => u.role === 'inspector').length;
            const chiefs = users.filter(u => u.role === 'chief').length;
            const marshals = users.filter(u => u.role === 'Fire Marshal').length;
            const cro = users.filter(u => u.role === 'CRO').length;
            const accessors = users.filter(u => u.role === 'Accessor').length;

            document.getElementById('inspectorCount').textContent = inspectors;
            document.getElementById('chiefCount').textContent = chiefs;
            document.getElementById('marshalCount').textContent = marshals;
            document.getElementById('croCount').textContent = cro;
            document.getElementById('accessorCount').textContent = accessors;
          }
        } catch (error) {
          console.error('Error loading user statistics:', error);
          // Set to 0 on error
          document.getElementById('inspectorCount').textContent = '0';
          document.getElementById('chiefCount').textContent = '0';
          document.getElementById('marshalCount').textContent = '0';
          document.getElementById('croCount').textContent = '0';
          document.getElementById('accessorCount').textContent = '0';
        }
      }

      // Load activity logs from database
      async function loadActivityLogs() {
        try {
          const response = await fetch('../../utility/getAllActivityLogs.php?limit=20');
          const data = await response.json();
          
          if (data.success) {
            allActivities = data.activities;
            filteredActivities = allActivities;
            renderActivityLogs(allActivities.slice(0, 10)); // Show only 10 in dashboard
          } else {
            showError();
          }
        } catch (error) {
          console.error('Error loading activity logs:', error);
          showError();
        }
      }

      // Render activity logs
      function renderActivityLogs(logs) {
        const container = document.getElementById("activityLogContainer");
        container.innerHTML = "";

        if (logs.length === 0) {
          container.innerHTML = `
            <div class="text-center py-4">
              <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
              <p class="text-muted">No activity logs found</p>
            </div>
          `;
          return;
        }

        logs.forEach((log) => {
          const div = document.createElement("div");
          div.className = "activity-item";
          div.style.cursor = "pointer";
          div.onclick = () => showActivityDetail(log.id);
          
          const timeAgo = getTimeAgo(log.created_at);
          const statusBadge = log.status === 'success' 
            ? '<span class="badge bg-success">Success</span>'
            : '<span class="badge bg-danger">Failed</span>';
          
          const actionIcon = getActionIcon(log.action_type);
          
          div.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-1">
                  <i class="${actionIcon} me-2"></i>
                  <strong>${log.user_fullname || 'Unknown User'}</strong>
                  <span class="badge ${getRoleBadgeClass(log.user_role)} badge-role ms-2">${log.user_role || 'N/A'}</span>
                  <span class="badge bg-secondary ms-2">${log.module}</span>
                  ${statusBadge}
                </div>
                <p class="mb-0 text-muted small">${log.description}</p>
              </div>
            </div>
            <small class="text-muted">
              <i class="fas fa-clock"></i> ${timeAgo}
              ${log.ip_address ? `<i class="fas fa-map-marker-alt ms-3"></i> ${log.ip_address}` : ''}
            </small>
          `;
          container.appendChild(div);
        });
      }

      // Get action icon
      function getActionIcon(actionType) {
        const icons = {
          'login': 'fas fa-sign-in-alt text-success',
          'logout': 'fas fa-sign-out-alt text-secondary',
          'create': 'fas fa-plus-circle text-primary',
          'update': 'fas fa-edit text-warning',
          'delete': 'fas fa-trash-alt text-danger'
        };
        return icons[actionType] || 'fas fa-info-circle';
      }

      // Get time ago
      function getTimeAgo(timestamp) {
        const now = new Date();
        const activityTime = new Date(timestamp);
        const diffMs = now - activityTime;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
        if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
        
        return activityTime.toLocaleString();
      }

      // Show activity detail modal
      function showActivityDetail(activityId) {
        const activity = allActivities.find(a => a.id == activityId);
        if (!activity) return;
        
        let html = `
          <div class="row mb-3">
            <div class="col-md-6">
              <strong>User:</strong><br>
              ${activity.user_fullname || 'Unknown'} (${activity.user_email || 'N/A'})<br>
              <small class="text-muted">Role: ${activity.user_role || 'N/A'}</small>
            </div>
            <div class="col-md-6">
              <strong>Action:</strong><br>
              <span class="badge bg-primary">${activity.action}</span>
              <span class="badge bg-secondary">${activity.action_type}</span>
              <span class="badge bg-info">${activity.module}</span>
            </div>
          </div>
          
          <div class="mb-3">
            <strong>Description:</strong><br>
            ${activity.description || 'N/A'}
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <strong>Status:</strong><br>
              ${activity.status === 'success' 
                ? '<span class="badge bg-success">Success</span>' 
                : '<span class="badge bg-danger">Failed</span>'}
              ${activity.error_message ? `<br><small class="text-danger">${activity.error_message}</small>` : ''}
            </div>
            <div class="col-md-6">
              <strong>Timestamp:</strong><br>
              ${new Date(activity.created_at).toLocaleString()}
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <strong>IP Address:</strong><br>
              ${activity.ip_address || 'N/A'}
            </div>
            <div class="col-md-6">
              <strong>User Agent:</strong><br>
              <small>${activity.user_agent || 'N/A'}</small>
            </div>
          </div>
        `;
        
        // Add old values if present
        if (activity.old_values) {
          try {
            const oldValues = typeof activity.old_values === 'string' 
              ? JSON.parse(activity.old_values) 
              : activity.old_values;
            html += `
              <div class="mb-3">
                <strong>Old Values (Before Change):</strong>
                <pre class="bg-light p-3 rounded mt-2" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(oldValues, null, 2)}</pre>
              </div>
            `;
          } catch (e) {
            console.error('Error parsing old_values:', e);
          }
        }
        
        // Add new values if present
        if (activity.new_values) {
          try {
            const newValues = typeof activity.new_values === 'string' 
              ? JSON.parse(activity.new_values) 
              : activity.new_values;
            html += `
              <div class="mb-3">
                <strong>New Values (After Change):</strong>
                <pre class="bg-light p-3 rounded mt-2" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(newValues, null, 2)}</pre>
              </div>
            `;
          } catch (e) {
            console.error('Error parsing new_values:', e);
          }
        }
        
        document.getElementById('activityModalBody').innerHTML = html;
        
        const modal = new bootstrap.Modal(document.getElementById('activityDetailModal'));
        modal.show();
      }

      // Show all logs modal
      function showAllLogs() {
        const container = document.getElementById('allLogsContainer');
        container.innerHTML = '';
        
        renderActivityLogsInModal(filteredActivities);
        
        const modal = new bootstrap.Modal(document.getElementById('viewAllLogsModal'));
        modal.show();
      }

      // Render activity logs in modal
      function renderActivityLogsInModal(logs) {
        const container = document.getElementById('allLogsContainer');
        container.innerHTML = '';
        
        if (logs.length === 0) {
          container.innerHTML = '<div class="text-center py-4"><p class="text-muted">No logs found</p></div>';
          return;
        }
        
        logs.forEach(log => {
          const div = document.createElement('div');
          div.className = 'activity-item';
          div.style.cursor = 'pointer';
          div.onclick = () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('viewAllLogsModal'));
            modal.hide();
            showActivityDetail(log.id);
          };
          
          const timeAgo = getTimeAgo(log.created_at);
          const statusBadge = log.status === 'success' 
            ? '<span class="badge bg-success">Success</span>'
            : '<span class="badge bg-danger">Failed</span>';
          
          const actionIcon = getActionIcon(log.action_type);
          
          div.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-1">
                  <i class="${actionIcon} me-2"></i>
                  <strong>${log.user_fullname || 'Unknown User'}</strong>
                  <span class="badge ${getRoleBadgeClass(log.user_role)} badge-role ms-2">${log.user_role || 'N/A'}</span>
                  <span class="badge bg-secondary ms-2">${log.module}</span>
                  ${statusBadge}
                </div>
                <p class="mb-0 text-muted small">${log.description}</p>
              </div>
            </div>
            <small class="text-muted">
              <i class="fas fa-clock"></i> ${timeAgo}
              ${log.ip_address ? `<i class="fas fa-map-marker-alt ms-3"></i> ${log.ip_address}` : ''}
            </small>
          `;
          container.appendChild(div);
        });
      }

      // Apply filters
      function applyFilters() {
        const actionType = document.getElementById('filterActionType').value;
        const module = document.getElementById('filterModule').value;
        const status = document.getElementById('filterStatus').value;
        
        filteredActivities = allActivities.filter(activity => {
          if (actionType && activity.action_type !== actionType) return false;
          if (module && activity.module !== module) return false;
          if (status && activity.status !== status) return false;
          return true;
        });
        
        renderActivityLogs(filteredActivities.slice(0, 10));
      }

      // Export to CSV
      function exportToCSV() {
        let csv = 'Timestamp,User,Email,Role,Action,Action Type,Module,Description,Status,IP Address\n';
        
        filteredActivities.forEach(activity => {
          const row = [
            new Date(activity.created_at).toLocaleString(),
            activity.user_fullname || 'Unknown',
            activity.user_email || 'N/A',
            activity.user_role || 'N/A',
            activity.action,
            activity.action_type,
            activity.module,
            `"${(activity.description || '').replace(/"/g, '""')}"`,
            activity.status,
            activity.ip_address || 'N/A'
          ];
          csv += row.join(',') + '\n';
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `activity_logs_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
      }

      // Show error
      function showError() {
        const container = document.getElementById("activityLogContainer");
        container.innerHTML = `
          <div class="text-center py-4">
            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
            <p class="text-muted">Error loading activity logs</p>
          </div>
        `;
      }

      function getRoleBadgeClass(role) {
        const badges = {
          Inspector: "bg-danger",
          inspector: "bg-danger",
          Chief: "bg-warning text-dark",
          chief: "bg-warning text-dark",
          "Fire Marshal": "bg-dark",
          CRO: "bg-info",
          Accessor: "bg-success",
          admin: "bg-primary"
        };
        return badges[role] || "bg-secondary";
      }
    </script>
    <script src="../../assets/scripts/components/sidebar.js"></script>
  </body>
</html>
