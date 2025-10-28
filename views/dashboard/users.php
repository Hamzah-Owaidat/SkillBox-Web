<?php
$title = "Users";

ob_start();

// Get current filters
$currentSearch = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$currentRole = isset($_GET['role']) ? (int)$_GET['role'] : '';
$currentStatus = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$currentLimit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
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

<!-- Search & Filter Section -->
<div class="card mb-3">
  <div class="card-body">
    <form method="GET" action="<?= $this->baseUrl ?>/dashboard/users" id="filterForm">
      <div class="row g-3">
        <!-- Search Input -->
        <div class="col-md-4">
          <label class="form-label"><i class="fas fa-search me-2"></i>Search</label>
          <input type="text"
            class="form-control"
            name="search"
            placeholder="Search by name or email..."
            value="<?= $currentSearch ?>">
        </div>

        <!-- Role Filter -->
        <div class="col-md-2">
          <label class="form-label"><i class="fas fa-user-tag me-2"></i>Role</label>
          <select class="form-select" name="role">
            <option value="">All Roles</option>
            <?php foreach ($roles as $role): ?>
              <option value="<?= $role['id'] ?>" <?= $currentRole == $role['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($role['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="col-md-2">
          <label class="form-label"><i class="fas fa-toggle-on me-2"></i>Status</label>
          <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="active" <?= $currentStatus == 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $currentStatus == 'inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>

        <!-- Rows Per Page -->
        <div class="col-md-2">
          <label class="form-label"><i class="fas fa-list me-2"></i>Rows/Page</label>
          <select class="form-select" name="limit" onchange="document.getElementById('filterForm').submit()">
            <option value="5" <?= $currentLimit == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $currentLimit == 10 ? 'selected' : '' ?>>10</option>
            <option value="25" <?= $currentLimit == 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $currentLimit == 50 ? 'selected' : '' ?>>50</option>
            <option value="100" <?= $currentLimit == 100 ? 'selected' : '' ?>>100</option>
          </select>
        </div>

        <!-- Action Buttons -->
        <div class="col-md-2 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary flex-fill">
            <i class="fas fa-filter me-2"></i>Filter
          </button>
          <a href="<?= $this->baseUrl ?>/dashboard/users" class="btn btn-secondary">
            <i class="fas fa-redo"></i>
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card p-3">
  <div class="table-responsive-custom">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created By</th>
          <th>Updated By</th>
          <th>Created At</th>
          <th>Updated At</th>
          <th class="text-center">Status</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users)): ?>
          <?php
          // Calculate row number (continuous across pages)
          $rowNumber = ($pagination['page'] - 1) * $pagination['limit'];
          foreach ($users as $user):
            $rowNumber++;
          ?>
            <tr>
              <td><strong><?= $rowNumber ?></strong></td>
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
              <td>
                <?php if (!empty($user['created_by_name'])): ?>
                  <small class="text-muted">
                    <i class="fas fa-user-plus me-1"></i>
                    <?= htmlspecialchars($user['created_by_name']) ?>
                  </small>
                <?php else: ?>
                  <small class="text-muted">-</small>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($user['updated_by_name'])): ?>
                  <small class="text-muted">
                    <i class="fas fa-user-edit me-1"></i>
                    <?= htmlspecialchars($user['updated_by_name']) ?>
                  </small>
                <?php else: ?>
                  <small class="text-muted">-</small>
                <?php endif; ?>
              </td>
              <td>
                <small class="text-muted">
                  <i class="fas fa-calendar me-1"></i>
                  <?= date('M d, Y', strtotime($user['created_at'])) ?>
                </small>
              </td>
              <td>
                <small class="text-muted">
                  <i class="fas fa-clock me-1"></i>
                  <?= date('M d, Y', strtotime($user['updated_at'])) ?>
                </small>
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
          <tr>
            <td colspan="10" class="text-center py-4">
              <i class="fas fa-users fa-3x text-muted mb-3"></i>
              <p class="text-muted">No users found.</p>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($pagination['pages'] > 1): ?>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div>
        <small class="text-muted">
          Showing <?= ($pagination['page'] - 1) * $pagination['limit'] + 1 ?>
          to <?= min($pagination['page'] * $pagination['limit'], $pagination['total']) ?>
          of <?= $pagination['total'] ?> entries
        </small>
      </div>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item <?= $pagination['page'] == 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&search=<?= urlencode($currentSearch) ?>&role=<?= $currentRole ?>&status=<?= $currentStatus ?>&limit=<?= $currentLimit ?>">&laquo;</a>
          </li>

          <?php
          // Smart pagination: show first, last, and pages around current
          $showPages = [];
          $showPages[] = 1; // Always show first page

          // Show pages around current page
          for ($i = max(2, $pagination['page'] - 2); $i <= min($pagination['pages'] - 1, $pagination['page'] + 2); $i++) {
            $showPages[] = $i;
          }

          if ($pagination['pages'] > 1) {
            $showPages[] = $pagination['pages']; // Always show last page
          }

          $showPages = array_unique($showPages);
          sort($showPages);

          $prevPage = 0;
          foreach ($showPages as $i):
            // Add ellipsis if there's a gap
            if ($i - $prevPage > 1): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>

            <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($currentSearch) ?>&role=<?= $currentRole ?>&status=<?= $currentStatus ?>&limit=<?= $currentLimit ?>"><?= $i ?></a>
            </li>

            <?php $prevPage = $i; ?>
          <?php endforeach; ?>

          <li class="page-item <?= $pagination['page'] == $pagination['pages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&search=<?= urlencode($currentSearch) ?>&role=<?= $currentRole ?>&status=<?= $currentStatus ?>&limit=<?= $currentLimit ?>">&raquo;</a>
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