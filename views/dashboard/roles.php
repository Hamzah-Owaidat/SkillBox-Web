<?php
$title = "Roles";

ob_start();
?>

<div class="d-flex justify-content-between align-items-center">
    <h2 class="mb-4">Role</h2>
    <a href="<?= $this->baseUrl ?>/dashboard/roles/add" class="btn btn-sm btn-success">
        <i class="fas fa-add"></i>
    </a>
</div>

<div class="card p-4">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($roles)): ?>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?= htmlspecialchars($role['id']) ?></td>
                        <td><?= htmlspecialchars($role['name']) ?></td>
                        <td>
                            <a href="<?= $this->baseUrl ?>/dashboard/roles/edit/<?= $role['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= $this->baseUrl ?>/dashboard/roles/delete/<?= $role['id'] ?>" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center">No roles found.</td></tr>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
