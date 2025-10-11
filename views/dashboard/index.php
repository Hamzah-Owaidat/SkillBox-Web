<?php
$title = "Dashboard Overview";

ob_start(); // Start capturing content
?>

<h2 class="mb-4">Dashboard Overview</h2>

<!-- Statistics -->
<div class="row g-4 mb-5">
  <div class="col-md-3">
    <div class="card card-stat">
      <i class="fas fa-box"></i>
      <h5>Total Services</h5>
      <h4>32</h4>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat">
      <i class="fas fa-users"></i>
      <h5>Total Users</h5>
      <h4>125</h4>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat">
      <i class="fas fa-clipboard-list"></i>
      <h5>Projects</h5>
      <h4>18</h4>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat">
      <i class="fas fa-user-shield"></i>
      <h5>Admins</h5>
      <h4>3</h4>
    </div>
  </div>
</div>

<h4 class="section-title">Edit CV</h4>
<div class="card p-4 mb-5">
  <form>
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-control" placeholder="Enter your full name">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" placeholder="example@mail.com">
      </div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
  </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
