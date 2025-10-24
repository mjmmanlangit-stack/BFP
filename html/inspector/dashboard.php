<?php
session_start();

// Check if user is logged in and is an inspector
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'inspector') {
    header('Location: ../index.php');
    exit;
}

$inspectorName = $_SESSION['fullname'] ?? 'Inspector';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BFP Inspector Dashboard</title>
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
    rel="stylesheet" />
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <link
    rel="stylesheet"
    href="../../assets/styles/layout/inspector-dashboard.css" />
  <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
  <style>
    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 24px;
    }

    .stats-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      height: 100%;
    }

    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .stats-number {
      font-size: 2rem;
      font-weight: bold;
      margin: 0;
    }

    .stats-label {
      color: #6c757d;
      font-size: 0.9rem;
      margin: 0;
    }

    .card {
      border: none;
      border-radius: 10px;
    }

    .card-header {
      border-radius: 10px 10px 0 0 !important;
      padding: 15px 20px;
    }

    .table {
      margin-bottom: 0;
    }

    .header {
      margin-bottom: 2rem;
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

    </nav>

    <div class="nav-item">
      <a href="../../utility/logout.php" class="nav-link">
        <i class="fas fa-sign-out-alt"></i>
        Logout
      </a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <h2 class="text-dark mb-1">Inspector Dashboard</h2>
      <p class="text-muted mb-0">
        Welcome back, <?php echo htmlspecialchars($inspectorName); ?>! Here's your inspection activity overview.
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
        <div class="stats-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon bg-primary">
              <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="ms-3">
              <div class="stats-number text-primary" id="totalInspections">0</div>
              <div class="stats-label">Total Inspections</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
        <div class="stats-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon bg-warning">
              <i class="fas fa-clock"></i>
            </div>
            <div class="ms-3">
              <div class="stats-number text-warning" id="scheduledInspections">0</div>
              <div class="stats-label">Scheduled</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
        <div class="stats-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon bg-success">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="ms-3">
              <div class="stats-number text-success" id="completedInspections">0</div>
              <div class="stats-label">Completed</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
        <div class="stats-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon bg-danger">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="ms-3">
              <div class="stats-number text-danger" id="overdueInspections">0</div>
              <div class="stats-label">Overdue</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Secondary Stats Row -->
    <div class="row mb-4">
      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
        <div class="stats-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon bg-info">
              <i class="fas fa-file-alt"></i>
            </div>
            <div class="ms-3">
              <div class="stats-number text-info" id="totalReports">0</div>
              <div class="stats-label">Total Reports</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
        <div class="stats-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon" style="background-color: #ffc107;">
              <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="ms-3">
              <div class="stats-number" style="color: #ffc107;" id="pendingFinalization">0</div>
              <div class="stats-label">Pending Finalization</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
        <div class="stats-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon bg-secondary">
              <i class="fas fa-tools"></i>
            </div>
            <div class="ms-3">
              <div class="stats-number text-secondary" id="totalDefects">0</div>
              <div class="stats-label">Total Defects</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
        <div class="stats-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon bg-success">
              <i class="fas fa-check-double"></i>
            </div>
            <div class="ms-3">
              <div class="stats-number text-success" id="solvedDefects">0</div>
              <div class="stats-label">Defects Solved</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
      <!-- Monthly Trend Chart -->
      <div class="col-lg-8 mb-4">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Inspection Trend (Last 6 Months)</h5>
          </div>
          <div class="card-body">
            <canvas id="monthlyTrendChart" height="80"></canvas>
          </div>
        </div>
      </div>

      <!-- Compliance Status Pie Chart -->
      <div class="col-lg-4 mb-4">
        <div class="card shadow-sm">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Compliance Status</h5>
          </div>
          <div class="card-body">
            <canvas id="complianceChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Second Charts Row -->
    <div class="row mb-4">
      <!-- Establishment Types Bar Chart -->
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Establishment Types</h5>
          </div>
          <div class="card-body">
            <canvas id="establishmentTypesChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Priority Distribution -->
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
          <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Priority Distribution</h5>
          </div>
          <div class="card-body">
            <canvas id="priorityChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Upcoming Inspections Table -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Upcoming Inspections (Next 7 Days)</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Establishment</th>
                    <th>Type</th>
                    <th>Address</th>
                    <th>Priority</th>
                  </tr>
                </thead>
                <tbody id="upcomingTableBody">
                  <tr>
                    <td colspan="6" class="text-center text-muted">Loading...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Completed Inspections -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Completed Inspections</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Establishment</th>
                    <th>Type</th>
                    <th>Inspection Type</th>
                    <th>Compliance Status</th>
                  </tr>
                </thead>
                <tbody id="recentTableBody">
                  <tr>
                    <td colspan="5" class="text-center text-muted">Loading...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  
  <script>
    let dashboardData = null;
    let charts = {};

    // Load dashboard data
    async function loadDashboardData() {
      try {
        const response = await fetch('../../utility/getInspectorDashboardData.php');
        const data = await response.json();

        if (data.success) {
          dashboardData = data;
          updateStatCards();
          renderCharts();
          renderUpcomingTable();
          renderRecentTable();
        } else {
          console.error('Error loading dashboard data:', data.message);
          showError('Failed to load dashboard data');
        }
      } catch (error) {
        console.error('Error:', error);
        showError('Failed to load dashboard data');
      }
    }

    function showError(message) {
      const alert = document.createElement('div');
      alert.className = 'alert alert-danger alert-dismissible fade show';
      alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      document.querySelector('.main-content').insertBefore(
        alert,
        document.querySelector('.header').nextSibling
      );
    }

    // Update stat cards
    function updateStatCards() {
      document.getElementById('totalInspections').textContent = dashboardData.stats.total_inspections;
      document.getElementById('scheduledInspections').textContent = dashboardData.stats.scheduled;
      document.getElementById('completedInspections').textContent = dashboardData.stats.completed;
      document.getElementById('overdueInspections').textContent = dashboardData.stats.overdue;
      
      document.getElementById('totalReports').textContent = dashboardData.reports.total;
      document.getElementById('pendingFinalization').textContent = dashboardData.reports.pending_finalization;
      document.getElementById('totalDefects').textContent = dashboardData.defects.total;
      document.getElementById('solvedDefects').textContent = dashboardData.defects.solved;
    }

    // Render all charts
    function renderCharts() {
      renderMonthlyTrendChart();
      renderComplianceChart();
      renderEstablishmentTypesChart();
      renderPriorityChart();
    }

    // Monthly Trend Line Chart
    function renderMonthlyTrendChart() {
      const ctx = document.getElementById('monthlyTrendChart');
      
      const labels = dashboardData.monthly_trend.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
      });
      
      const data = dashboardData.monthly_trend.map(item => item.count);

      if (charts.monthlyTrend) {
        charts.monthlyTrend.destroy();
      }

      charts.monthlyTrend = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Inspections',
            data: data,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: true,
              position: 'top'
            },
            tooltip: {
              mode: 'index',
              intersect: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    }

    // Compliance Pie Chart
    function renderComplianceChart() {
      const ctx = document.getElementById('complianceChart');
      
      const data = {
        labels: ['Compliant', 'Partially Compliant', 'Non-Compliant', 'Pending'],
        datasets: [{
          data: [
            dashboardData.reports.compliant,
            dashboardData.reports.partially_compliant,
            dashboardData.reports.non_compliant,
            dashboardData.reports.pending_finalization
          ],
          backgroundColor: [
            '#28a745',
            '#ffc107',
            '#dc3545',
            '#6c757d'
          ],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      };

      if (charts.compliance) {
        charts.compliance.destroy();
      }

      charts.compliance = new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.parsed || 0;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                  return `${label}: ${value} (${percentage}%)`;
                }
              }
            }
          }
        }
      });
    }

    // Establishment Types Bar Chart
    function renderEstablishmentTypesChart() {
      const ctx = document.getElementById('establishmentTypesChart');
      
      const labels = dashboardData.establishment_types.map(item => item.type);
      const data = dashboardData.establishment_types.map(item => item.count);

      if (charts.establishmentTypes) {
        charts.establishmentTypes.destroy();
      }

      charts.establishmentTypes = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Number of Inspections',
            data: data,
            backgroundColor: '#17a2b8',
            borderColor: '#17a2b8',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    }

    // Priority Distribution Chart
    function renderPriorityChart() {
      const ctx = document.getElementById('priorityChart');
      
      const priorityMap = {
        'high': 0,
        'medium': 0,
        'low': 0
      };

      dashboardData.priority_distribution.forEach(item => {
        const priority = (item.priority_level || 'medium').toLowerCase();
        priorityMap[priority] = item.count;
      });

      if (charts.priority) {
        charts.priority.destroy();
      }

      charts.priority = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['High Priority', 'Medium Priority', 'Low Priority'],
          datasets: [{
            label: 'Count',
            data: [priorityMap.high, priorityMap.medium, priorityMap.low],
            backgroundColor: [
              '#dc3545',
              '#ffc107',
              '#28a745'
            ],
            borderWidth: 1
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            x: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    }

    // Render upcoming inspections table
    function renderUpcomingTable() {
      const tbody = document.getElementById('upcomingTableBody');
      tbody.innerHTML = '';

      if (dashboardData.upcoming_inspections.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="6" class="text-center text-muted py-4">
              <i class="fas fa-calendar-check fa-3x mb-3"></i>
              <p>No upcoming inspections in the next 7 days</p>
            </td>
          </tr>
        `;
        return;
      }

      dashboardData.upcoming_inspections.forEach(inspection => {
        const priorityBadge = getPriorityBadge(inspection.priority_level);
        const row = `
          <tr>
            <td>${inspection.inspection_date}</td>
            <td>${inspection.time_slot}</td>
            <td>${inspection.establishment_name}</td>
            <td>${inspection.establishment_type}</td>
            <td>${inspection.address}</td>
            <td>${priorityBadge}</td>
          </tr>
        `;
        tbody.innerHTML += row;
      });
    }

    // Render recent completed table
    function renderRecentTable() {
      const tbody = document.getElementById('recentTableBody');
      tbody.innerHTML = '';

      if (dashboardData.recent_completed.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center text-muted py-4">
              <i class="fas fa-history fa-3x mb-3"></i>
              <p>No completed inspections yet</p>
            </td>
          </tr>
        `;
        return;
      }

      dashboardData.recent_completed.forEach(inspection => {
        const complianceBadge = getComplianceBadge(inspection.compliance_status);
        const row = `
          <tr>
            <td>${inspection.inspection_date}</td>
            <td>${inspection.establishment_name}</td>
            <td>${inspection.establishment_type}</td>
            <td>${inspection.inspection_type}</td>
            <td>${complianceBadge}</td>
          </tr>
        `;
        tbody.innerHTML += row;
      });
    }

    function getPriorityBadge(priority) {
      const p = (priority || 'medium').toLowerCase();
      if (p === 'high') {
        return '<span class="badge bg-danger">High</span>';
      } else if (p === 'low') {
        return '<span class="badge bg-success">Low</span>';
      } else {
        return '<span class="badge bg-warning text-dark">Medium</span>';
      }
    }

    function getComplianceBadge(status) {
      if (!status) {
        return '<span class="badge bg-secondary">Pending Review</span>';
      }
      
      if (status === 'compliant') {
        return '<span class="badge bg-success">Compliant</span>';
      } else if (status === 'partially_compliant') {
        return '<span class="badge bg-warning text-dark">Partially Compliant</span>';
      } else if (status === 'non_compliant') {
        return '<span class="badge bg-danger">Non-Compliant</span>';
      }
      
      return '<span class="badge bg-secondary">Unknown</span>';
    }

    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
      loadDashboardData();

      // Refresh data every 5 minutes
      setInterval(loadDashboardData, 300000);
    });
  </script>
</body>

</html>