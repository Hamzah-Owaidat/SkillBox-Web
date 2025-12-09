<?php
ob_start();
$baseUrl = '/skillbox/public';
$errors = $_SESSION['reset_password_errors'] ?? [];
unset($_SESSION['reset_password_errors']);
?>

<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
  <div class="row w-100 shadow-lg rounded-4 overflow-hidden" style="max-width: 950px; background: #fff;">
    
    <!-- Left Branding -->
    <?php require __DIR__ . '/partials/branding.php'; ?>

    <!-- Right Reset Password Side -->
    <div class="col-lg-6 bg-white d-flex flex-column justify-content-center align-items-center p-3">
      <div class="w-100" style="max-width: 370px;">
        <div class="text-center mb-4">
          <i class="bi bi-lock fs-1" style="color:#25BDB0;"></i>
          <h3 class="fw-bold mt-2" style="color:#1F3440;">Reset Password</h3>
          <p class="text-muted mb-0">Enter your new password below.</p>
        </div>
        <form id="resetPasswordForm" method="POST" action="<?= $baseUrl ?>/reset-password">
          <div class="mb-3">
            <label for="newPassword" class="form-label" style="color:#2C6566;">New Password</label>
            <input type="password" 
                   class="form-control rounded-pill <?= !empty($errors['password']) ? 'is-invalid' : '' ?>" 
                   id="newPassword" 
                   name="password" 
                   placeholder="Enter new password"
                   autocomplete="new-password">
            <?php if(!empty($errors['password'])): ?>
              <div class="text-danger mt-1"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
            <small class="text-muted mt-1 d-block">
              <i class="bi bi-info-circle me-1"></i>Must have 1 uppercase, 1 special char, and be at least 8 chars
            </small>
          </div>

          <div class="mb-3">
            <label for="confirmPassword" class="form-label" style="color:#2C6566;">Confirm Password</label>
            <input type="password" 
                   class="form-control rounded-pill <?= !empty($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                   id="confirmPassword" 
                   name="confirm_password" 
                   placeholder="Confirm new password"
                   autocomplete="new-password">
            <?php if(!empty($errors['confirm_password'])): ?>
              <div class="text-danger mt-1"><?= htmlspecialchars($errors['confirm_password']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn w-100 rounded-pill fw-bold" style="background-color:#25BDB0; color:#fff;">
            <i class="bi bi-check-circle me-2"></i>Reset Password
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetPasswordForm');
    const password = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    
    form.addEventListener('submit', function(e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            confirmPassword.classList.add('is-invalid');
            const errorDiv = confirmPassword.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('text-danger')) {
                errorDiv.textContent = 'Passwords do not match';
            } else {
                const div = document.createElement('div');
                div.className = 'text-danger mt-1';
                div.textContent = 'Passwords do not match';
                confirmPassword.parentNode.insertBefore(div, confirmPassword.nextSibling);
            }
        }
    });
    
    confirmPassword.addEventListener('input', function() {
        if (this.value === password.value) {
            this.classList.remove('is-invalid');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$title = "Reset Password | SkillBox";
require __DIR__ . '/../layouts/auth.php';
?>

