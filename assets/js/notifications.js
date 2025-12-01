/**
 * Notification System JavaScript
 * Handles notification dropdown, real-time updates, and user interactions
 */

class NotificationManager {
    constructor() {
        this.dropdownToggle = document.getElementById('notificationsDropdown');
        this.notificationsList = document.getElementById('notificationsList');
        this.notificationBadge = document.getElementById('notificationBadge');
        this.markAllReadBtn = document.getElementById('markAllReadBtn');
        this.updateInterval = null;
        this.lastCheckTime = null;
        
        this.init();
    }

    init() {
        if (!this.dropdownToggle) return; // Exit if notification elements don't exist
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Initial load
        this.loadNotifications();
        this.updateUnreadCount();
        
        // Set up periodic updates
        this.startPeriodicUpdates();
    }

    setupEventListeners() {
        // Load notifications when dropdown is opened
        this.dropdownToggle.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown-menu')) {
                this.loadNotifications();
            }
        });

        // Mark all as read
        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        // Stop periodic updates when page is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPeriodicUpdates();
            } else {
                this.startPeriodicUpdates();
                this.updateUnreadCount(); // Refresh when page becomes visible
            }
        });
    }

    async loadNotifications() {
        try {
            this.showLoading();
            
            const response = await fetch('../assets/includes/get_notifications.php?type=recent&limit=5');
            const data = await response.json();
            
            if (data.success) {
                this.displayNotifications(data.notifications);
            } else {
                this.showError('Failed to load notifications');
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError('Error loading notifications');
        }
    }

    async updateUnreadCount() {
        try {
            const response = await fetch('../assets/includes/get_notification_count.php');
            const data = await response.json();
            
            if (data.success) {
                this.updateBadge(data.count);
            }
        } catch (error) {
            console.error('Error updating notification count:', error);
        }
    }

    displayNotifications(notifications) {
        if (notifications.length === 0) {
            this.notificationsList.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-bell-slash text-muted fa-2x mb-2"></i>
                    <p class="mb-0 text-muted">No new notifications</p>
                </div>
            `;
            this.markAllReadBtn.style.display = 'none';
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            html += this.createNotificationHTML(notification);
        });

        this.notificationsList.innerHTML = html;
        this.markAllReadBtn.style.display = 'block';

        // Add click listeners to individual notifications
        this.notificationsList.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', (e) => {
                // Check if already on the notifications page
                if (window.location.pathname.includes('/notifications/')) {
                    return; // Don't redirect if already on notifications page
                }
                // Redirect to notifications page instead of marking as read
                window.location.href = '../notifications/';
            });
        });
    }

    createNotificationHTML(notification) {
        const timeAgo = notification.time_ago;
        const isUnread = !notification.is_read;
        
        return `
            <div class="notification-item px-3 py-2 border-bottom ${isUnread ? 'unread' : 'read'}" 
                 data-notification-id="${notification.id}" 
                 style="cursor: pointer; ${isUnread ? 'background-color: #f8f9fa;' : ''}">
                <div class="d-flex align-items-start">
                    <div class="notification-icon me-2 ${notification.color}">
                        <i class="${notification.icon}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="mb-1 fw-bold" style="font-size: 0.85rem;">
                                ${this.escapeHtml(notification.title)}
                                ${isUnread ? '<span class="badge bg-primary ms-1" style="font-size: 0.6rem;">New</span>' : ''}
                            </h6>
                        </div>
                        <p class="mb-1 text-dark" style="font-size: 0.8rem;">
                            ${this.escapeHtml(notification.message)}
                        </p>
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i>${timeAgo}
                        </small>
                    </div>
                </div>
            </div>
        `;
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch('../assets/includes/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_id: parseInt(notificationId)
                })
            });

            const data = await response.json();
            if (data.success) {
                // Update the UI immediately
                this.updateUnreadCount();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('../assets/includes/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    mark_all: true
                })
            });

            const data = await response.json();
            if (data.success) {
                // Update UI
                this.loadNotifications();
                this.updateUnreadCount();
                
                // Show success message
                this.showToast('Success', 'All notifications marked as read', 'success');
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            this.showToast('Error', 'Error marking notifications as read', 'error');
        }
    }

    updateBadge(count) {
        if (count > 0) {
            this.notificationBadge.textContent = count > 99 ? '99+' : count;
            this.notificationBadge.style.display = 'block';
        } else {
            this.notificationBadge.style.display = 'none';
        }
    }

    showLoading() {
        this.notificationsList.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0 mt-2 text-muted">Loading notifications...</p>
            </div>
        `;
    }

    showError(message) {
        this.notificationsList.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i>
                <p class="mb-0 text-muted">${message}</p>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="notificationManager.loadNotifications()">
                    Try Again
                </button>
            </div>
        `;
    }

    showToast(title, message, type = 'success') {
        // Create toast container if it doesn't exist
        if (!document.getElementById('toastContainer')) {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        // Generate unique ID for the toast
        const toastId = 'toast-' + Date.now();

        // Modern universal toast styling and structure
        const icon = type === 'success' ?
            `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#eaf0fe;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#1304ee"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>` :
            type === 'error' ?
            `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#fbeaea;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#dc3545"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></span>` :
            `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#fffbe6;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#ffc107"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg></span>`;

        const bgColor = type === 'success' ? '#f6fffa' : (type === 'error' ? '#fff6f6' : '#fffbe6');
        const borderColor = type === 'success' ? '#1304ee' : (type === 'error' ? '#dc3545' : '#ffc107');
        const textColor = '#222';
        
        const toastHtml = `
<div id="${toastId}" class="toast align-items-center border-0 shadow-lg"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    style="min-width:320px;max-width:400px;opacity:1;background:${bgColor};border-left:5px solid ${borderColor};border-radius:12px;margin-bottom:1rem;box-shadow:0 4px 24px 0 rgba(0,0,0,0.10);">
    <div class="d-flex align-items-center" style="padding:1rem 1.25rem;">
        ${icon}
        <div class="toast-body p-0" style="font-size:1rem;color:${textColor};line-height:1.5;">
            <div style="font-weight:600;font-size:1.08rem;margin-bottom:2px;">${title}</div>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close" style="margin-left:1.5rem;"></button>
    </div>
</div>
`;

        // Add toast to container
        document.getElementById('toastContainer').insertAdjacentHTML('beforeend', toastHtml);

        // Initialize and show the toast with modified options
        const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
            autohide: true,
            delay: 3000,
            animation: true
        });
        toastElement.show();

        // Remove toast element after it's hidden
        document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }

    startPeriodicUpdates() {
        // Update every 30 seconds
        this.updateInterval = setInterval(() => {
            this.updateUnreadCount();
        }, 30000);
    }

    stopPeriodicUpdates() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Method to manually refresh notifications (can be called from outside)
    refresh() {
        this.loadNotifications();
        this.updateUnreadCount();
    }

    // Cleanup method
    destroy() {
        this.stopPeriodicUpdates();
    }
}

// Initialize notification manager when DOM is ready
let notificationManager;

document.addEventListener('DOMContentLoaded', function() {
    notificationManager = new NotificationManager();
});

// Global function to refresh notifications (useful for calling after creating new notifications)
function refreshNotifications() {
    if (notificationManager) {
        notificationManager.refresh();
    }
}

// Export for potential use in other scripts
window.refreshNotifications = refreshNotifications;
