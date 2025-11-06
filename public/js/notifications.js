// Get config from PHP
const userId = window.NOTIFICATION_USER_ID;
const baseUrl = window.BASE_URL;
const pusherKey = window.PUSHER_KEY;
const pusherCluster = window.PUSHER_CLUSTER;
const shownToasts = new Set();

// Validate config
if (!pusherKey || !userId) {
  console.error("❌ Pusher or User ID not configured");
}

// Track expanded notifications
const expandedNotifications = new Set();

// Helper to render a single notification item
function renderNotification(notification) {
  const typeIcons = {
    add: "➕",
    edit: "✏️",
    delete: "🗑️",
    accept: "✅",
    reject: "❌",
    info: "ℹ️",
    warning: "⚠️",
    error: "❌",
    success: "✅",
    announcement: "📢",
  };

  const icon = typeIcons[notification.type] || "ℹ️";
  const isRead = notification.is_read == 1 || notification.is_read === true;
  const isExpanded = expandedNotifications.has(notification.id);

  // Truncate message if not expanded
  const displayMessage = isExpanded
    ? escapeHtml(notification.message)
    : truncateText(escapeHtml(notification.message), 60);

  const hasMoreText = notification.message.length > 60;

  return `
    <li class="dropdown-item notification-item border-bottom py-2 ${isExpanded ? "expanded" : ""}" 
        data-notification-id="${notification.id}" 
        onclick="toggleNotification(${notification.id})"
        style="cursor: pointer; ${!isRead ? "background-color: #f0f9ff;" : ""} transition: all 0.3s ease;">
        <div class="d-flex align-items-start gap-2">
            <span style="font-size: 1.2rem; flex-shrink: 0;">${icon}</span>
            <div style="flex: 1; min-width: 0;">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                    <span class="fw-semibold ${
                      isRead ? "text-muted" : "text-dark"
                    }" style="font-size: 0.9rem; word-break: break-word; flex: 1;">
                        ${escapeHtml(notification.title)}
                    </span>
                    <small class="text-muted" style="font-size: 0.7rem; white-space: nowrap; flex-shrink: 0;">
                        ${formatTimeShort(notification.created_at)}
                    </small>
                </div>
                <div class="notification-message text-muted" style="font-size: 0.8rem; word-break: break-word; overflow-wrap: break-word; line-height: 1.4;">
                    ${displayMessage}${!isExpanded && hasMoreText ? '<span class="text-primary">...</span>' : ""}
                </div>
                ${
                  hasMoreText
                    ? `
                    <small class="text-primary" style="font-size: 0.75rem; margin-top: 4px; display: inline-block;">
                        ${isExpanded ? "Show less" : "Read more"}
                    </small>
                `
                    : ""
                }
            </div>
        </div>
    </li>`;
}

// Toggle notification expansion and mark as read
async function toggleNotification(notificationId) {
  const isExpanded = expandedNotifications.has(notificationId);

  if (isExpanded) {
    // Collapse
    expandedNotifications.delete(notificationId);
  } else {
    // Expand and mark as read
    expandedNotifications.add(notificationId);
    await markNotificationAsRead(notificationId);
  }

  // Re-render notifications to show the change
  fetchNotifications();
}

// Truncate long text
function truncateText(text, maxLength) {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength);
}

// Format time in short format
function formatTimeShort(timestamp) {
  if (!timestamp) return "";
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;

  if (diff < 60000) return "now";
  if (diff < 3600000) {
    const minutes = Math.floor(diff / 60000);
    return `${minutes}m`;
  }
  if (diff < 86400000) {
    const hours = Math.floor(diff / 3600000);
    return `${hours}h`;
  }
  if (diff < 604800000) {
    const days = Math.floor(diff / 86400000);
    return `${days}d`;
  }

  return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
}

