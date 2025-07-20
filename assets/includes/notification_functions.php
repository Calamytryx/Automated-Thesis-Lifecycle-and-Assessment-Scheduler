<?php
// Include guard to prevent multiple inclusions
if (!defined('NOTIFICATION_FUNCTIONS_INCLUDED')) {
    define('NOTIFICATION_FUNCTIONS_INCLUDED', true);

/**
 * Notification Management System
 * Handles creation, retrieval, and management of user notifications
 */

require_once __DIR__ . '/../setup/db.inc.php';

/**
 * Create a new notification
 * @param PDO $pdo Database connection
 * @param int $user_id The ID of the user to notify
 * @param string $title Short title of the notification
 * @param string $message Detailed message
 * @param string $type Type of notification (defense_schedule, title_approved, etc.)
 * @param int|null $reference_id ID of related entity (team_id, requirement_id, etc.)
 * @return bool Success status
 */
function createNotification($pdo, $user_id, $title, $message, $type, $reference_id = null) {
    try {
        // Check for duplicate notifications in the last 10 seconds
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_id = ? AND title = ? AND message = ? AND type = ? AND related_id = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)
        ");
        $stmt->execute([$user_id, $title, $message, $type, $reference_id]);
        $duplicateCount = $stmt->fetch()['count'];
        
        if ($duplicateCount > 0) {
            error_log("Duplicate notification prevented for user $user_id, type $type");
            return true; // Consider it successful but don't create duplicate
        }
        
        // For now, always create notifications (preferences checking can be added later)
        $stmt = $pdo->prepare("
            INSERT INTO notifications 
            (user_id, title, message, type, related_id, is_read, created_at) 
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        return $stmt->execute([$user_id, $title, $message, $type, $reference_id]);
        
    } catch (PDOException $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get unread notifications for a user (for header dropdown)
 * @param int $user_id User ID
 * @param int $limit Maximum number of notifications to return
 * @return array Array of notifications
 */
function getUnreadNotifications($user_id, $limit = 5) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, type, title, message, related_id, created_at
            FROM notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error fetching unread notifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all notifications for a user with pagination
 * @param int $user_id User ID
 * @param int $page Page number (1-based)
 * @param int $per_page Number of notifications per page
 * @param string|null $filter Filter by read status ('read', 'unread', or null for all)
 * @return array Array with 'notifications' and 'total_count'
 */
function getAllNotifications($user_id, $page = 1, $per_page = 20, $filter = null) {
    global $pdo;
    
    try {
        $offset = ($page - 1) * $per_page;
        
        // Build WHERE clause based on filter
        $where_clause = "WHERE user_id = ?";
        $params = [$user_id];
        
        if ($filter === 'read') {
            $where_clause .= " AND is_read = 1";
        } elseif ($filter === 'unread') {
            $where_clause .= " AND is_read = 0";
        }
        
        // Get notifications
        $stmt = $pdo->prepare("
            SELECT id, type, title, message, related_id, is_read, created_at
            FROM notifications 
            {$where_clause}
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $params[] = $per_page;
        $params[] = $offset;
        $stmt->execute($params);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $count_params = array_slice($params, 0, -2); // Remove LIMIT and OFFSET
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM notifications 
            {$where_clause}
        ");
        $stmt->execute($count_params);
        $total_count = $stmt->fetch()['total'];
        
        return [
            'notifications' => $notifications,
            'total_count' => $total_count,
            'current_page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total_count / $per_page)
        ];
        
    } catch (PDOException $e) {
        error_log("Error fetching all notifications: " . $e->getMessage());
        return [
            'notifications' => [],
            'total_count' => 0,
            'current_page' => $page,
            'per_page' => $per_page,
            'total_pages' => 0
        ];
    }
}

/**
 * Mark a specific notification as read
 * @param int $notification_id Notification ID
 * @param int $user_id User ID (for security)
 * @return bool Success status
 */
function markAsRead($notification_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$notification_id, $user_id]);
        
    } catch (PDOException $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Mark all notifications as read for a user
 * @param int $user_id User ID
 * @return bool Success status
 */
function markAllAsRead($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = 1, updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ? AND is_read = 0
        ");
        return $stmt->execute([$user_id]);
        
    } catch (PDOException $e) {
        error_log("Error marking all notifications as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Get count of unread notifications for a user
 * @param int $user_id User ID
 * @return int Number of unread notifications
 */
function getUnreadCount($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$user_id]);
        return (int) $stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        error_log("Error getting unread count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get all team members including adviser for a team
 * @param int $team_id Team ID
 * @return array Array of user IDs
 */
function getTeamMembersForNotifications($team_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT user_id 
            FROM team_members 
            WHERE team_id = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
        
    } catch (PDOException $e) {
        error_log("Error getting team members: " . $e->getMessage());
        return [];
    }
}

/**
 * Get team members excluding advisers (for title approval notifications)
 * @param int $team_id Team ID
 * @return array Array of user IDs
 */
function getTeamMembersExcludingAdvisers($team_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT user_id 
            FROM team_members 
            WHERE team_id = ? AND role != 'adviser'
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
        
    } catch (PDOException $e) {
        error_log("Error getting team members excluding advisers: " . $e->getMessage());
        return [];
    }
}

/**
 * Get panelists for a defense schedule
 * @param int $defense_id Defense schedule ID
 * @return array Array of user IDs
 */
function getDefensePanelists($defense_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT panelist_id, panelist_id2, panelist_id3 
            FROM defense_schedules 
            WHERE id = ?
        ");
        $stmt->execute([$defense_id]);
        $row = $stmt->fetch();
        
        if ($row) {
            $panelists = [];
            if ($row['panelist_id']) $panelists[] = $row['panelist_id'];
            if ($row['panelist_id2']) $panelists[] = $row['panelist_id2'];
            if ($row['panelist_id3']) $panelists[] = $row['panelist_id3'];
            return array_filter($panelists); // Remove null values
        }
        
        return [];
        
    } catch (PDOException $e) {
        error_log("Error getting defense panelists: " . $e->getMessage());
        return [];
    }
}

/**
 * Create notifications for defense scheduling
 * @param int $team_id Team ID
 * @param int $defense_id Defense schedule ID
 * @param string $schedule_date Defense date
 * @param string $start_time Start time
 * @param string $room Room location
 * @return bool Success status
 */
function notifyDefenseScheduled($team_id, $defense_id, $schedule_date, $start_time, $room) {
    try {
        // Get team members (including adviser)
        $team_members = getTeamMembers($team_id);
        
        // Get panelists
        $panelists = getDefensePanelists($defense_id);
        
        // Combine all recipients
        $all_recipients = array_unique(array_merge($team_members, $panelists));
        
        $title = "Defense Scheduled";
        $message = "Your team has been scheduled for defense on {$schedule_date} at {$start_time} in {$room}.";
        
        $success = true;
        foreach ($all_recipients as $user_id) {
            if ($user_id) { // Make sure user_id is not null
                $result = createNotification(
                    $user_id, 
                    'defense_scheduled', 
                    $title, 
                    $message, 
                    $team_id, 
                    'team'
                );
                if (!$result) $success = false;
            }
        }
        
        return $success;
        
    } catch (Exception $e) {
        error_log("Error in notifyDefenseScheduled: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notifications for title approval
 * @param int $team_id Team ID
 * @param string $title Research title
 * @return bool Success status
 */
function notifyTitleApproved($team_id, $research_title) {
    try {
        // Get team members excluding advisers and panelists
        $team_members = getTeamMembersExcludingAdvisers($team_id);
        
        $title = "Research Title Approved";
        $message = "Your research title '{$research_title}' has been approved!";
        
        $success = true;
        foreach ($team_members as $user_id) {
            if ($user_id) { // Make sure user_id is not null
                $result = createNotification(
                    $user_id, 
                    'title_approved', 
                    $title, 
                    $message, 
                    $team_id, 
                    'team'
                );
                if (!$result) $success = false;
            }
        }
        
        return $success;
        
    } catch (Exception $e) {
        error_log("Error in notifyTitleApproved: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notifications for new requirement
 * @param int $team_id Team ID
 * @param string $requirement_name Requirement name
 * @param int $requirement_id Requirement ID
 * @return bool Success status
 */
function notifyRequirementCreated($team_id, $requirement_name, $requirement_id) {
    try {
        // Get all team members
        $team_members = getTeamMembers($team_id);
        
        $title = "New Requirement Added";
        $message = "A new requirement '{$requirement_name}' has been added for your team.";
        
        $success = true;
        foreach ($team_members as $user_id) {
            if ($user_id) { // Make sure user_id is not null
                $result = createNotification(
                    $user_id, 
                    'requirement_created', 
                    $title, 
                    $message, 
                    $requirement_id, 
                    'requirement'
                );
                if (!$result) $success = false;
            }
        }
        
        return $success;
        
    } catch (Exception $e) {
        error_log("Error in notifyRequirementCreated: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notifications for requirement submission
 * @param int $team_id Team ID
 * @param string $requirement_name Requirement name
 * @param int $requirement_id Requirement ID
 * @return bool Success status
 */
function notifyRequirementSubmitted($team_id, $requirement_name, $requirement_id) {
    try {
        // Get team members (mainly for advisers to know about submission)
        $team_members = getTeamMembers($team_id);
        
        $title = "Requirement Submitted";
        $message = "The requirement '{$requirement_name}' has been submitted by your team.";
        
        $success = true;
        foreach ($team_members as $user_id) {
            if ($user_id) { // Make sure user_id is not null
                $result = createNotification(
                    $user_id, 
                    'requirement_submitted', 
                    $title, 
                    $message, 
                    $requirement_id, 
                    'requirement'
                );
                if (!$result) $success = false;
            }
        }
        
        return $success;
        
    } catch (Exception $e) {
        error_log("Error in notifyRequirementSubmitted: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete old notifications (cleanup function)
 * @param int $days_old Delete notifications older than this many days
 * @return bool Success status
 */
function cleanupOldNotifications($days_old = 30) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            DELETE FROM notifications 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        return $stmt->execute([$days_old]);
        
    } catch (PDOException $e) {
        error_log("Error cleaning up old notifications: " . $e->getMessage());
        return false;
    }
}

/**
 * Format notification time for display
 * @param string $created_at Timestamp from database
 * @return string Formatted time string
 */
function formatNotificationTime($created_at) {
    $time = strtotime($created_at);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

/**
 * Get notification icon class based on type
 * @param string $type Notification type
 * @return string CSS class for icon
 */
function getNotificationIcon($type) {
    switch ($type) {
        case 'defense_scheduled':
            return 'fas fa-calendar-alt';
        case 'title_approved':
            return 'fas fa-check-circle';
        case 'requirement_created':
            return 'fas fa-plus-circle';
        case 'requirement_submitted':
            return 'fas fa-file-upload';
        case 'requirement_approved':
            return 'fas fa-thumbs-up';
        case 'requirement_rejected':
            return 'fas fa-times-circle';
        default:
            return 'fas fa-bell';
    }
}

/**
 * Get notification color class based on type
 * @param string $type Notification type
 * @return string CSS class for color
 */
function getNotificationColor($type) {
    switch ($type) {
        case 'defense_scheduled':
        case 'requirement_uploaded':
            return 'primary'; // Blue for file uploads
        case 'requirement_uploaded':
            return 'fas fa-file-upload'; // Upload icon for file uploads
        case 'defense_schedule':
            return 'text-primary';
        case 'title_approved':
            return 'text-success';
        case 'requirement_created':
            return 'text-info';
        case 'requirement_submitted':
            return 'text-warning';
        case 'requirement_approved':
            return 'text-success';
        case 'requirement_rejected':
            return 'text-danger';
        default:
            return 'text-secondary';
    }
}

/**
 * Create notifications for when a defense schedule is created or updated
 * Notifies team members (adviser, leader, members) and panelists
 * 
 * @param PDO $pdo Database connection
 * @param int $scheduleId Defense schedule ID
 * @param int $teamId Team ID
 * @param array $panelistIds Array of panelist user IDs
 * @param string $scheduleDate Schedule date (Y-m-d format)
 * @param string $startTime Start time (H:i format)
 * @param string $endTime End time (H:i format)
 * @param string $room Room/venue
 * @param bool $isUpdate Whether this is an update (true) or creation (false)
 */
function createDefenseScheduleNotifications($pdo, $scheduleId, $teamId, $panelistIds, $scheduleDate, $startTime, $endTime, $room, $isUpdate = false) {
    try {
        // Get team information
        $teamStmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
        $teamStmt->execute([$teamId]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$team) {
            error_log("Team not found with ID: $teamId");
            return false;
        }
        
        $teamName = $team['name'];
        
        // Format date and time for display
        $formattedDate = date('F j, Y', strtotime($scheduleDate));
        $formattedTime = date('g:i A', strtotime($startTime)) . ' - ' . date('g:i A', strtotime($endTime));
        
        // Create notification messages
        $actionText = $isUpdate ? 'updated' : 'scheduled';
        $titleForTeam = $isUpdate ? 'Defense Schedule Updated' : 'Defense Schedule Created';
        $titleForPanelists = $isUpdate ? 'Defense Assignment Updated' : 'New Defense Assignment';
        
        $messageForTeam = "Your team's defense has been {$actionText} for {$formattedDate} at {$formattedTime} in {$room}.";
        $messageForPanelists = "You have been assigned as a panelist for {$teamName}'s defense on {$formattedDate} at {$formattedTime} in {$room}.";
        
        // Get all team members (adviser, leader, members) - returns array of user IDs
        $teamMemberIds = getTeamMembersForNotifications($teamId);
        
        // Create notifications for team members
        foreach ($teamMemberIds as $userId) {
            createNotification($pdo, $userId, $titleForTeam, $messageForTeam, 'defense_schedule', $scheduleId);
        }
        
        // Create notifications for panelists (filter out null values)
        $validPanelists = array_filter($panelistIds, function($id) { return !is_null($id) && $id !== ''; });
        
        foreach ($validPanelists as $panelistId) {
            // Don't notify panelists who are also team members (avoid duplicate notifications)
            if (!in_array($panelistId, $teamMemberIds)) {
                createNotification($pdo, $panelistId, $titleForPanelists, $messageForPanelists, 'defense_schedule', $scheduleId);
            }
        }
        
        error_log("Defense schedule notifications created for schedule ID: $scheduleId, Team: $teamName");
        return true;
        
    } catch (Exception $e) {
        error_log("Error creating defense schedule notifications: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notifications for title approval
 * Notifies all team members (excluding panels) when their title is approved
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID whose title was approved
 * @param string $titleText The approved title text
 */
function createTitleApprovalNotifications($pdo, $teamId, $titleText) {
    try {
        // Get team information
        $teamStmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
        $teamStmt->execute([$teamId]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$team) {
            error_log("Team not found with ID: $teamId");
            return false;
        }
        
        $teamName = $team['name'];
        
        // Create notification content
        $title = 'Research Title Approved';
        $message = "Great news! Your research title '{$titleText}' has been approved and you can now proceed with your research.";
        
        // Get all team members (adviser, leader, members) - excludes panels
        $teamMemberIds = getTeamMembersForNotifications($teamId);
        
        // Create notifications for each team member
        foreach ($teamMemberIds as $userId) {
            createNotification($pdo, $userId, $title, $message, 'title_approved', $teamId);
        }
        
        error_log("Title approval notifications created for team ID: $teamId, Title: $titleText");
        return true;
        
    } catch (Exception $e) {
        error_log("Error creating title approval notifications: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notifications for requirement file uploads
 * Notifies advisers when team members upload requirement files
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID that uploaded the file
 * @param string $fileName Name of the uploaded file
 * @param string|null $description File description
 */
function createRequirementUploadNotifications($pdo, $teamId, $fileName, $description = null) {
    try {
        // Get team information
        $teamStmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
        $teamStmt->execute([$teamId]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$team) {
            error_log("Team not found with ID: $teamId");
            return false;
        }
        
        $teamName = $team['name'];
        
        // Create notification content
        $title = 'New File Uploaded';
        $message = "Team '{$teamName}' has uploaded a new file: '{$fileName}'";
        
        if ($description) {
            $message .= " - {$description}";
        }
        
        // Get team adviser(s) only - they need to review uploaded files
        $adviserStmt = $pdo->prepare("
            SELECT DISTINCT tm.user_id 
            FROM team_members tm 
            JOIN users u ON tm.user_id = u.id 
            WHERE tm.team_id = ? AND u.role = 'staff'
        ");
        $adviserStmt->execute([$teamId]);
        $advisers = $adviserStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Also notify admin users who might need to review files
        $adminStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin'");
        $adminStmt->execute();
        $admins = $adminStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Combine advisers and admins
        $notifyUsers = array_merge($advisers, $admins);
        $notifyUsers = array_unique($notifyUsers); // Remove duplicates
        
        // Create notifications for advisers and admins
        foreach ($notifyUsers as $userId) {
            createNotification($pdo, $userId, $title, $message, 'requirement_uploaded', $teamId);
        }
        
        error_log("Requirement upload notifications created for team ID: $teamId, File: $fileName");
        return true;
        
    } catch (Exception $e) {
        error_log("Error creating requirement upload notifications: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notifications for requirement submissions
 * Notifies advisers when students submit requirements through the home interface
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID that submitted the requirement
 * @param int $requirementId Requirement ID that was submitted
 * @param string $fileName Name of the submitted file
 */
function createRequirementSubmissionNotifications($pdo, $teamId, $requirementId, $fileName) {
    try {
        // Get team information
        $teamStmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
        $teamStmt->execute([$teamId]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$team) {
            error_log("Team not found with ID: $teamId");
            return false;
        }
        
        // Get requirement information
        $reqStmt = $pdo->prepare("SELECT name FROM requirements WHERE id = ?");
        $reqStmt->execute([$requirementId]);
        $requirement = $reqStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$requirement) {
            error_log("Requirement not found with ID: $requirementId");
            return false;
        }
        
        $teamName = $team['name'];
        $requirementName = $requirement['name'];
        
        // Create notification content
        $title = 'New Requirement Submission';
        $message = "Team '{$teamName}' has submitted the requirement '{$requirementName}'. File: {$fileName}";
        
        // Get advisers for this team (usertype = 2 for advisers, usertype = 0 for admin)
        $adviserStmt = $pdo->prepare("
            SELECT DISTINCT u.id 
            FROM users u 
            JOIN team_members tm ON u.id = tm.user_id 
            WHERE tm.team_id = ? AND u.usertype IN (0, 2)
        ");
        $adviserStmt->execute([$teamId]);
        $advisers = $adviserStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Create notifications for advisers
        foreach ($advisers as $userId) {
            createNotification($pdo, $userId, $title, $message, 'requirement_submitted', $teamId);
        }
        
        error_log("Requirement submission notifications created for team ID: $teamId, Requirement: $requirementName");
        return true;
        
    } catch (Exception $e) {
        error_log("Error creating requirement submission notifications: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notifications for requirement feedback
 * Notifies students when advisers provide feedback on their submissions
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID that received feedback
 * @param int $requirementId Requirement ID that received feedback
 * @param string $feedback The feedback text provided
 */
function createRequirementFeedbackNotifications($pdo, $teamId, $requirementId, $feedback) {
    try {
        // Get team information
        $teamStmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
        $teamStmt->execute([$teamId]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$team) {
            error_log("Team not found with ID: $teamId");
            return false;
        }
        
        // Get requirement information
        $reqStmt = $pdo->prepare("SELECT name FROM requirements WHERE id = ?");
        $reqStmt->execute([$requirementId]);
        $requirement = $reqStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$requirement) {
            error_log("Requirement not found with ID: $requirementId");
            return false;
        }
        
        $teamName = $team['name'];
        $requirementName = $requirement['name'];
        
        // Create notification content
        $title = 'New Feedback Available';
        $message = "Your adviser has provided feedback for your '{$requirementName}' submission. Please check your requirements section to view the feedback.";
        
        // Get all team members (students) for this team (usertype = 1 for students)
        $studentsStmt = $pdo->prepare("
            SELECT DISTINCT u.id 
            FROM users u 
            JOIN team_members tm ON u.id = tm.user_id 
            WHERE tm.team_id = ? AND u.usertype = 1
        ");
        $studentsStmt->execute([$teamId]);
        $students = $studentsStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Create notifications for all team members (students)
        foreach ($students as $userId) {
            createNotification($pdo, $userId, $title, $message, 'requirement_feedback', $teamId);
        }
        
        error_log("Requirement feedback notifications created for team ID: $teamId, Requirement: $requirementName");
        return true;
        
    } catch (Exception $e) {
        error_log("Error creating requirement feedback notifications: " . $e->getMessage());
        return false;
    }
}

} // End of include guard
?>
