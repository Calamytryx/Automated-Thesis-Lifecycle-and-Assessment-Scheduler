<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Basic auth check before including header
if (!isset($_SESSION['id'])) {
    header("Location: ../login/");
    exit();
}

// Define constants for header
define('TITLE', 'Notifications');

// Include required files
require_once '../assets/setup/env.php';
require_once '../assets/setup/db.inc.php';
require_once '../assets/includes/auth_functions.php';
require_once '../assets/includes/notification_functions.php';

// Now include header which will also include navbar
require_once '../assets/layouts/header.php';

// Now we can safely use the functions
check_verified();
$user_id = $_SESSION['id'];

// Get parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['read', 'unread']) ? $_GET['filter'] : null;
$per_page = 20;

// Get notifications
$result = getAllNotifications($user_id, $page, $per_page, $filter);
$notifications = $result['notifications'];
$total_pages = $result['total_pages'];
$total_count = $result['total_count'];

// Format notifications
$formatted_notifications = [];
foreach ($notifications as $notification) {
        $formatted_notifications[] = [
            'id' => $notification['id'],
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'related_id' => $notification['related_id'],
            'related_type' => 'team', // Default related type since we don't have this column
            'is_read' => (bool)$notification['is_read'],
            'created_at' => $notification['created_at'],
            'time_ago' => formatNotificationTime($notification['created_at']),
            'icon' => getNotificationIcon($notification['type']),
            'color' => getNotificationColor($notification['type'])
        ];
    }
    ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="main-content">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2><i class="fas fa-bell me-2"></i>Notifications</h2>
                            <p class="text-muted">
                                <?php if ($total_count > 0): ?>
                                    Showing notifications 
                                    <?php echo (($page - 1) * $per_page) + 1; ?> - 
                                    <?php echo min($page * $per_page, $total_count); ?> 
                                    of <?php echo $total_count; ?>
                                <?php else: ?>
                                    No notifications found
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <!-- Filter Buttons -->
                            <div class="btn-group" role="group">
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['filter' => null, 'page' => 1])); ?>" 
                                   class="btn <?php echo is_null($filter) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    All
                                </a>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['filter' => 'unread', 'page' => 1])); ?>" 
                                   class="btn <?php echo $filter === 'unread' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    Unread
                                </a>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['filter' => 'read', 'page' => 1])); ?>" 
                                   class="btn <?php echo $filter === 'read' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    Read
                                </a>
                            </div>
                            
                            <!-- Mark All as Read Button -->
                            <?php if ($filter !== 'read'): ?>
                                <button type="button" class="btn btn-success" id="markAllReadBtn">
                                    <i class="fas fa-check-double me-1"></i>Mark All as Read
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    <div class="card">
                        <div class="card-body p-0">
                            <?php if (empty($formatted_notifications)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">No notifications</h4>
                                    <p class="text-muted">
                                        <?php if ($filter === 'unread'): ?>
                                            You have no unread notifications.
                                        <?php elseif ($filter === 'read'): ?>
                                            You have no read notifications.
                                        <?php else: ?>
                                            You haven't received any notifications yet.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($formatted_notifications as $notification): ?>
                                        <div class="list-group-item list-group-item-action notification-item <?php echo !$notification['is_read'] ? 'notification-unread' : ''; ?>" 
                                             data-notification-id="<?php echo $notification['id']; ?>">
                                            <div class="d-flex w-100 justify-content-between align-items-start">
                                                <div class="d-flex align-items-start">
                                                    <!-- Notification Icon -->
                                                    <div class="notification-icon me-3 <?php echo $notification['color']; ?>">
                                                        <i class="<?php echo $notification['icon']; ?> fa-lg"></i>
                                                    </div>
                                                    
                                                    <!-- Notification Content -->
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                            <?php if (!$notification['is_read']): ?>
                                                                <span class="badge bg-primary ms-2">New</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <p class="mb-1 text-dark"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                        <small class="text-muted">
                                                            <i class="far fa-clock me-1"></i><?php echo $notification['time_ago']; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                
                                                <!-- Status Indicator -->
                                                <div class="d-flex align-items-center">
                                                    <?php if (!$notification['is_read']): ?>
                                                        <span class="badge bg-primary me-2">New</span>
                                                    <?php endif; ?>
                                                    <?php 
                                                    // Show notification type icon and special actions
                                                    $typeIcon = 'fas fa-bell';
                                                    $hasActions = false;
                                                    switch($notification['type']) {
                                                        case 'defense_scheduled':
                                                            $typeIcon = 'fas fa-calendar-alt';
                                                            break;
                                                        case 'defense_approval':
                                                            $typeIcon = 'fas fa-calendar-check';
                                                            $hasActions = true;
                                                            break;
                                                        case 'title_approved':
                                                            $typeIcon = 'fas fa-check-circle';
                                                            break;
                                                        case 'requirement_submitted':
                                                            $typeIcon = 'fas fa-file-upload';
                                                            break;
                                                        case 'requirement_feedback':
                                                            $typeIcon = 'fas fa-comment';
                                                            break;
                                                    }
                                                    ?>
                                                    <i class="<?php echo $typeIcon; ?> text-muted"></i>
                                                </div>
                                            </div>
                                            
                                            <?php if ($hasActions && $notification['type'] === 'defense_approval'): ?>
                                                <!-- Defense Approval Actions -->
                                                <div class="mt-3 border-top pt-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                            Approval Required
                                                        </small>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                    onclick="showApprovalModal(<?php echo $notification['related_id']; ?>, 'reject')">
                                                                <i class="fas fa-times me-1"></i>Decline
                                                            </button>
                                                            <button type="button" class="btn btn-success btn-sm" 
                                                                    onclick="showApprovalModal(<?php echo $notification['related_id']; ?>, 'approve')">
                                                                <i class="fas fa-check me-1"></i>Accept
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Notifications pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <!-- Previous Page -->
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" 
                                       href="?<?php echo http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])); ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>

                                <!-- Page Numbers -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" 
                                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">
                                            <?php echo $total_pages; ?>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Next Page -->
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" 
                                       href="?<?php echo http_build_query(array_merge($_GET, ['page' => min($total_pages, $page + 1)])); ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../assets/layouts/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mark individual notification as read
            document.querySelectorAll('.mark-read-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const notificationId = this.dataset.notificationId;
                    markNotificationAsRead(notificationId);
                });
            });

            // Mark all notifications as read
            const markAllBtn = document.getElementById('markAllReadBtn');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function() {
                    markAllNotificationsAsRead();
                });
            }

            // Mark individual notifications as read when clicked
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    // Don't auto-mark if clicking on approval buttons or other interactive elements
                    if (e.target.closest('.btn-group') || e.target.closest('.dropdown') || e.target.closest('.mark-read-btn')) {
                        return;
                    }
                    
                    if (this.classList.contains('notification-unread')) {
                        const notificationId = this.dataset.notificationId;
                        markNotificationAsRead(notificationId);
                    }
                });
            });
        });

        function markNotificationAsRead(notificationId) {
            fetch('../assets/includes/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_id: parseInt(notificationId)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                    if (notificationItem) {
                        notificationItem.classList.remove('notification-unread');
                        const badges = notificationItem.querySelectorAll('.badge');
                        badges.forEach(badge => {
                            if (badge.textContent.trim() === 'New') {
                                badge.remove();
                            }
                        });
                    }
                } else {
                    console.error('Failed to mark notification as read:', data.error || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }

        function markAllNotificationsAsRead() {
            fetch('../assets/includes/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    mark_all: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to show updated state
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
        }

        // Defense approval modal functionality
        function showApprovalModal(scheduleId, defaultAction = null) {
            // Fetch schedule details for the modal
            fetch('../assets/includes/get_pending_approvals.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.pendingApprovals.length > 0) {
                        // Find the specific approval for this schedule
                        const approval = data.pendingApprovals.find(a => a.schedule_id == scheduleId);
                        if (approval) {
                            displayApprovalModal(approval, defaultAction);
                        } else {
                            alert('This approval request is no longer available or has already been processed.');
                        }
                    } else {
                        alert('No pending approvals found or this request has already been processed.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching approval details:', error);
                    alert('Error loading approval details. Please try again.');
                });
        }

        function displayApprovalModal(approval, defaultAction = null) {
            const modalHtml = `
                <div class="modal fade" id="notificationApprovalModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-calendar-check me-2"></i>Defense Schedule Approval
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info border-0 mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle me-2 fs-5"></i>
                                        <strong>You are assigned as a panelist for this defense:</strong>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary">
                                                    <i class="fas fa-calendar3 me-2"></i>Schedule Details
                                                </h6>
                                                <p class="mb-1"><strong>Date:</strong> ${approval.formatted_date}</p>
                                                <p class="mb-1"><strong>Time:</strong> ${approval.formatted_time}</p>
                                                <p class="mb-0"><strong>Room:</strong> ${approval.room}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary">
                                                    <i class="fas fa-users me-2"></i>Team Information
                                                </h6>
                                                <p class="mb-1"><strong>Team:</strong> ${approval.team_name}</p>
                                                <p class="mb-1"><strong>Program:</strong> ${approval.program || 'N/A'}</p>
                                                <p class="mb-0"><strong>Research:</strong> ${approval.research_title || 'N/A'}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="text-primary">
                                        <i class="fas fa-user-friends me-2"></i>Other Panelists
                                    </h6>
                                    <p class="mb-0">${approval.other_panelists}</p>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="modalRejectionReason" class="form-label">
                                        <i class="fas fa-comment me-2"></i>Reason for declining (optional):
                                    </label>
                                    <textarea class="form-control" id="modalRejectionReason" rows="3" 
                                              placeholder="Please provide a reason if you cannot attend this defense session..."></textarea>
                                </div>
                                
                                <div class="d-flex justify-content-end gap-3">
                                    <button type="button" class="btn btn-outline-danger" onclick="processApprovalFromModal('reject', ${approval.schedule_id})">
                                        <i class="fas fa-times me-2"></i>Decline
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="processApprovalFromModal('approve', ${approval.schedule_id})">
                                        <i class="fas fa-check me-2"></i>Accept
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('notificationApprovalModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('notificationApprovalModal'));
            modal.show();
            
            // Clean up when modal is hidden
            document.getElementById('notificationApprovalModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        function processApprovalFromModal(action, scheduleId) {
            const reason = document.getElementById('modalRejectionReason').value.trim();
            const modalElement = document.getElementById('notificationApprovalModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            
            // Show loading state
            const modalBody = modalElement.querySelector('.modal-body');
            const originalContent = modalBody.innerHTML;
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Processing...</span>
                    </div>
                    <p>Processing your response...</p>
                </div>
            `;
            
            fetch('../assets/includes/handle_defense_approval.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: action,
                    schedule_id: scheduleId,
                    rejection_reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    modalBody.innerHTML = `
                        <div class="text-center py-4">
                            <div class="text-success mb-3">
                                <i class="fas fa-check-circle" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-success">Response Recorded!</h5>
                            <p class="mb-0">${data.message}</p>
                        </div>
                    `;
                    
                    // Close modal and refresh page after 2 seconds
                    setTimeout(function() {
                        modal.hide();
                        window.location.reload();
                    }, 2000);
                    
                } else {
                    // Show error message
                    modalBody.innerHTML = `
                        <div class="text-center py-4">
                            <div class="text-danger mb-3">
                                <i class="fas fa-exclamation-circle" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-danger">Error</h5>
                            <p class="mb-3">${data.message}</p>
                            <button class="btn btn-primary" onclick="window.location.reload()">
                                <i class="fas fa-redo me-2"></i>Refresh Page
                            </button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error processing approval:', error);
                modalBody.innerHTML = `
                    <div class="text-center py-4">
                        <div class="text-danger mb-3">
                            <i class="fas fa-exclamation-triangle" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-danger">Connection Error</h5>
                        <p class="mb-3">Failed to process your response. Please try again.</p>
                        <button class="btn btn-primary" onclick="window.location.reload()">
                            <i class="fas fa-redo me-2"></i>Try Again
                        </button>
                    </div>
                `;
            });
        }
    </script>
</body>

</html>
