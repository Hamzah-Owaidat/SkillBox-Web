<?php
$title = "Portfolios Management";

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Portfolios Management</h2>
    <div class="d-flex gap-3">
      <button class="btn btn-success" onclick="window.location.href='<?= $this->baseUrl ?>/dashboard/portfolios/export'">
        <i class="fas fa-file-excel me-2"></i> Export to Excel
      </button>
    </div>
</div>

<div class="card p-4">
  <!-- Add this wrapper div -->
  <div class="table-responsive-custom">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Address</th>
          <th>CV</th>
          <th>Requested Role</th>
          <th class="text-center">Status</th>
          <th>Created At</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (isset($portfolios) && !empty($portfolios)): ?>
          <?php foreach ($portfolios as $portfolio): ?>
            <tr>
              <td><?= htmlspecialchars($portfolio['id']) ?></td>
              <td><?= htmlspecialchars($portfolio['user_name'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($portfolio['full_name']) ?></td>
              <td><?= htmlspecialchars($portfolio['email']) ?></td>
              <td><?= htmlspecialchars($portfolio['phone'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($portfolio['address'] ?? 'N/A') ?></td>
              <td>
                <?php if (!empty($portfolio['attachment_path'])): ?>
                  <a href="<?= $this->baseUrl . '/' . ltrim(str_replace('public/', '', $portfolio['attachment_path']), '/') ?>" 
                        target="_blank" 
                        class="btn btn-sm btn-info">
                    <i class="fas fa-file-pdf"></i> View
                  </a>
                <?php else: ?>
                  <span class="text-muted">No CV</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge bg-primary">
                  <?= htmlspecialchars($portfolio['requested_role'] ?? 'N/A') ?>
                </span>
              </td>
              <td class="text-center">
                <?php if ($portfolio['status'] === 'pending'): ?>
                    <form method="POST" action="<?= $this->baseUrl ?>/dashboard/portfolios/<?= $portfolio['id'] ?>/accept" style="display:inline-block;">
                    <input type="hidden" name="_method" value="PATCH">
                    <button type="submit" class="btn btn-sm btn-success" title="Accept" onclick="return confirm('Accept this portfolio?')">
                        <i class="fas fa-check"></i>
                    </button>
                    </form>
                    <form method="POST" action="<?= $this->baseUrl ?>/dashboard/portfolios/<?= $portfolio['id'] ?>/reject" style="display:inline-block;">
                    <input type="hidden" name="_method" value="PATCH">
                    <button type="submit" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Reject this portfolio?')">
                        <i class="fas fa-times"></i>
                    </button>
                    </form>
                <?php else: ?>
                    <span class="badge bg-<?= $portfolio['status'] === 'approved' ? 'success' : 'danger' ?>">
                    <?= ucfirst($portfolio['status']) ?>
                    </span>
                <?php endif; ?>
              </td>

              <td><?= date('Y-m-d H:i', strtotime($portfolio['created_at'])) ?></td>
              <td class="text-center">
                <button class="btn btn-sm btn-info" 
                        data-bs-toggle="modal" 
                        data-bs-target="#viewPortfolioModal<?= $portfolio['id'] ?>"
                        title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
                <form method="POST" action="<?= $this->baseUrl ?>/dashboard/portfolios/<?= $portfolio['id'] ?>" style="display: inline-block;">
                  <input type="hidden" name="_method" value="DELETE">
                  <button type="submit" 
                          class="btn btn-sm btn-danger" 
                          onclick="return confirm('Are you sure you want to delete this portfolio?')"
                          title="Delete Portfolio">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            <!-- View Details Modal -->
          <?php
          $modalConfig = [
              'id' => 'viewPortfolioModal' . $portfolio['id'],
              'title' => 'Portfolio Details - ' . htmlspecialchars($portfolio['full_name']),
              'size' => 'lg',
              'content' => ''
          ];
          ob_start();
          ?>
          <div class="row">
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-user me-2"></i>Full Name:</strong>
              <p><?= htmlspecialchars($portfolio['full_name']) ?></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-envelope me-2"></i>Email:</strong>
              <p><?= htmlspecialchars($portfolio['email']) ?></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-phone me-2"></i>Phone:</strong>
              <p><?= htmlspecialchars($portfolio['phone'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-map-marker-alt me-2"></i>Address:</strong>
              <p><?= htmlspecialchars($portfolio['address'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fab fa-linkedin me-2"></i>LinkedIn:</strong>
              <p><?= !empty($portfolio['linkedin']) ? '<a href="' . htmlspecialchars($portfolio['linkedin']) . '" target="_blank">View Profile</a>' : 'N/A' ?></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-briefcase me-2"></i>Requested Role:</strong>
              <p><span class="badge bg-primary"><?= htmlspecialchars($portfolio['requested_role'] ?? 'N/A') ?></span></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-info-circle me-2"></i>Status:</strong>
              <p><span class="badge bg-<?= $portfolio['status'] == 'approved' ? 'success' : ($portfolio['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                <?= ucfirst($portfolio['status']) ?>
              </span></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-calendar me-2"></i>Submitted:</strong>
              <p><?= date('F j, Y \a\t g:i A', strtotime($portfolio['created_at'])) ?></p>
            </div>
            <?php if (!empty($portfolio['attachment_path'])): ?>
            <div class="col-12 mb-3">
              <strong><i class="fas fa-file-pdf me-2"></i>CV/Resume:</strong>
              <p><a href="<?= $this->baseUrl . '/' . ltrim(str_replace('public/', '', $portfolio['attachment_path']), '/') ?>" 
                    target="_blank" 
                    class="btn btn-sm btn-info">
                <i class="fas fa-download me-2"></i>Download CV
              </a></p>
            </div>
            <?php endif; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
          <?php
          $modalConfig['content'] = ob_get_clean();
          include __DIR__ . '/../components/modal.php';
          ?>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="11" class="text-center py-4">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <p class="text-muted">No portfolios found.</p>
          </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div> <!-- Close table-responsive-custom div -->

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