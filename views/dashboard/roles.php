<?php
$title = "Roles";

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Roles Management</h2>
    <div class="d-flex justify-content-between align-items-center gap-3">
      <button class="btn btn-success" onclick="window.location.href='/skillbox/public/dashboard/roles/export'">
        <i class="fas fa-file-excel me-2"></i> Export to Excel
      </button>

      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRoleModal">
          <i class="fas fa-plus me-2"></i>Add New Role
      </button>
    </div>
</div>

<div class="card p-3">
  <table class="table table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th class="text-center">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($roles)): ?>
        <?php foreach ($roles as $role): ?>
          <tr>
            <td>
                <?= htmlspecialchars($role['id']) ?>
            </td>

            <td>
              <div class="d-flex align-items-center">
                <?= htmlspecialchars($role['name']) ?>
              </div>
            </td>

            <td class="text-center">
              <button class="btn btn-sm btn-warning" 
                      data-bs-toggle="modal" 
                      data-bs-target="#editRoleModal<?= $role['id'] ?>"
                      title="Edit Role">
                <i class="fas fa-edit"></i>
              </button>
              <form method="POST" action="<?= $this->baseUrl ?>/dashboard/roles/<?= $role['id'] ?>" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" 
                        class="btn btn-sm btn-danger" 
                        onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($role['name']) ?>?')"
                        title="Delete Role">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>

          <!-- Edit Modal for each user -->
          <?php
          $modalConfig = [
              'id' => 'editRoleModal' . $role['id'],
              'title' => 'Edit role - ' . htmlspecialchars($role['name']),
              'size' => 'lg',
              'content' => ''
          ];
          ob_start();
          ?>
          <form method="POST" action="<?= $this->baseUrl ?>/dashboard/roles/<?= $role['id'] ?>">
            <input type="hidden" name="_method" value="PATCH">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label"><i class="fas fa-user-shield me-2"></i>Name *</label>
                <input type="text" 
                      class="form-control" 
                      name="name" 
                      value="<?= htmlspecialchars($role['name']) ?>" 
                      required
                      placeholder="Enter role name">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-2"></i>Cancel
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Update Role
              </button>
            </div>
          </form>
          <?php
          $modalConfig['content'] = ob_get_clean();
          include __DIR__ . '/../components/modal.php';
          ?>

        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center py-4">
          <i class="fas fa-users fa-3x text-muted mb-3"></i>
          <p class="text-muted">No roles found.</p>
        </td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <?php if ($pagination['pages'] > 1): ?>
  <div class="d-flex justify-content-center mt-2">
      <nav>
          <ul class="pagination">
              <li class="page-item <?= $pagination['page'] == 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>">&laquo;</a>
              </li>
              <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                  <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                      <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                  </li>
              <?php endfor; ?>
              <li class="page-item <?= $pagination['page'] == $pagination['pages'] ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>">&raquo;</a>
              </li>
          </ul>
      </nav>
  </div>
  <?php endif; ?>
</div>

<!-- Add User Modal -->
<?php
$modalConfig = [
    'id' => 'addRoleModal',
    'title' => 'Add New Role',
    'size' => 'lg',
    'content' => ''
];
ob_start();
?>
<form method="POST" action="<?= $this->baseUrl ?>/dashboard/roles/store">
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label"><i class="fas fa-user-shield me-2"></i>Name *</label>
      <input type="text" 
             class="form-control" 
             name="name" 
             required
             placeholder="Enter role name">
    </div>
  </div>

  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
      <i class="fas fa-times me-2"></i>Cancel
    </button>
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-plus me-2"></i>Add Role
    </button>
  </div>
</form>
<?php
$modalConfig['content'] = ob_get_clean();
include __DIR__ . '/../components/modal.php';
?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>