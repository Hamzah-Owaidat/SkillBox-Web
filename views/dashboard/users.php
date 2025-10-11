<?php
$title = "Users";

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Users Management</h2>
    <div class="d-flex justify-content-between align-items-center gap-3">
      <button class="btn btn-success" onclick="window.location.href='/skillbox/public/dashboard/users/export'">
        <i class="fas fa-file-excel me-2"></i> Export to Excel
      </button>

      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="fas fa-plus me-2"></i>Add New User
      </button>
    </div>
</div>

<div class="card p-4">
  <table class="table table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Role</th>
        <th class="text-center">Status</th>
        <th class="text-center">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td>
              <div class="d-flex align-items-center">
                <?= htmlspecialchars($user['full_name']) ?>
              </div>
            </td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
              <span class="badge bg-<?= $user['role_id'] == 1 ? 'danger' : 'primary' ?>">
                <?= htmlspecialchars($user['role_name'] ?? 'No Role') ?>
              </span>
            </td>
            <td class="text-center">
              <!-- Status Toggle Switch -->
              <form method="POST" action="<?= $this->baseUrl ?>/dashboard/users/<?= $user['id'] ?>/toggle-status" style="display: inline-block;">
                <input type="hidden" name="_method" value="PATCH">
                <div class="form-check form-switch d-inline-block">
                  <input class="form-check-input status-toggle" 
                         type="checkbox" 
                         role="switch" 
                         id="statusSwitch<?= $user['id'] ?>"
                         <?= $user['status'] == 'active' ? 'checked' : '' ?>
                         onchange="this.form.submit()"
                         style="cursor: pointer;">
                </div>
              </form>
            </td>
            <td class="text-center">
              <button class="btn btn-sm btn-warning" 
                      data-bs-toggle="modal" 
                      data-bs-target="#editUserModal<?= $user['id'] ?>"
                      title="Edit User">
                <i class="fas fa-edit"></i>
              </button>
              <form method="POST" action="<?= $this->baseUrl ?>/dashboard/users/<?= $user['id'] ?>" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" 
                        class="btn btn-sm btn-danger" 
                        onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($user['full_name']) ?>?')"
                        title="Delete User">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>

          <!-- Edit Modal for each user -->
          <?php
          $modalConfig = [
              'id' => 'editUserModal' . $user['id'],
              'title' => 'Edit User - ' . htmlspecialchars($user['full_name']),
              'size' => 'lg',
              'content' => ''
          ];
          ob_start();
          ?>
          <form method="POST" action="<?= $this->baseUrl ?>/dashboard/users/<?= $user['id'] ?>">
            <input type="hidden" name="_method" value="PATCH">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label"><i class="fas fa-user me-2"></i>Full Name *</label>
                <input type="text" 
                      class="form-control" 
                      name="full_name" 
                      value="<?= htmlspecialchars($user['full_name']) ?>" 
                      required
                      placeholder="Enter full name">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label"><i class="fas fa-envelope me-2"></i>Email *</label>
                <input type="email" 
                      class="form-control" 
                      name="email" 
                      value="<?= htmlspecialchars($user['email']) ?>" 
                      required
                      placeholder="user@example.com">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                <input type="password" 
                      class="form-control" 
                      name="password" 
                      placeholder="Leave blank to keep current password">
                <small class="text-muted">Minimum 6 characters</small>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label"><i class="fas fa-user-tag me-2"></i>Role *</label>
                <select class="form-select" name="role_id" required>
                  <option value="">Select Role</option>
                  <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($role['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-2"></i>Cancel
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Update User
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
          <p class="text-muted">No users found.</p>
        </td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <?php if ($pagination['pages'] > 1): ?>
  <div class="d-flex justify-content-center mt-4">
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
    'id' => 'addUserModal',
    'title' => 'Add New User',
    'size' => 'lg',
    'content' => ''
];
ob_start();
?>
<form method="POST" action="<?= $this->baseUrl ?>/dashboard/users/store">
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label"><i class="fas fa-user me-2"></i>Full Name *</label>
      <input type="text" 
             class="form-control" 
             name="full_name" 
             required
             placeholder="Enter full name">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label"><i class="fas fa-envelope me-2"></i>Email *</label>
      <input type="email" 
             class="form-control" 
             name="email" 
             required
             placeholder="user@example.com">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label"><i class="fas fa-lock me-2"></i>Password *</label>
      <input type="password" 
             class="form-control" 
             name="password" 
             required
             placeholder="Enter password">
      <small class="text-muted">Minimum 6 characters</small>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label"><i class="fas fa-user-tag me-2"></i>Role *</label>
      <select class="form-select" name="role_id" required>
        <option value="">Select Role</option>
        <?php foreach ($roles as $role): ?>
          <option value="<?= $role['id'] ?>" <?= $role['id'] == 2 ? 'selected' : '' ?>>
            <?= htmlspecialchars($role['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Note:</strong> User will be created with the selected role. Default role is Client.
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
      <i class="fas fa-times me-2"></i>Cancel
    </button>
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-plus me-2"></i>Add User
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