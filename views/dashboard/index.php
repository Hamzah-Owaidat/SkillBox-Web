<?php 
  $title = "Dashboard Overview"; 
  ob_start(); 
?> 

<h2 class="mb-4">Dashboard Overview</h2> 

<!-- Statistics -->
<div class="row g-4 mb-5">
  <!-- Total Services -->
  <div class="col-md-3">
    <div class="card shadow-sm border-0 text-center py-4 hover-scale">
      <div class="text-primary mb-2" style="font-size: 2rem;"> <i class="fas fa-box"></i> </div>
      <h6 class="text-muted mb-1">Total Services</h6>
      <h3 class="fw-bold"><?= $totalServices ?></h3>
    </div>
  </div> <!-- Total Users -->
  <div class="col-md-3">
    <div class="card shadow-sm border-0 text-center py-4 hover-scale">
      <div class="text-success mb-2" style="font-size: 2rem;"> <i class="fas fa-users"></i> </div>
      <h6 class="text-muted mb-1">Total Users</h6>
      <h3 class="fw-bold"><?= $totalUsers ?></h3>
    </div>
  </div> <!-- Total Workers -->
  <div class="col-md-3">
    <div class="card shadow-sm border-0 text-center py-4 hover-scale">
      <div class="text-warning mb-2" style="font-size: 2rem;"> <i class="fas fa-briefcase"></i> </div>
      <h6 class="text-muted mb-1">Workers</h6>
      <h3 class="fw-bold"><?= $totalWorkers ?></h3>
    </div>
  </div> <!-- Total Admins -->
  <div class="col-md-3">
    <div class="card shadow-sm border-0 text-center py-4 hover-scale">
      <div class="text-danger mb-2" style="font-size: 2rem;"> <i class="fas fa-user-shield"></i> </div>
      <h6 class="text-muted mb-1">Admins</h6>
      <h3 class="fw-bold"><?= $totalAdmins ?></h3>
    </div>
  </div>
</div> 

<!-- Recent Activities -->
<div class="card shadow-sm border-0 mb-5">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Recent Activities</h4> <a href="<?= $this->baseUrl ?>/dashboard/activities/export"
      class="btn btn-sm btn-outline-primary"> Export All </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Message</th>
            <th>User</th>
            <th>Action</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody> <?php if (!empty($recentActivities)): ?> <?php foreach ($recentActivities as $index => $activity): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($activity['message']) ?></td>
            <td><strong><?= htmlspecialchars($activity['full_name'] ?? 'Unknown') ?></strong></td>
            <td><?= htmlspecialchars($activity['action']) ?></td>
            <td><?= date('H:i d/m/Y', strtotime($activity['created_at'])) ?></td>
          </tr> <?php endforeach; ?> <?php else: ?> <tr>
            <td colspan="5" class="text-center text-muted py-3">No recent activities found.</td>
          </tr> <?php endif; ?> </tbody>
      </table>
    </div>
  </div>
</div> 

<?php 
  $content = ob_get_clean(); 
  include __DIR__ . '/../layouts/dashboard.php'; 
?>