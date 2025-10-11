<?php
/**
 * Reusable Modal Component
 * 
 * Usage:
 * $modalConfig = [
 *     'id' => 'myModal',
 *     'title' => 'Modal Title',
 *     'size' => 'lg', // sm, md, lg, xl (optional, default: md)
 *     'content' => 'Modal content here or include another file'
 * ];
 * include __DIR__ . '/../components/modal.php';
 */

$modalId = $modalConfig['id'] ?? 'defaultModal';
$modalTitle = $modalConfig['title'] ?? 'Modal';
$modalSize = $modalConfig['size'] ?? 'md';
$modalContent = $modalConfig['content'] ?? '';
?>

<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
  <div class="modal-dialog modal-<?= $modalSize ?> modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, var(--bright-teal) 0%, var(--mint) 100%); color: white;">
        <h5 class="modal-title" id="<?= $modalId ?>Label">
          <i class="fas fa-user-edit me-2"></i><?= htmlspecialchars($modalTitle) ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?= $modalContent ?>
      </div>
    </div>
  </div>
</div>