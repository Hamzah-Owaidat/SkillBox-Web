<?php
$title = "Services Management";

ob_start();
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

<div class="card p-3">
  <div class="table-responsive-custom">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Icon/Emoji</th>
          <th>Title</th>
          <th>Description</th>
          <th>Supervisors</th>
          <th>Created By</th>
          <th>Updated By</th>
          <th>Created At</th>
          <th>Updated At</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (isset($services) && !empty($services)): ?>
          <?php foreach ($services as $service): ?>
            <tr>
              <td><?= htmlspecialchars($service['id']) ?></td>
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
                <?php if (!empty($service['supervisors'])): ?>
                  <span class="badge bg-info text-dark">
                    <i class="fas fa-users me-1"></i><?= count($service['supervisors']) ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted">No Supervisors</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($service['created_by_name'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($service['updated_by_name'] ?? 'N/A') ?></td>
              <td><?= date('Y-m-d H:i', strtotime($service['created_at'])) ?></td>
              <td><?= date('Y-m-d H:i', strtotime($service['updated_at'])) ?></td>
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
            <div class="col-md-12 mb-3">
              <strong><i class="fas fa-users me-2"></i>Supervisors:</strong>
              <?php if (!empty($service['supervisors'])): ?>
                <div class="mt-2">
                  <?php foreach ($service['supervisors'] as $supervisor): ?>
                    <span class="badge bg-primary me-2 mb-2">
                      <i class="fas fa-user me-1"></i><?= htmlspecialchars($supervisor['full_name']) ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="text-muted mt-2">No supervisors assigned</p>
              <?php endif; ?>
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
          
          // Get current supervisor IDs
          $currentSupervisorIds = array_map(function($sup) {
              return $sup['id'];
          }, $service['supervisors'] ?? []);
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
              <div class="col-md-12 mb-3">
                <label class="form-label"><i class="fas fa-users me-2"></i>Assign Supervisors</label>
                <select class="form-select" name="supervisors[]" multiple size="6">
                  <?php if (empty($allSupervisors)): ?>
                    <option disabled>No supervisors available</option>
                  <?php else: ?>
                    <?php foreach ($allSupervisors as $supervisor): ?>
                      <option value="<?= $supervisor['id'] ?>" 
                              <?= in_array($supervisor['id'], $currentSupervisorIds) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($supervisor['full_name']) ?> - <?= htmlspecialchars($supervisor['email']) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <small class="text-muted d-block mt-1">
                  <i class="fas fa-info-circle me-1"></i>Hold Ctrl (Windows) or Cmd (Mac) to select multiple supervisors
                </small>
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
        <tr><td colspan="10" class="text-center py-4">
          <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
          <p class="text-muted">No Services found.</p>
        </td></tr>
      <?php endif; ?>
            
      </tbody>
    </table>
  </div>

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
    <div class="col-md-12 mb-3">
      <label class="form-label"><i class="fas fa-users me-2"></i>Assign Supervisors</label>
      <select class="form-select" name="supervisors[]" multiple size="6">
        <?php if (empty($allSupervisors)): ?>
          <option disabled>No supervisors available</option>
        <?php else: ?>
          <?php foreach ($allSupervisors as $supervisor): ?>
            <option value="<?= $supervisor['id'] ?>">
              <?= htmlspecialchars($supervisor['full_name']) ?> - <?= htmlspecialchars($supervisor['email']) ?>
            </option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <small class="text-muted d-block mt-1">
        <i class="fas fa-info-circle me-1"></i>Hold Ctrl (Windows) or Cmd (Mac) to select multiple supervisors
      </small>
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