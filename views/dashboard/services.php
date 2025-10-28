<?php
$title = "Services Management";

ob_start();

// Get current filters
$currentSearch = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$currentSortBy = isset($_GET['sort_by']) ? htmlspecialchars($_GET['sort_by']) : 'id';
$currentSortOrder = isset($_GET['sort_order']) ? htmlspecialchars($_GET['sort_order']) : 'ASC';
$currentLimit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Services Management</h2>
    <div class="d-flex gap-3">
        <button class="btn btn-success" onclick="window.location.href='<?= $this->baseUrl ?>/dashboard/services/export'">
            <i class="fas fa-file-excel me-2"></i> Export to Excel
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createServiceModal">
          <i class="fas fa-plus me-2"></i> Add Service
        </button>
    </div>
</div>

<!-- Search & Filter Section -->
<div class="card mb-3">
  <div class="card-body">
    <form method="GET" action="<?= $this->baseUrl ?>/dashboard/services" id="filterForm">
      <div class="row g-3">
        <!-- Search Input -->
        <div class="col-md-4">
          <label class="form-label"><i class="fas fa-search me-2"></i>Search</label>
          <input type="text"
            class="form-control"
            name="search"
            placeholder="Search by title or description..."
            value="<?= $currentSearch ?>">
        </div>

        <!-- Sort By -->
        <div class="col-md-2">
          <label class="form-label"><i class="fas fa-sort me-2"></i>Sort By</label>
          <select class="form-select" name="sort_by">
            <option value="id" <?= $currentSortBy == 'id' ? 'selected' : '' ?>>ID</option>
            <option value="title" <?= $currentSortBy == 'title' ? 'selected' : '' ?>>Title</option>
            <option value="created_at" <?= $currentSortBy == 'created_at' ? 'selected' : '' ?>>Created Date</option>
            <option value="updated_at" <?= $currentSortBy == 'updated_at' ? 'selected' : '' ?>>Updated Date</option>
          </select>
        </div>

        <!-- Sort Order -->
        <div class="col-md-2">
          <label class="form-label"><i class="fas fa-sort-amount-down me-2"></i>Order</label>
          <select class="form-select" name="sort_order">
            <option value="ASC" <?= $currentSortOrder == 'ASC' ? 'selected' : '' ?>>Ascending</option>
            <option value="DESC" <?= $currentSortOrder == 'DESC' ? 'selected' : '' ?>>Descending</option>
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
          <a href="<?= $this->baseUrl ?>/dashboard/services" class="btn btn-secondary">
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
          <th>Icon/Emoji</th>
          <th>Title</th>
          <th>Description</th>
          <th>Created By</th>
          <th>Updated By</th>
          <th>Created At</th>
          <th>Updated At</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (isset($services) && !empty($services)): ?>
          <?php
          // Calculate row number (continuous across pages)
          $rowNumber = ($pagination['page'] - 1) * $pagination['limit'];
          foreach ($services as $service):
            $rowNumber++;
          ?>
            <tr>
              <td><strong><?= $rowNumber ?></strong></td>
              <td>
                <?php if (!empty($service['image'])): ?>
                  <span style="font-size: 2rem;"><?= htmlspecialchars($service['image']) ?></span>
                <?php else: ?>
                  <span class="text-muted">No Icon</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($service['title']) ?></td>
              <td><?= htmlspecialchars(substr($service['description'], 0, 60)) ?>...</td>
              <td>
                <?php if (!empty($service['created_by_name'])): ?>
                  <small class="text-muted">
                    <i class="fas fa-user-plus me-1"></i>
                    <?= htmlspecialchars($service['created_by_name']) ?>
                  </small>
                <?php else: ?>
                  <small class="text-muted">-</small>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($service['updated_by_name'])): ?>
                  <small class="text-muted">
                    <i class="fas fa-user-edit me-1"></i>
                    <?= htmlspecialchars($service['updated_by_name']) ?>
                  </small>
                <?php else: ?>
                  <small class="text-muted">-</small>
                <?php endif; ?>
              </td>
              <td>
                <small class="text-muted">
                  <i class="fas fa-calendar me-1"></i>
                  <?= date('M d, Y', strtotime($service['created_at'])) ?>
                </small>
              </td>
              <td>
                <small class="text-muted">
                  <i class="fas fa-clock me-1"></i>
                  <?= date('M d, Y', strtotime($service['updated_at'])) ?>
                </small>
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-info" 
                        data-bs-toggle="modal" 
                        data-bs-target="#viewServiceModal<?= $service['id'] ?>"
                        title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editServiceModal<?= $service['id'] ?>"
                        title="Edit Service">
                    <i class="fas fa-edit"></i>
                </button>
                <form method="POST" action="<?= $this->baseUrl ?>/dashboard/services/<?= $service['id'] ?>" style="display: inline-block;">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" 
                            class="btn btn-sm btn-danger" 
                            onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($service['title']) ?>?')"
                            title="Delete Service">
                    <i class="fas fa-trash"></i>
                    </button>
                </form>
              </td>
            </tr>

            <!-- View Details Modal -->
          <?php
          $modalConfig = [
              'id' => 'viewServiceModal' . $service['id'],
              'title' => 'Service Details - ' . htmlspecialchars($service['title']),
              'size' => 'lg',
              'content' => ''
          ];
          ob_start();
          ?>
          <div class="row">
            <div class="col-md-12 mb-3 text-center">
              <div style="font-size: 5rem;"><?= htmlspecialchars($service['image']) ?></div>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-heading me-2"></i>Title:</strong>
              <p><?= htmlspecialchars($service['title']) ?></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-image me-2"></i>Icon/Emoji:</strong>
              <p style="font-size: 2rem;"><?= htmlspecialchars($service['image']) ?></p>
            </div>
            <div class="col-md-12 mb-3">
              <strong><i class="fas fa-align-left me-2"></i>Description:</strong>
              <p><?= htmlspecialchars($service['description']) ?></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-user me-2"></i>Created By:</strong>
              <p><?= htmlspecialchars($service['created_by_name'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-user-edit me-2"></i>Updated By:</strong>
              <p><?= htmlspecialchars($service['updated_by_name'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-calendar me-2"></i>Created At:</strong>
              <p><?= date('F j, Y \a\t g:i A', strtotime($service['created_at'])) ?></p>
            </div>
            <div class="col-md-6 mb-3">
              <strong><i class="fas fa-calendar-check me-2"></i>Updated At:</strong>
              <p><?= date('F j, Y \a\t g:i A', strtotime($service['updated_at'])) ?></p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times me-2"></i>Close
            </button>
          </div>
          <?php
          $modalConfig['content'] = ob_get_clean();
          include __DIR__ . '/../components/modal.php';
          ?>

            <!-- Edit Modal -->
          <?php
          $modalConfig = [
              'id' => 'editServiceModal' . $service['id'],
              'title' => 'Edit Service - ' . htmlspecialchars($service['title']),
              'size' => 'lg',
              'content' => ''
          ];
          ob_start();
          ?>
          <form method="POST" action="<?= $this->baseUrl ?>/dashboard/services/<?= $service['id'] ?>">
            <input type="hidden" name="_method" value="PATCH">
            <div class="row">
              <div class="col-md-12 mb-3">
                <label class="form-label"><i class="fas fa-heading me-2"></i>Title *</label>
                <input type="text" 
                      class="form-control" 
                      name="title" 
                      value="<?= htmlspecialchars($service['title']) ?>" 
                      required
                      placeholder="Enter service title">
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label"><i class="fas fa-image me-2"></i>Icon/Emoji *</label>
                <input type="text" 
                      class="form-control" 
                      name="image" 
                      value="<?= htmlspecialchars($service['image']) ?>" 
                      required
                      placeholder="🖼️">
                <small class="text-muted">Enter an emoji (e.g., 🖼️, 📲, 🎬, 📢, 🖌️, ✏️)</small>
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label"><i class="fas fa-align-left me-2"></i>Description *</label>
                <textarea 
                      class="form-control" 
                      name="description" 
                      rows="4"
                      required
                      placeholder="Enter service description"><?= htmlspecialchars($service['description']) ?></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-2"></i>Cancel
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Update Service
              </button>
            </div>
          </form>
          <?php
          $modalConfig['content'] = ob_get_clean();
          include __DIR__ . '/../components/modal.php';
          ?>

        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="9" class="text-center py-4">
          <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
          <p class="text-muted">No Services found.</p>
        </td></tr>
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
            <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&search=<?= urlencode($currentSearch) ?>&sort_by=<?= $currentSortBy ?>&sort_order=<?= $currentSortOrder ?>&limit=<?= $currentLimit ?>">&laquo;</a>
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
              <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($currentSearch) ?>&sort_by=<?= $currentSortBy ?>&sort_order=<?= $currentSortOrder ?>&limit=<?= $currentLimit ?>"><?= $i ?></a>
            </li>

            <?php $prevPage = $i; ?>
          <?php endforeach; ?>

          <li class="page-item <?= $pagination['page'] == $pagination['pages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&search=<?= urlencode($currentSearch) ?>&sort_by=<?= $currentSortBy ?>&sort_order=<?= $currentSortOrder ?>&limit=<?= $currentLimit ?>">&raquo;</a>
          </li>
        </ul>
      </nav>
    </div>
  <?php endif; ?>
</div>

<!-- Create Service Modal -->
<?php
$modalConfig = [
    'id' => 'createServiceModal',
    'title' => 'Add New Service',
    'size' => 'lg',
    'content' => ''
];
ob_start();
?>
<form method="POST" action="<?= $this->baseUrl ?>/dashboard/services/store">
  <div class="row">
    <div class="col-md-12 mb-3">
      <label class="form-label"><i class="fas fa-heading me-2"></i>Title *</label>
      <input type="text" 
            class="form-control" 
            name="title" 
            required
            placeholder="Enter service title">
    </div>
    <div class="col-md-12 mb-3">
      <label class="form-label"><i class="fas fa-image me-2"></i>Icon/Emoji *</label>
      <input type="text" 
            class="form-control" 
            name="image" 
            required
            placeholder="🖼️">
      <small class="text-muted">Enter an emoji (e.g., 🖼️, 📲, 🎬, 📢, 🖌️, ✏️)</small>
    </div>
    <div class="col-md-12 mb-3">
      <label class="form-label"><i class="fas fa-align-left me-2"></i>Description *</label>
      <textarea 
            class="form-control" 
            name="description" 
            rows="4"
            required
            placeholder="Enter service description"></textarea>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
      <i class="fas fa-times me-2"></i>Cancel
    </button>
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-plus me-2"></i>Create Service
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