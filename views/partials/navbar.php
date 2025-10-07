<?php
$baseUrl ??= '/skillbox/public';

// Simple auth simulation: set these variables somewhere in your session
// $_SESSION['user_id'] = 1;
// $_SESSION['role'] = 'client'; // 'admin', 'client', or null for guest
// $_SESSION['full_name'] = 'Hamzah Owaidat';

$role = $_SESSION['role'] ?? null;
$fullName = $_SESSION['full_name'] ?? '';
?>

<nav class="navbar navbar-expand-lg shadow-sm" style="background-color:#1F3440;">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="<?= $baseUrl ?>/">Skillbox</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto d-flex align-items-center">

        <!-- Guest Links -->
        <?php if (!$role): ?>
          <li class="nav-item"><a href="<?= $baseUrl ?>/" class="nav-link">Home</a></li>
          <li class="nav-item">
            <a href="<?= $baseUrl ?>/login" class="nav-link">GetStarted</a>
          </li>

        <!-- Client Links -->
        <?php elseif($role === 'client'): ?>
          <li class="nav-item"><a href="<?= $baseUrl ?>/" class="nav-link">Home</a></li>
          <li class="nav-item"><a href="<?= $baseUrl ?>/submit-cv" class="nav-link">Submit CV</a></li>
          <li class="nav-item"><a href="<?= $baseUrl ?>/services" class="nav-link">Services</a></li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <?= htmlspecialchars($fullName) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= $baseUrl ?>/profile">Profile</a></li>
              <li><a class="dropdown-item" href="<?= $baseUrl ?>/chating">Chat</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= $baseUrl ?>/logout">Logout</a></li>
            </ul>
          </li>

        <!-- Admin Links -->
        <?php elseif($role === 'admin'): ?>
          <li class="nav-item"><a href="<?= $baseUrl ?>/" class="nav-link">Home</a></li>
          <li class="nav-item"><a href="<?= $baseUrl ?>/dashboard" class="nav-link">Dashboard</a></li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <?= htmlspecialchars($fullName) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= $baseUrl ?>/profile">Profile</a></li>
              <li><a class="dropdown-item" href="<?= $baseUrl ?>/logout">Logout</a></li>
            </ul>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>
