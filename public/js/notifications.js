// public/js/notifications.js
const userId = window.NOTIFICATION_USER_ID; // Set from PHP
const baseUrl = window.BASE_URL; // Set from PHP

// Helper to render a single notification item
function renderNotification(notification) {
    return `
    <li class="dropdown-item d-flex flex-column border-bottom py-2 ${notification.is_read ? '' : 'bg-light'}">
        <div class="d-flex justify-content-between align-items-center">
            <span class="fw-semibold">${notification.title}</span>
            <small class="text-muted" style="font-size: 0.7rem;">${new Date(notification.created_at).toLocaleString()}</small>
        </div>
        <div class="text-muted" style="font-size: 0.85rem;">${notification.message}</div>
    </li>`;
}

// Fetch notifications
async function fetchNotifications(limit = 10, unreadOnly = false) {
    try {
        const res = await fetch(`${baseUrl}/api/notifications?limit=${limit}&unread_only=${unreadOnly}`);
        const data = await res.json();
        const container = document.getElementById('notification-list-container');

        if (data.success && data.notifications.length > 0) {
            container.innerHTML = data.notifications.map(renderNotification).join('');
        } else {
            container.innerHTML = '<li class="text-center py-2">No notifications</li>';
        }

        updateBadge(data.notifications);
    } catch (err) {
        console.error(err);
    }
}

// Update the notification badge
function updateBadge(notifications) {
    const unreadCount = notifications.filter(n => n.is_read == 0).length;
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

// Mark all as read
document.querySelector('.dropdown-header button')?.addEventListener('click', async () => {
    try {
        const res = await fetch(`${baseUrl}/api/notifications/mark-all-read`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            fetchNotifications();
        }
    } catch (err) {
        console.error(err);
    }
});

// Initial fetch
fetchNotifications();

// ===============================
// Pusher real-time notifications
// ===============================
Pusher.logToConsole = false;

const pusher = new Pusher(window.PUSHER_KEY, {
    cluster: window.PUSHER_CLUSTER,
    encrypted: true
});

// Public channel
const channel = pusher.subscribe('notifications');

// Bind the same event name used in broadcast
channel.bind('notification.received', function(data) {
    const container = document.getElementById('notification-list-container');
    container.innerHTML = renderNotification(data) + container.innerHTML;

    const badge = document.querySelector('.notification-badge');
    let count = parseInt(badge.textContent || '0') + 1;
    badge.textContent = count;
    badge.style.display = 'inline-block';
});

