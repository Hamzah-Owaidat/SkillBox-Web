<?php
$title = "Users";

ob_start();

// Include reusable components
include __DIR__ . '/../components/actionButton.php';
include __DIR__ . '/../components/table.php';

?>

<div class="d-flex justify-content-between align-items-center">
    <h2 class="mb-4">Users</h2>
    <a href="<?= $this->baseUrl ?>/dashboard/users/add" class="btn btn-sm btn-success">
        <i class="fas fa-add"></i>
    </a>
</div>

<div class="card p-4">
  <table class="table table-hover align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role_name'] ?? '—') ?></td>
            <td>
              <a href="<?= $this->baseUrl ?>/dashboard/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-warning">
                <i class="fas fa-edit"></i>
              </a>
              <a href="<?= $this->baseUrl ?>/dashboard/users/delete/<?= $user['id'] ?>" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center">No users found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination Links -->
<?php if ($pagination['pages'] > 1): ?>
<div class="d-flex justify-content-center mt-4">
    <nav>
        <ul class="pagination">
            <!-- Previous Button -->
            <li class="page-item <?= $pagination['page'] == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>" aria-label="Previous">
                    &laquo;
                </a>
            </li>

            <!-- Page Numbers -->
            <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Next Button -->
            <li class="page-item <?= $pagination['page'] == $pagination['pages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>" aria-label="Next">
                    &raquo;
                </a>
            </li>
        </ul>
    </nav>
</div>
<?php endif; ?>

</div>



<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
