<?php
$baseUrl ??= '/skillbox/public';
$role = $_SESSION['role'] ?? null;
$fullName = $_SESSION['full_name'] ?? '';
$userId = $_SESSION['user_id'] ?? null;
?>

<nav class="navbar navbar-expand-lg shadow-sm py-3" style="background-color:#1F3440;">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="<?= $baseUrl ?>/">Skillbox</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto d-flex align-items-center gap-2">

        <?php if (!$role): ?>
          <!-- Guest Links -->
          <li class="nav-item"><a href="<?= $baseUrl ?>/" class="nav-link">Home</a></li>
          <li class="nav-item"><a href="<?= $baseUrl ?>/login" class="nav-link">Get Started</a></li>

        <?php else: ?>
          <!-- Common Links for all logged-in users -->
          <li class="nav-item"><a href="<?= $baseUrl ?>/" class="nav-link">Home</a></li>

          <?php if ($role === 'client'): ?>
            <li class="nav-item"><a href="<?= $baseUrl ?>/submit-cv" class="nav-link">Submit CV</a></li>
            <li class="nav-item"><a href="<?= $baseUrl ?>/services" class="nav-link">Services</a></li>
          <?php else: ?>
            <li class="nav-item"><a href="<?= $baseUrl ?>/dashboard" class="nav-link">Dashboard</a></li>
          <?php endif; ?>

          <!-- 🔔 Notification Dropdown -->
          <li class="nav-item dropdown me-3">
            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationDropdownToggle">
              <i class="fas fa-bell text-white fs-5"></i>
              <!-- Badge (hidden when zero) -->
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="font-size: 0.7rem; display: none;">
                0
              </span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="width: 350px; max-height: 450px; overflow-y: auto;" id="notificationDropdown">
              <li class="dropdown-header d-flex justify-content-between align-items-center border-bottom pb-2">
                <h6 class="mb-0">Notifications</h6>
                <button class="btn btn-sm btn-link text-primary p-0" style="text-decoration: none; font-size: 0.85rem;">
                  Mark all read
                </button>
              </li>

              <!-- Notification List Container -->
              <div id="notification-list-container">
                <li class="text-center py-3">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </li>
              </div>

              <li class="border-top">
                <a class="dropdown-item text-center text-primary fw-semibold py-2" href="<?= $baseUrl ?>/notifications">
                  View all notifications
                </a>
              </li>
            </ul>
          </li>

          <!-- Profile Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
              <?= htmlspecialchars($fullName) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= $baseUrl ?>/profile">Profile</a></li>
              <?php if ($role): ?>
                <li><a class="dropdown-item" href="<?= $baseUrl ?>/chat">Chats</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= $baseUrl ?>/logout">Logout</a></li>
            </ul>
          </li>

        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>
