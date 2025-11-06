<?php ob_start(); ?>
<?php $baseUrl = '/skillbox/public'; ?>

<div class="container py-4">
  <h4 class="mb-4">All Notifications</h4>

  <div class="d-flex justify-content-between mb-3">
    <button class="btn btn-sm btn-outline-primary" onclick="markAllNotificationsAsRead()">Mark all as read</button>
  </div>

  <ul class="list-group" id="notificationList">
    <?php if (empty($notifications)): ?>
      <li class="list-group-item text-center text-muted py-4">No notifications yet.</li>
    <?php else: ?>
      <?php foreach ($notifications as $n): ?>
        <li class="list-group-item d-flex justify-content-between align-items-start <?= $n['is_read'] ? '' : 'bg-light' ?>" data-id="<?= $n['id'] ?>">
          <div class="ms-2 me-auto">
            <div class="fw-bold"><?= htmlspecialchars($n['title']) ?></div>
            <small><?= htmlspecialchars($n['message']) ?></small>
          </div>
          <div class="d-flex flex-column align-items-end">
            <small class="text-muted"><?= date('M d, H:i', strtotime($n['created_at'])) ?></small>
            <div class="mt-1">
              <?php if (!$n['is_read']): ?>
                <button class="btn btn-sm btn-link text-success p-0 me-2" onclick="markAsRead(<?= $n['id'] ?>)">Mark read</button>
              <?php endif; ?>
              <button class="btn btn-sm btn-link text-danger p-0" onclick="deleteNotification(<?= $n['id'] ?>)">Delete</button>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    <?php endif; ?>
  </ul>
</div>

<?php
$content = ob_get_clean();
$title = "SkillBox - Notifications";
require __DIR__ . '/layouts/main.php';
