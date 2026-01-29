/**
 * Notifications Handler
 */

let notificationInterval;

document.addEventListener('DOMContentLoaded', function() {
    // Start fetching notifications
    fetchNotifications();
    
    // Poll for new notifications every 30 seconds
    notificationInterval = setInterval(fetchNotifications, 30000);
    
    // Notification dropdown toggle
    const notificationBell = document.getElementById('notificationBell');
    if (notificationBell) {
        notificationBell.addEventListener('click', function() {
            fetchNotifications();
        });
    }
});

// Fetch unread notifications
function fetchNotifications() {
    ajax('/simaker/api/notifications.php', 'GET')
        .then(response => {
            if (response.success) {
                updateNotificationBadge(response.count);
                updateNotificationDropdown(response.notifications);
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
        });
}

// Update notification badge
function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-bell .badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Update notification dropdown
function updateNotificationDropdown(notifications) {
    const dropdown = document.getElementById('notificationDropdown');
    if (!dropdown) return;
    
    if (notifications.length === 0) {
        dropdown.innerHTML = '<div class="dropdown-item text-center text-muted">Tidak ada notifikasi</div>';
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        const typeClass = getNotificationTypeClass(notification.type);
        html += `
            <a href="#" class="dropdown-item notification-item ${notification.is_read ? 'read' : 'unread'}" 
               data-id="${notification.id}" onclick="markAsRead(${notification.id})">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="bi ${getNotificationIcon(notification.type)} ${typeClass}"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <strong>${escapeHtml(notification.title)}</strong>
                        <p class="mb-1 text-small">${escapeHtml(notification.message)}</p>
                        <small class="text-muted">${notification.time_ago}</small>
                    </div>
                </div>
            </a>
        `;
    });
    
    dropdown.innerHTML = html;
}

// Mark notification as read
function markAsRead(notificationId) {
    ajax('/simaker/api/notifications.php', 'POST', {
        action: 'mark_read',
        id: notificationId
    }).then(response => {
        if (response.success) {
            fetchNotifications();
        }
    });
}

// Get notification icon based on type
function getNotificationIcon(type) {
    const icons = {
        'info': 'bi-info-circle',
        'success': 'bi-check-circle',
        'warning': 'bi-exclamation-triangle',
        'error': 'bi-x-circle'
    };
    return icons[type] || 'bi-bell';
}

// Get notification type class
function getNotificationTypeClass(type) {
    const classes = {
        'info': 'text-info',
        'success': 'text-success',
        'warning': 'text-warning',
        'error': 'text-danger'
    };
    return classes[type] || 'text-primary';
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
