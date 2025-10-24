<?php
/**
 * Activity Logger Helper Class
 * Use this to log user activities throughout the application
 */

class ActivityLogger {
    private $conn;
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    /**
     * Log user activity
     * 
     * @param int $userId - User performing the action
     * @param string $action - Specific action name (e.g., 'user_login', 'create_inspection')
     * @param string $actionType - Type: create, update, delete, view, login, logout
     * @param string $module - Module name: inspection, report, defect, establishment, user, authentication
     * @param string $description - Detailed description of what happened
     * @param array|null $oldValues - Previous values (for updates)
     * @param array|null $newValues - New/updated values
     * @param string $status - success, failed, error (default: success)
     * @param string|null $errorMessage - Error details if status is failed
     * @return bool
     */
    public function log($userId, $action, $actionType, $module, $description, 
                       $oldValues = null, $newValues = null, $status = 'success', $errorMessage = null) {
        try {
            // Get client information
            $ipAddress = $this->getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Convert arrays to JSON
            $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
            $newValuesJson = $newValues ? json_encode($newValues) : null;
            
            // Insert activity log
            $query = "INSERT INTO activity_log 
                      (user_id, action, action_type, module, description, 
                       ip_address, user_agent, old_values, new_values, status, error_message) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("issssssssss", 
                $userId, $action, $actionType, $module, $description,
                $ipAddress, $userAgent, $oldValuesJson, $newValuesJson, $status, $errorMessage
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            // Don't throw errors from logging - just fail silently
            error_log("Activity log error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Quick log methods for common actions
     */
    
    public function logLogin($userId, $success = true) {
        $status = $success ? 'success' : 'failed';
        $description = $success ? 'User logged in successfully' : 'Failed login attempt';
        return $this->log($userId, 'user_login', 'login', 'authentication', $description, null, null, $status);
    }
    
    public function logLogout($userId) {
        return $this->log($userId, 'user_logout', 'logout', 'authentication', 'User logged out');
    }
    
    public function logCreate($userId, $module, $description, $newValues = null) {
        return $this->log($userId, "create_{$module}", 'create', $module, $description, null, $newValues);
    }
    
    public function logUpdate($userId, $module, $description, $oldValues = null, $newValues = null) {
        return $this->log($userId, "update_{$module}", 'update', $module, $description, $oldValues, $newValues);
    }
    
    public function logDelete($userId, $module, $description, $oldValues = null) {
        return $this->log($userId, "delete_{$module}", 'delete', $module, $description, $oldValues);
    }
    
    public function logView($userId, $module, $description) {
        return $this->log($userId, "view_{$module}", 'view', $module, $description);
    }
    
    /**
     * Get client IP address (handles proxies)
     */
    private function getClientIP() {
        $ipAddress = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        return $ipAddress;
    }
    
    /**
     * Get recent activities for a user
     */
    public function getUserActivities($userId, $limit = 50) {
        $query = "SELECT * FROM activity_log 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get all activities with optional filters (for admin)
     */
    public function getAllActivities($filters = [], $limit = 100, $offset = 0) {
        $where = [];
        $params = [];
        $types = '';
        
        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['action_type'])) {
            $where[] = 'action_type = ?';
            $params[] = $filters['action_type'];
            $types .= 's';
        }
        
        if (!empty($filters['module'])) {
            $where[] = 'module = ?';
            $params[] = $filters['module'];
            $types .= 's';
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['date_from'];
            $types .= 's';
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['date_to'];
            $types .= 's';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $query = "SELECT al.*, u.fullname, u.email, u.role 
                  FROM activity_log al
                  LEFT JOIN user u ON al.user_id = u.id
                  {$whereClause}
                  ORDER BY al.created_at DESC
                  LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get activity statistics (for admin dashboard)
     */
    public function getStatistics($dateFrom = null, $dateTo = null) {
        $whereClause = '';
        $params = [];
        $types = '';
        
        if ($dateFrom && $dateTo) {
            $whereClause = 'WHERE created_at BETWEEN ? AND ?';
            $params = [$dateFrom, $dateTo];
            $types = 'ss';
        }
        
        $query = "SELECT 
                    COUNT(*) as total_activities,
                    COUNT(DISTINCT user_id) as active_users,
                    SUM(CASE WHEN action_type = 'create' THEN 1 ELSE 0 END) as creates,
                    SUM(CASE WHEN action_type = 'update' THEN 1 ELSE 0 END) as updates,
                    SUM(CASE WHEN action_type = 'delete' THEN 1 ELSE 0 END) as deletes,
                    SUM(CASE WHEN action_type = 'login' THEN 1 ELSE 0 END) as logins,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_actions
                  FROM activity_log
                  {$whereClause}";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }
}

/**
 * USAGE EXAMPLES:
 * 
 * // Initialize (in your utility/db.php or wherever you init DB)
 * $activityLogger = new ActivityLogger($conn);
 * 
 * // Example 1: Log login
 * $activityLogger->logLogin($_SESSION['user'], true);
 * 
 * // Example 2: Log creating an inspection
 * $activityLogger->logCreate(
 *     $_SESSION['user'],
 *     'inspection',
 *     'Scheduled inspection for establishment: ' . $establishmentName,
 *     ['inspection_id' => $inspectionId, 'date' => $date]
 * );
 * 
 * // Example 3: Log updating defect status
 * $activityLogger->logUpdate(
 *     $_SESSION['user'],
 *     'defect',
 *     'Updated defect status from pending to solved',
 *     ['status' => 'pending'],
 *     ['status' => 'solved']
 * );
 * 
 * // Example 4: Log deleting user
 * $activityLogger->logDelete(
 *     $_SESSION['user'],
 *     'user',
 *     'Deleted user: ' . $username,
 *     ['user_id' => $deletedUserId, 'username' => $username]
 * );
 * 
 * // Example 5: Custom log with error
 * $activityLogger->log(
 *     $_SESSION['user'],
 *     'finalize_report',
 *     'update',
 *     'report',
 *     'Attempted to finalize report #123',
 *     null,
 *     ['report_id' => 123, 'status' => 'compliant'],
 *     'failed',
 *     'Missing inspector notes'
 * );
 */
?>
