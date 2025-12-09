<?php
ob_start();
$baseUrl = '/skillbox/public';
$errors = $_SESSION['forgot_password_errors'] ?? [];
$old = $_SESSION['old_email'] ?? '';
unset($_SESSION['forgot_password_errors'], $_SESSION['old_email']);
?>

<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
  <div class="row w-100 shadow-lg rounded-4 overflow-hidden" style="max-width: 950px; background: #fff;">
    
    <!-- Left Branding -->
    <?php require __DIR__ . '/partials/branding.php'; ?>

    <!-- Right Forgot Password Side -->
    <div class="col-lg-6 bg-white d-flex flex-column justify-content-center align-items-center p-3">
      <div class="w-100" style="max-width: 370px;">
        <div class="text-center mb-4">
          <i class="bi bi-key fs-1" style="color:#25BDB0;"></i>
          <h3 class="fw-bold mt-2" style="color:#1F3440;">Forgot Password?</h3>
          <p class="text-muted mb-0">Enter your email address and we'll send you a reset code.</p>
        </div>
        <form id="forgotPasswordForm" method="POST" action="<?= $baseUrl ?>/forgot-password/send">
          <div class="mb-3">
            <label for="forgotEmail" class="form-label" style="color:#2C6566;">Email address</label>
            <input type="email" class="form-control rounded-pill <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                   id="forgotEmail" name="email" value="<?= htmlspecialchars($old) ?>" 
                   placeholder="Enter your email" autocomplete="email">
            <?php if(!empty($errors['email'])): ?>
              <div class="text-danger mt-1"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn w-100 rounded-pill fw-bold" style="background-color:#25BDB0; color:#fff;">
            <i class="bi bi-send me-2"></i>Send Reset Code
          </button>
        </form>
        <div class="text-center mt-4">
          <a href="<?= $baseUrl ?>/login" class="fw-bold" style="color:#25BDB0;">
            <i class="bi bi-arrow-left me-1"></i>Back to Login
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
$title = "Forgot Password | SkillBox";
require __DIR__ . '/../layouts/auth.php';
?>

