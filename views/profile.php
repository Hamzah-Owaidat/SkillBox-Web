<?php ob_start(); ?>
<?php $baseUrl = '/skillbox/public'; ?>

<section class="profile-section py-5" style="background: #f4f7f9;">
  <div class="container">
    <div class="row">

      <!-- Profile Info -->
      <div class="col-lg-5 mb-4">
        <div class="card p-4 shadow rounded-4">
          <h3 class="mb-4 fw-bold">My Profile</h3>
          <form action="<?= $baseUrl ?>/user/update" method="POST">
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <hr>
            <div class="mb-3">
              <label class="form-label">Old Password</label>
              <input type="password" name="old_password" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Profile</button>
          </form>
        </div>
      </div>

      <!-- Portfolio Status -->
      <div class="col-lg-7">
        <div class="card p-4 shadow rounded-4">
          <h3 class="mb-4 fw-bold">My Portfolios</h3>
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($portfolios as $p): ?>
                <tr>
                  <td><?= htmlspecialchars($p['full_name']) ?></td>
                  <td><?= htmlspecialchars($p['email']) ?></td>
                  <td><?= htmlspecialchars($p['requested_role']) ?></td>
                  <td>
                    <span class="badge 
                      <?= $p['status'] === 'pending' ? 'bg-warning' : ($p['status'] === 'accepted' ? 'bg-success' : 'bg-danger') ?>">
                      <?= ucfirst($p['status']) ?>
                    </span>
                  </td>
                  <td>
                    <?php if($p['status'] === 'pending'): ?>
                        <a href="<?= $baseUrl ?>/portfolio/edit/<?= $p['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <form action="<?= $baseUrl ?>/portfolio/delete/<?= $p['id'] ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this portfolio?');">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    <?php else: ?>
                      <span class="text-muted">No actions</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
$title = "My Profile";
require __DIR__ . '/layouts/main.php';
