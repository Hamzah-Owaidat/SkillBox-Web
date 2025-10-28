<?php
$title = "Portfolios Management";

ob_start();

// Get current filters
$currentSearch = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$currentStatus = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$currentRole = isset($_GET['role']) ? (int)$_GET['role'] : '';
$currentSortBy = isset($_GET['sort_by']) ? htmlspecialchars($_GET['sort_by']) : 'created_at';
$currentSortOrder = isset($_GET['sort_order']) ? htmlspecialchars($_GET['sort_order']) : 'DESC';
$currentLimit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Portfolios Management</h2>
    <div class="d-flex gap-3">
      <button class="btn btn-success" onclick="window.location.href='<?= $this->baseUrl ?>/dashboard/portfolios/export'">
        <i class="fas fa-file-excel me-2"></i> Export to Excel
      </button>
    </div>
</div>

<!-- Summary Stats (Optional) -->
<div class="row mb-4">
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <h5 class="text-warning">Pending</h5>
        <h3><?= $totalPortfolios['pending'] ?? 0 ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <h5 class="text-success">Approved</h5>
        <h3><?= $totalPortfolios['approved'] ?? 0 ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <h5 class="text-danger">Rejected</h5>
        <h3><?= $totalPortfolios['rejected'] ?? 0 ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-center">
      <div class="card-body">
        <h5 class="text-primary">Total</h5>
        <h3><?= $pagination['total'] ?></h3>
      </div>
    </div>
  </div>
</div>


<!-- Search & Filter Section -->
<div class="card mb-3">
  <div class="card-body">
    <form method="GET" action="<?= $this->baseUrl ?>/dashboard/portfolios" id="filterForm">
      <div class="row g-3">
        <!-- Search Input -->
        <div class="col-md-3">
          <label class="form-label"><i class="fas fa-search me-2"></i>Search</label>
          <input type="text"
            class="form-control"
            name="search"
            placeholder="Search by name, email, phone..."
            value="<?= $currentSearch ?>">
        </div>

        <!-- Status Filter -->
        <div class="col-md-2">
          <label class="form-label"><i class="fas fa-check-circle me-2"></i>Status</label>
          <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="pending" <?= $currentStatus == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= $currentStatus == 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= $currentStatus == 'rejected' ? 'selected' : '' ?>>Rejected</option>
          </select>
        </div>

        <!-- Role Filter -->
        <div class="col-md-2">
          <label class="form-label"><i class="fas fa-user-tag me-2"></i>Requested Role</label>
          <select class="form-select" name="role">
            <option value="">All Roles</option>
            <?php foreach ($roles as $role): ?>
              <option value="<?= $role['id'] ?>" <?= $currentRole == $role['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($role['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Sort By -->
        <div class="col-md-1">
          <label class="form-label"><i class="fas fa-sort me-2"></i>Sort</label>
          <select class="form-select" name="sort_by">
            <option value="id" <?= $currentSortBy == 'id' ? 'selected' : '' ?>>ID</option>
            <option value="full_name" <?= $currentSortBy == 'full_name' ? 'selected' : '' ?>>Name</option>
            <option value="email" <?= $currentSortBy == 'email' ? 'selected' : '' ?>>Email</option>
            <option value="status" <?= $currentSortBy == 'status' ? 'selected' : '' ?>>Status</option>
            <option value="created_at" <?= $currentSortBy == 'created_at' ? 'selected' : '' ?>>Date</option>
          </select>
        </div>

        <!-- Sort Order -->
        <div class="col-md-1">
          <label class="form-label"><i class="fas fa-sort-amount-down me-2"></i>Order</label>
          <select class="form-select" name="sort_order">
            <option value="ASC" <?= $currentSortOrder == 'ASC' ? 'selected' : '' ?>>ASC</option>
            <option value="DESC" <?= $currentSortOrder == 'DESC' ? 'selected' : '' ?>>DESC</option>
          </select>
        </div>

        <!-- Rows Per Page -->
        <div class="col-md-1">
          <label class="form-label"><i class="fas fa-list me-2"></i>Rows</label>
          <select class="form-select" name="limit" onchange="document.getElementById('filterForm').submit()">
            <option value="5" <?= $currentLimit == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $currentLimit == 10 ? 'selected' : '' ?>>10</option>
            <option value="25" <?= $currentLimit == 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $currentLimit == 50 ? 'selected' : '' ?>>50</option>
          </select>
        </div>

        <!-- Action Buttons -->
        <div class="col-md-2 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary flex-fill">
            <i class="fas fa-filter me-2"></i>Filter
          </button>
          <a href="<?= $this->baseUrl ?>/dashboard/portfolios" class="btn btn-secondary">
            <i class="fas fa-redo"></i>
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card p-3">
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
          <th>Services</th>
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
                    <i class="fas fa-file-pdf"></i>
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
              <td>
                <?php if (!empty($portfolio['services'])): ?>
                  <?php foreach ($portfolio['services'] as $service): ?>
                    <span class="badge bg-info mb-1"><?= htmlspecialchars($service['title']) ?></span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="text-muted">No Services</span>
                <?php endif; ?>
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
              <strong><i class="fas fa-briefcase me-2"></i>Services:</strong>
              <p>
                <?php if (!empty($portfolio['services'])): ?>
                  <?php foreach ($portfolio['services'] as $service): ?>
                    <span class="badge bg-info mb-1"><?= htmlspecialchars($service['title']) ?></span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="text-muted">No Services</span>
                <?php endif; ?>
              </p>
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
  <div class="d-flex justify-content-center mt-3">
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