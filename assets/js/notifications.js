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
            
            const response = await fetch('../assets/includes/get_notifications.php?type=unread&limit=5');
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
                const notificationId = item.dataset.notificationId;
                if (notificationId && !item.classList.contains('read')) {
                    this.markAsRead(notificationId);
                    item.classList.add('read');
                    item.style.opacity = '0.7';
                }
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
                this.showToast('All notifications marked as read', 'success');
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            this.showToast('Error marking notifications as read', 'error');
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

    showToast(message, type = 'info') {
        // Create a simple toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
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
