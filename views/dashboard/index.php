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
            </tr> <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Charts Section (Bootstrap Grid) -->
<div class="row mb-5">
  <!-- Users by Role -->
  <div class="col-md-6 mb-4">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white">
        <h4 class="mb-0">Users by Role</h4>
      </div>
      <div class="card-body mx-auto" style="width: 300px;">
        <canvas id="usersByRoleChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Services Per Month -->
  <div class="col-md-6 mb-4">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white">
        <h4 class="mb-0">Services Created per Month</h4>
      </div>
      <div class="card-body" style="height: 315px;">
        <canvas id="servicesPerMonthChart"></canvas>
      </div>
    </div>
  </div>
</div>


<!-- Activities Per Day -->
<div class="card shadow-sm border-0 mb-5">
  <div class="card-header bg-white">
    <h4 class="mb-0">Recent Activities (Last 7 Days)</h4>
  </div>
  <div class="card-body">
    <canvas id="activitiesPerDayChart" height="70"></canvas>
  </div>
</div>





<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>

<script>
  const chartData = <?= json_encode($chartData) ?>;
  const servicesChart = <?= json_encode($servicesChart) ?>;
  const activityChart = <?= json_encode($activityChart) ?>;

  // Create gradient helper
  function createGradient(ctx, colorStart, colorEnd) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
  }

  // === Doughnut Chart: Users by Role ===
  const ctx1 = document.getElementById('usersByRoleChart').getContext('2d');
  new Chart(ctx1, {
    type: 'doughnut',
    data: {
      labels: chartData.labels,
      datasets: [{
        data: chartData.values,
        backgroundColor: [
          '#4e79a7',
          '#f28e2b',
          '#e15759',
          '#76b7b2',
          '#59a14f',
          '#edc948'
        ],
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 8
      }]
    },
    options: {
      responsive: true,
      cutout: '70%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#333',
            font: { size: 14 }
          }
        },
        tooltip: {
          backgroundColor: '#fff',
          titleColor: '#000',
          bodyColor: '#000',
          borderColor: '#ddd',
          borderWidth: 1
        }
      }
    }
  });

  // === Line Chart: Services per Month ===
  const ctx2 = document.getElementById('servicesPerMonthChart').getContext('2d');
  const gradientBlue = createGradient(ctx2, 'rgba(13,110,253,0.6)', 'rgba(13,110,253,0.1)');
  new Chart(ctx2, {
    type: 'line',
    data: {
      labels: servicesChart.labels,
      datasets: [{
        label: 'Services Created',
        data: servicesChart.values,
        borderColor: '#0d6efd',
        backgroundColor: gradientBlue,
        fill: true,
        tension: 0.4,
        borderWidth: 3,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#0d6efd',
        pointRadius: 5
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: { mode: 'index', intersect: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0,0,0,0.05)' },
          ticks: { color: '#666' }
        },
        x: {
          grid: { display: false },
          ticks: { color: '#666' }
        }
      }
    }
  });

  // === Bar Chart: Activities per Day ===
  const ctx3 = document.getElementById('activitiesPerDayChart').getContext('2d');
  const gradientGreen = createGradient(ctx3, 'rgba(40,167,69,0.7)', 'rgba(40,167,69,0.2)');
  new Chart(ctx3, {
    type: 'bar',
    data: {
      labels: activityChart.labels,
      datasets: [{
        label: 'Activities',
        data: activityChart.values,
        backgroundColor: gradientGreen,
        borderRadius: 10,
        barThickness: 35,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: { backgroundColor: '#fff', titleColor: '#000', bodyColor: '#000' }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0,0,0,0.05)' },
          ticks: { color: '#666' }
        },
        x: {
          grid: { display: false },
          ticks: { color: '#666' }
        }
      }
    }
  });
</script>