// Escape HTML
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// Format time
function formatTime(timestamp) {
  if (!timestamp) return "";
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;

  if (diff < 60000) return "Just now";
  if (diff < 3600000) {
    const minutes = Math.floor(diff / 60000);
    return `${minutes} min${minutes > 1 ? "s" : ""} ago`;
  }
  if (diff < 86400000) {
    const hours = Math.floor(diff / 3600000);
    return `${hours} hour${hours > 1 ? "s" : ""} ago`;
  }

  return date.toLocaleString();
}

// Fetch notifications from API
async function fetchNotifications(limit = 10, unreadOnly = false) {
  try {
    const res = await fetch(`${baseUrl}/api/notifications?limit=${limit}&unread_only=${unreadOnly}`);
    const data = await res.json();
    const container = document.getElementById("notification-list-container");

    if (data.success && data.notifications && data.notifications.length > 0) {
      container.innerHTML = data.notifications.map(renderNotification).join("");
    } else {
      container.innerHTML = '<li class="text-center py-3 text-muted">No notifications yet</li>';
    }

    updateBadge(data.notifications || []);
  } catch (err) {
    console.error("Failed to fetch notifications:", err);
    document.getElementById("notification-list-container").innerHTML =
      '<li class="text-center py-2 text-danger">Failed to load</li>';
  }
}

// Update notification badge
function updateBadge(notifications) {
  const unreadCount = notifications.filter((n) => !n.is_read || n.is_read == 0).length;
  const badge = document.querySelector(".notification-badge");
  if (badge) {
    if (unreadCount > 0) {
      badge.textContent = unreadCount;
      badge.style.display = "inline-block";
    } else {
      badge.style.display = "none";
    }
  }
}

// Mark notification as read
async function markNotificationAsRead(notificationId) {
  try {
    const res = await fetch(`${baseUrl}/api/notifications/${notificationId}/read`, {
      method: "POST",
    });
    const data = await res.json();

    if (data.success) {
      // Don't re-fetch, just update the specific notification's styling
      const notifElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
      if (notifElement) {
        notifElement.style.backgroundColor = "";
        const title = notifElement.querySelector(".fw-semibold");
        if (title) {
          title.classList.remove("text-dark");
          title.classList.add("text-muted");
        }
      }

      // Update badge count
      fetchNotifications();
    }
  } catch (err) {
    console.error("Failed to mark as read:", err);
  }
}

// Mark all as read
async function markAllNotificationsAsRead() {
  try {
    const res = await fetch(`${baseUrl}/api/notifications/mark-all-read`, {
      method: "POST",
    });
    const data = await res.json();

    if (data.success) {
      expandedNotifications.clear();
      fetchNotifications();
    }
  } catch (err) {
    console.error("Failed to mark all as read:", err);
  }
}

/**
 * Delete a notification
 */
async function deleteNotification(id) {
  if (!confirm('Delete this notification?')) return;

  try {
    const response = await fetch(`${baseUrl}/api/notifications/${id}`, {
      method: 'DELETE',
    });

    const data = await response.json();

    if (data.success) {
      const item = document.querySelector(`[data-id="${id}"]`);
      if (item) item.remove();
    } else {
      alert('Failed to delete notification.');
    }
  } catch (error) {
    console.error('Error deleting notification:', error);
    alert('Error deleting notification.');
  }
}


