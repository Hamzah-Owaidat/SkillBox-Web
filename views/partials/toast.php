<?php if (!empty($_SESSION['toast_message'])): ?>
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div class="toast align-items-center text-white bg-<?= $_SESSION['toast_type'] ?? 'primary' ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <?= htmlspecialchars($_SESSION['toast_message']) ?>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>
  <?php 
    unset($_SESSION['toast_message'], $_SESSION['toast_type']);
  ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const toastEl = document.querySelector('.toast');
      const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
      toast.show();
    });
  </script>
<?php endif; ?>
