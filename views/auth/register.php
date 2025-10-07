<?php
ob_start();
$baseUrl = '/skillbox/public';
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);
?>


<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
  <div class="row w-100 shadow-lg rounded-4 overflow-hidden" style="max-width: 950px; background: #fff;">
    
    <!-- Left Branding -->
    <?php require __DIR__ . '/partials/branding.php'; ?>

    <!-- Right Register Side -->
    <div class="col-lg-6 bg-white d-flex flex-column justify-content-center align-items-center p-3">
      <div class="w-100" style="max-width: 370px;">
        <div class="text-center mb-4">
          <i class="bi bi-person-plus fs-1" style="color:#25BDB0;"></i>
          <h3 class="fw-bold mt-2" style="color:#1F3440;">Register</h3>
          <p class="text-muted mb-0">Create your account and start using SkillBox.</p>
        </div>
        <form id="registerForm" method="POST" action="<?= $baseUrl ?>/register">
          <div class="mb-3">
            <label for="registerName" class="form-label" style="color:#2C6566;">Full Name</label>
            <input type="text" class="form-control rounded-pill <?= !empty($errors['full_name']) ? 'is-invalid' : '' ?>" id="registerName" name="full_name"
                  value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
            <?php if(!empty($errors['full_name'])): ?>
              <div class="text-danger mt-1"><?= htmlspecialchars($errors['full_name']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label for="registerEmail" class="form-label" style="color:#2C6566;">Email address</label>
            <input type="text" class="form-control rounded-pill <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" id="registerEmail" name="email"
                  value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            <?php if(!empty($errors['email'])): ?>
              <div class="text-danger mt-1"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label for="registerPassword" class="form-label" style="color:#2C6566;">Password</label>
            <input type="password" class="form-control rounded-pill <?= !empty($errors['password']) ? 'is-invalid' : '' ?>" id="registerPassword" name="password">
            <?php if(!empty($errors['password'])): ?>
              <div class="text-danger mt-1"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn w-100 rounded-pill fw-bold" style="background-color:#25BDB0; color:#fff;">Register</button>
        </form>
        <div class="text-center mt-4">
          <span class="text-muted">Already have an account?</span>
          <a href="<?= $baseUrl ?>/login" class="fw-bold ms-1" style="color:#EDBF43;">Login</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
$title = "Register | SkillBox";
require __DIR__ . '/../layouts/auth.php';