// Show toast notification
function showToastNotification(data) {
  if (shownToasts.has(data.id)) return;
  shownToasts.add(data.id);
  const typeColors = {
    add: "#10b981",
    edit: "#3b82f6",
    delete: "#ef4444",
    accept: "#10b981",
    reject: "#ef4444",
    info: "#3b82f6",
    warning: "#f59e0b",
    error: "#ef4444",
    success: "#10b981",
    announcement: "#8b5cf6",
  };

  const typeIcons = {
    add: "➕",
    edit: "✏️",
    delete: "🗑️",
    accept: "✅",
    reject: "❌",
    info: "ℹ️",
    warning: "⚠️",
    error: "❌",
    success: "✅",
    announcement: "📢",
  };

  const color = typeColors[data.type] || typeColors["info"];
  const icon = typeIcons[data.type] || typeIcons["info"];

  // Create toast container if it doesn't exist
  let toastContainer = document.getElementById("notification-toast-container");
  if (!toastContainer) {
    toastContainer = document.createElement("div");
    toastContainer.id = "notification-toast-container";
    toastContainer.style.cssText = "position: fixed; top: 80px; right: 20px; z-index: 9999; max-width: 400px;";
    document.body.appendChild(toastContainer);
  }

  const toastId = "toast-" + Date.now();
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

  toastContainer.insertAdjacentHTML("beforeend", toastHtml);

  // Auto remove after 5 seconds
  setTimeout(() => {
    const toast = document.getElementById(toastId);
    if (toast) {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 150);
    }
  }, 5000);
}

// ===============================
// PUSHER REAL-TIME SETUP
// ===============================

Pusher.logToConsole = true;

const pusher = new Pusher(pusherKey, {
  cluster: pusherCluster,
  encrypted: true,
  authEndpoint: `${baseUrl}/pusher/auth`, // Authentication endpoint for private channels
  auth: {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  },
});

// ===== PUBLIC CHANNEL (for broadcasts to all users) =====
const publicChannel = pusher.subscribe("notifications");

publicChannel.bind("notification.received", function (data) {
  console.log("📢 Public notification received:", data);

  // Show toast
  showToastNotification(data);

  fetchNotifications();
});

// ===== PRIVATE CHANNEL (for user-specific notifications) =====
const privateChannel = pusher.subscribe(`private-user-${userId}`);

privateChannel.bind("notification.received", function (data) {
  console.log("🔒 Private notification received:", data);

  // Show toast
  showToastNotification(data);

  fetchNotifications();
});

// Connection status logging
pusher.connection.bind("connected", () => {
  console.log("✅ Pusher connected successfully");
  console.log(`📢 Subscribed to public: notifications`);
  console.log(`🔒 Subscribed to private: private-user-${userId}`);
});

pusher.connection.bind("error", (err) => {
  console.error("❌ Pusher connection error:", err);
});

// ===============================
// INITIAL LOAD & EVENT LISTENERS
// ===============================

// Load notifications when dropdown is clicked
document.getElementById("notificationDropdownToggle")?.addEventListener("click", function () {
  fetchNotifications();
});

// Initial fetch on page load
fetchNotifications();

// Prevent dropdown from closing when clicking inside notifications
document.addEventListener("DOMContentLoaded", () => {
  const notificationDropdown = document.getElementById("notificationDropdown");
  if (notificationDropdown) {
    notificationDropdown.addEventListener("click", (event) => {
      event.stopPropagation(); // Prevent dropdown from auto-closing
    });
  }
});

// Add CSS animation and notification styles
if (!document.getElementById("notification-animations")) {
  const style = document.createElement("style");
  style.id = "notification-animations";
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
        
        /* Notification dropdown styles */
        #notificationDropdown .dropdown-item {
            white-space: normal !important;
            padding: 0.75rem 1rem !important;
        }
        
        #notificationDropdown .dropdown-item:hover {
            background-color: #f8f9fa !important;
        }
        
        /* Notification item transitions */
        .notification-item {
            transition: all 0.3s ease;
        }
        
        .notification-item.expanded {
            background-color: #f8f9fa !important;
        }
        
        .notification-message {
            transition: max-height 0.3s ease;
        }
        
        #notification-list-container {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        /* Custom scrollbar for notification list */
        #notification-list-container::-webkit-scrollbar {
            width: 6px;
        }
        
        #notification-list-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        #notification-list-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        
        #notification-list-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    `;
  document.head.appendChild(style);
}

console.log("✅ Notification system initialized");
