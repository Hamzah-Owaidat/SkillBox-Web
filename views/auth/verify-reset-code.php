<?php
ob_start();
$baseUrl = '/skillbox/public';
$errors = $_SESSION['verify_code_errors'] ?? [];
unset($_SESSION['verify_code_errors']);
?>

<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
  <div class="row w-100 shadow-lg rounded-4 overflow-hidden" style="max-width: 950px; background: #fff;">
    
    <!-- Left Branding -->
    <?php require __DIR__ . '/partials/branding.php'; ?>

    <!-- Right Verify Code Side -->
    <div class="col-lg-6 bg-white d-flex flex-column justify-content-center align-items-center p-3">
      <div class="w-100" style="max-width: 370px;">
        <div class="text-center mb-4">
          <i class="bi bi-shield-check fs-1" style="color:#25BDB0;"></i>
          <h3 class="fw-bold mt-2" style="color:#1F3440;">Verify Code</h3>
          <p class="text-muted mb-0">Enter the 6-digit code sent to your email.</p>
        </div>
        <form id="verifyCodeForm" method="POST" action="<?= $baseUrl ?>/verify-reset-code">
          <div class="mb-3">
            <label for="resetCode" class="form-label" style="color:#2C6566;">Verification Code</label>
            <input type="text" 
                   class="form-control text-center rounded-pill <?= !empty($errors['code']) ? 'is-invalid' : '' ?>" 
                   id="resetCode" 
                   name="code" 
                   maxlength="6" 
                   pattern="[0-9]{6}"
                   placeholder="000000"
                   autocomplete="one-time-code"
                   style="font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: bold;">
            <?php if(!empty($errors['code'])): ?>
              <div class="text-danger mt-1"><?= htmlspecialchars($errors['code']) ?></div>
            <?php endif; ?>
            <small class="text-muted mt-2 d-block text-center">
              <i class="bi bi-info-circle me-1"></i>Code expires in 15 minutes
            </small>
          </div>

          <button type="submit" class="btn w-100 rounded-pill fw-bold" style="background-color:#25BDB0; color:#fff;">
            <i class="bi bi-check-circle me-2"></i>Verify Code
          </button>
        </form>
        <div class="text-center mt-4">
          <a href="<?= $baseUrl ?>/forgot-password" class="fw-bold" style="color:#25BDB0;">
            <i class="bi bi-arrow-left me-1"></i>Request New Code
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('resetCode');
    
    // Only allow numbers
    codeInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });
    
    // Auto-focus and auto-submit on 6 digits
    codeInput.addEventListener('input', function(e) {
        if (e.target.value.length === 6) {
            // Optional: auto-submit after 6 digits
            // document.getElementById('verifyCodeForm').submit();
        }
    });
    
    codeInput.focus();
});
</script>

<?php
$content = ob_get_clean();
$title = "Verify Code | SkillBox";
require __DIR__ . '/../layouts/auth.php';
?>

