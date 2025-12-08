<?php
ob_start();
$baseUrl = '/skillbox/public';
$errors = $_SESSION['login_errors'] ?? [];
$old = $_SESSION['old_email'] ?? '';
unset($_SESSION['login_errors'], $_SESSION['old_email']);
?>

<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
  <div class="row w-100 shadow-lg rounded-4 overflow-hidden" style="max-width: 950px; background: #fff;">
    
    <!-- Left Branding -->
    <?php require __DIR__ . '/partials/branding.php'; ?>

    <!-- Right Login Side -->
    <div class="col-lg-6 bg-white d-flex flex-column justify-content-center align-items-center p-3">
      <div class="w-100" style="max-width: 370px;">
        <div class="text-center mb-4">
          <i class="bi bi-person-circle fs-1" style="color:#25BDB0;"></i>
          <h3 class="fw-bold mt-2" style="color:#1F3440;">Login</h3>
          <p class="text-muted mb-0">Welcome back! Please login to your account.</p>
        </div>
        <?php 
        $redirect = $_GET['redirect'] ?? '';
        ?>
        <form id="loginForm" method="POST" action="<?= $baseUrl ?>/login<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>">
          <?php if (!empty($redirect)): ?>
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
          <?php endif; ?>
          <div class="mb-3">
            <label for="loginEmail" class="form-label" style="color:#2C6566;">Email address</label>
            <input type="text" class="form-control rounded-pill <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                   id="loginEmail" name="email" value="<?= htmlspecialchars($old) ?>">
            <?php if(!empty($errors['email'])): ?>
              <div class="text-danger mt-1"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label for="loginPassword" class="form-label" style="color:#2C6566;">Password</label>
            <input type="password" class="form-control rounded-pill <?= !empty($errors['password']) ? 'is-invalid' : '' ?>" 
                   id="loginPassword" name="password">
            <?php if(!empty($errors['password'])): ?>
              <div class="text-danger mt-1"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn w-100 rounded-pill fw-bold" style="background-color:#25BDB0; color:#fff;">Login</button>
        </form>
        <div class="text-center mt-4">
          <span class="text-muted">Don’t have an account?</span>
          <a href="<?= $baseUrl ?>/register" class="fw-bold ms-1" style="color:#EDBF43;">Register</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
$title = "Login | SkillBox";
require __DIR__ . '/../layouts/auth.php';
