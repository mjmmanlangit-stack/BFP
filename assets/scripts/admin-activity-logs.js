// Admin Activity Logs JavaScript

let currentPage = 1;
let totalPages = 1;
const logsPerPage = 20;
let allActivities = [];
let filteredActivities = [];

// Load activities on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    loadUsersList();
    loadActivities();
    
    // Event listeners
    document.getElementById('applyFilters').addEventListener('click', applyFilters);
    document.getElementById('resetFilters').addEventListener('click', resetFilters);
    document.getElementById('exportLogs').addEventListener('click', exportToCSV);
});

// Load statistics
async function loadStatistics() {
    try {
        const response = await fetch('../../utility/getActivityStatistics.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalActivities').textContent = data.stats.total_today || 0;
            document.getElementById('totalLogins').textContent = data.stats.logins_today || 0;
            document.getElementById('failedLogins').textContent = data.stats.failed_logins_today || 0;
            document.getElementById('activeUsers').textContent = data.stats.active_users_today || 0;
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

// Load users list for filter
async function loadUsersList() {
    try {
        const response = await fetch('../../utility/getUserList.php');
        const data = await response.json();
        
        if (data) {
            const userSelect = document.getElementById('filterUser');
            data.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.fullname} (${user.role})`;
                userSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

// Load activities
async function loadActivities() {
    try {
        const response = await fetch('../../utility/getAllActivityLogs.php');
        const data = await response.json();
        
        if (data.success) {
            allActivities = data.activities;
            filteredActivities = allActivities;
            displayActivities();
        } else {
            showNoData();
        }
    } catch (error) {
        console.error('Error loading activities:', error);
        showError();
    }
}

// Display activities
function displayActivities() {
    const container = document.getElementById('activityLogsContainer');
    
    if (filteredActivities.length === 0) {
        showNoData();
        return;
    }
    
    // Calculate pagination
    totalPages = Math.ceil(filteredActivities.length / logsPerPage);
    const startIndex = (currentPage - 1) * logsPerPage;
    const endIndex = startIndex + logsPerPage;
    const pageActivities = filteredActivities.slice(startIndex, endIndex);
    
    // Build HTML
    let html = '';
    pageActivities.forEach(activity => {
        html += createActivityRow(activity);
    });
    
    container.innerHTML = html;
    
    // Add click listeners
    document.querySelectorAll('.activity-row').forEach(row => {
        row.addEventListener('click', function() {
            const activityId = this.dataset.activityId;
            showActivityDetail(activityId);
        });
    });
    
    // Update pagination
    updatePagination();
}

// Create activity row HTML
function createActivityRow(activity) {
    const iconClass = getIconClass(activity.action_type);
    const timeAgo = getTimeAgo(activity.created_at);
    const statusBadge = activity.status === 'success' 
        ? '<span class="badge-status badge-success">Success</span>'
        : '<span class="badge-status badge-failed">Failed</span>';
    
    return `
        <div class="activity-row" data-activity-id="${activity.id}">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="activity-icon ${activity.action_type}">
                        <i class="${iconClass}"></i>
                    </div>
                </div>
                <div class="col">
                    <div class="activity-user">${activity.user_fullname || 'Unknown User'}</div>
                    <div class="activity-action">
                        ${activity.description}
                        <span class="badge-module">${activity.module}</span>
                    </div>
                    <div class="activity-time">
                        <i class="fas fa-clock"></i> ${timeAgo}
                        ${activity.ip_address ? `<i class="fas fa-map-marker-alt ms-3"></i> ${activity.ip_address}` : ''}
                    </div>
                </div>
                <div class="col-auto">
                    ${statusBadge}
                </div>
            </div>
        </div>
    `;
}

// Get icon class based on action type
function getIconClass(actionType) {
    const icons = {
        'login': 'fas fa-sign-in-alt',
        'logout': 'fas fa-sign-out-alt',
        'create': 'fas fa-plus-circle',
        'update': 'fas fa-edit',
        'delete': 'fas fa-trash-alt',
        'view': 'fas fa-eye'
    };
    return icons[actionType] || 'fas fa-info-circle';
}

// Get time ago string
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
        <div class="detail-row">
            <div class="detail-label">User</div>
            <div class="detail-value">
                <strong>${activity.user_fullname || 'Unknown'}</strong> (${activity.user_email || 'N/A'})
                <br><small class="text-muted">Role: ${activity.user_role || 'N/A'}</small>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Action</div>
            <div class="detail-value">
                <span class="badge bg-primary">${activity.action}</span>
                <span class="badge bg-secondary">${activity.action_type}</span>
                <span class="badge bg-info">${activity.module}</span>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Description</div>
            <div class="detail-value">${activity.description || 'N/A'}</div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                ${activity.status === 'success' 
                    ? '<span class="badge bg-success">Success</span>' 
                    : '<span class="badge bg-danger">Failed</span>'}
                ${activity.error_message ? `<br><small class="text-danger">${activity.error_message}</small>` : ''}
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Timestamp</div>
            <div class="detail-value">${new Date(activity.created_at).toLocaleString()}</div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">IP Address</div>
            <div class="detail-value">${activity.ip_address || 'N/A'}</div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">User Agent</div>
            <div class="detail-value"><small>${activity.user_agent || 'N/A'}</small></div>
        </div>
    `;
    
    // Add old values if present
    if (activity.old_values) {
        try {
            const oldValues = typeof activity.old_values === 'string' 
                ? JSON.parse(activity.old_values) 
                : activity.old_values;
            html += `
                <div class="detail-row">
                    <div class="detail-label">Old Values (Before Change)</div>
                    <div class="detail-value">
                        <div class="json-viewer">
                            <pre>${JSON.stringify(oldValues, null, 2)}</pre>
                        </div>
                    </div>
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
                <div class="detail-row">
                    <div class="detail-label">New Values (After Change)</div>
                    <div class="detail-value">
                        <div class="json-viewer">
                            <pre>${JSON.stringify(newValues, null, 2)}</pre>
                        </div>
                    </div>
                </div>
            `;
        } catch (e) {
            console.error('Error parsing new_values:', e);
        }
    }
    
    document.getElementById('modalBody').innerHTML = html;
    
    const modal = new bootstrap.Modal(document.getElementById('activityDetailModal'));
    modal.show();
}

// Apply filters
function applyFilters() {
    const actionType = document.getElementById('filterActionType').value;
    const module = document.getElementById('filterModule').value;
    const status = document.getElementById('filterStatus').value;
    const userId = document.getElementById('filterUser').value;
    
    filteredActivities = allActivities.filter(activity => {
        if (actionType && activity.action_type !== actionType) return false;
        if (module && activity.module !== module) return false;
        if (status && activity.status !== status) return false;
        if (userId && activity.user_id != userId) return false;
        return true;
    });
    
    currentPage = 1;
    displayActivities();
}

// Reset filters
function resetFilters() {
    document.getElementById('filterActionType').value = '';
    document.getElementById('filterModule').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterUser').value = '';
    
    filteredActivities = allActivities;
    currentPage = 1;
    displayActivities();
}

// Update pagination
function updatePagination() {
    const container = document.getElementById('paginationContainer');
    
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<nav><ul class="pagination">';
    
    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>
    `;
    
    html += '</ul></nav>';
    container.innerHTML = html;
    
    // Add click listeners
    container.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.dataset.page);
            if (page && page !== currentPage && page >= 1 && page <= totalPages) {
                currentPage = page;
                displayActivities();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });
}

// Show no data message
function showNoData() {
    document.getElementById('activityLogsContainer').innerHTML = `
        <div class="no-data">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>No activities found</p>
        </div>
    `;
    document.getElementById('paginationContainer').innerHTML = '';
}

// Show error message
function showError() {
    document.getElementById('activityLogsContainer').innerHTML = `
        <div class="no-data">
            <i class="fas fa-exclamation-triangle fa-3x mb-3 text-danger"></i>
            <p>Error loading activities. Please try again.</p>
        </div>
    `;
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
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `activity_logs_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
}
