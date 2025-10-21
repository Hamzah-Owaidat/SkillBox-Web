// Get config from PHP
const userId = window.NOTIFICATION_USER_ID;
const baseUrl = window.BASE_URL;
const pusherKey = window.PUSHER_KEY;
const pusherCluster = window.PUSHER_CLUSTER;
const shownToasts = new Set();


// Validate config
if (!pusherKey || !userId) {
    console.error('❌ Pusher or User ID not configured');
}

// Helper to render a single notification item
function renderNotification(notification) {
    const typeIcons = {
        'add': '➕',
        'edit': '✏️',
        'delete': '🗑️',
        'accept': '✅',
        'reject': '❌',
        'info': 'ℹ️',
        'warning': '⚠️',
        'error': '❌',
        'success': '✅',
        'announcement': '📢'
    };
    
    const icon = typeIcons[notification.type] || 'ℹ️';
    const isRead = notification.is_read == 1 || notification.is_read === true;
    
    return `
    <li class="dropdown-item d-flex flex-column border-bottom py-2 ${isRead ? '' : 'bg-light'}" 
        data-notification-id="${notification.id}" 
        onclick="markNotificationAsRead(${notification.id})">
        <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex align-items-start flex-grow-1">
                <span class="me-2" style="font-size: 1.2rem;">${icon}</span>
                <div class="flex-grow-1">
                    <span class="fw-semibold ${isRead ? 'text-muted' : 'text-dark'}">${escapeHtml(notification.title)}</span>
                    <div class="text-muted" style="font-size: 0.85rem;">${escapeHtml(notification.message)}</div>
                </div>
            </div>
            <small class="text-muted ms-2" style="font-size: 0.7rem; white-space: nowrap;">
                ${formatTime(notification.created_at)}
            </small>
        </div>
    </li>`;
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Format time
function formatTime(timestamp) {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) {
        const minutes = Math.floor(diff / 60000);
        return `${minutes} min${minutes > 1 ? 's' : ''} ago`;
    }
    if (diff < 86400000) {
        const hours = Math.floor(diff / 3600000);
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    }
    
    return date.toLocaleString();
}

// Fetch notifications from API
async function fetchNotifications(limit = 10, unreadOnly = false) {
    try {
        const res = await fetch(`${baseUrl}/api/notifications?limit=${limit}&unread_only=${unreadOnly}`);
        const data = await res.json();
        const container = document.getElementById('notification-list-container');

        if (data.success && data.notifications && data.notifications.length > 0) {
            container.innerHTML = data.notifications.map(renderNotification).join('');
        } else {
            container.innerHTML = '<li class="text-center py-3 text-muted">No notifications yet</li>';
        }

        updateBadge(data.notifications || []);
    } catch (err) {
        console.error('Failed to fetch notifications:', err);
        document.getElementById('notification-list-container').innerHTML = 
            '<li class="text-center py-2 text-danger">Failed to load</li>';
    }
}

// Update notification badge
function updateBadge(notifications) {
    const unreadCount = notifications.filter(n => !n.is_read || n.is_read == 0).length;
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Mark notification as read
async function markNotificationAsRead(notificationId) {
    try {
        const res = await fetch(`${baseUrl}/api/notifications/${notificationId}/read`, { 
            method: 'POST' 
        });
        const data = await res.json();
        
        if (data.success) {
            // Update UI
            const notifElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notifElement) {
                notifElement.classList.remove('bg-light');
                const title = notifElement.querySelector('.fw-semibold');
                if (title) title.classList.remove('text-dark');
                if (title) title.classList.add('text-muted');
            }
            
            // Update badge
            fetchNotifications();
        }
    } catch (err) {
        console.error('Failed to mark as read:', err);
    }
}

// Mark all as read
async function markAllNotificationsAsRead() {
    try {
        const res = await fetch(`${baseUrl}/api/notifications/mark-all-read`, { 
            method: 'POST' 
        });
        const data = await res.json();
        
        if (data.success) {
            fetchNotifications();
        }
    } catch (err) {
        console.error('Failed to mark all as read:', err);
    }
}

// Show toast notification
function showToastNotification(data) {
    if (shownToasts.has(data.id)) return;
    shownToasts.add(data.id);
    const typeColors = {
        'add': '#10b981',
        'edit': '#3b82f6',
        'delete': '#ef4444',
        'accept': '#10b981',
        'reject': '#ef4444',
        'info': '#3b82f6',
        'warning': '#f59e0b',
        'error': '#ef4444',
        'success': '#10b981',
        'announcement': '#8b5cf6'
    };
    
    const typeIcons = {
        'add': '➕',
        'edit': '✏️',
        'delete': '🗑️',
        'accept': '✅',
        'reject': '❌',
        'info': 'ℹ️',
        'warning': '⚠️',
        'error': '❌',
        'success': '✅',
        'announcement': '📢'
    };
    
    const color = typeColors[data.type] || typeColors['info'];
    const icon = typeIcons[data.type] || typeIcons['info'];
    
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('notification-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'notification-toast-container';
        toastContainer.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="alert alert-dismissible fade show shadow-lg mb-3" role="alert" 
             style="border-left: 4px solid ${color}; background: white; animation: slideInRight 0.3s ease-out;">
            <div class="d-flex align-items-start">
                <div class="me-2" style="font-size: 24px;">${icon}</div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1" style="font-size: 14px;">${escapeHtml(data.title)}</h6>
                    <p class="mb-0" style="font-size: 13px; color: #6b7280;">${escapeHtml(data.message)}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 150);
        }
    }, 5000);
}

// ===============================
// PUSHER REAL-TIME SETUP
// ===============================

Pusher.logToConsole = true; // Enable for debugging

const pusher = new Pusher(pusherKey, {
    cluster: pusherCluster,
    encrypted: true,
    authEndpoint: `${baseUrl}/pusher/auth`, // Authentication endpoint for private channels
    auth: {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }
});

// ===== PUBLIC CHANNEL (for broadcasts to all users) =====
const publicChannel = pusher.subscribe('notifications');

publicChannel.bind('notification.received', function(data) {
    console.log('📢 Public notification received:', data);
    
    // Show toast
    showToastNotification(data);
    
    // ALWAYS fetch fresh notifications from API
    // This ensures the dropdown has the latest data when opened
    fetchNotifications();
});

// ===== PRIVATE CHANNEL (for user-specific notifications) =====
const privateChannel = pusher.subscribe(`private-user-${userId}`);

privateChannel.bind('notification.received', function(data) {
    console.log('🔒 Private notification received:', data);
    
    // Show toast
    showToastNotification(data);
    
    // ✅ FIX: ALWAYS fetch fresh notifications from API
    // This ensures the notification is in the dropdown when opened
    // AND updates the badge count correctly
    fetchNotifications();
});

// Connection status logging
pusher.connection.bind('connected', () => {
    console.log('✅ Pusher connected successfully');
    console.log(`📢 Subscribed to public: notifications`);
    console.log(`🔒 Subscribed to private: private-user-${userId}`);
});

pusher.connection.bind('error', (err) => {
    console.error('❌ Pusher connection error:', err);
});

// ===============================
// INITIAL LOAD & EVENT LISTENERS
// ===============================

// Load notifications when dropdown is clicked
document.getElementById('notificationDropdownToggle')?.addEventListener('click', function() {
    fetchNotifications();
});

// Initial fetch on page load
fetchNotifications();

// Add CSS animation
if (!document.getElementById('notification-animations')) {
    const style = document.createElement('style');
    style.id = 'notification-animations';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
}

console.log('✅ Notification system initialized');