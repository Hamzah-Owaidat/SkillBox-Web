<?php ob_start(); ?>
<?php $baseUrl = '/skillbox/public'; ?>

<section class="services-section">
  <div class="container">
    <h2 class="section-title"><?= $service['title'] ?></h2>
    <p class="section-subtitle"><?= $service['description'] ?></p>

    <h3 class="mt-5">Our Expert Workers</h3>
    <?php if (!empty($workers)): ?>
      <div class="row g-4">
        <?php foreach ($workers as $worker): ?>
          <div class="col-md-4">
            <div class="service-card h-100">
              <div class="icon-wrapper">
                <div class="icon-circle bg-cyan">
                  <?= strtoupper(substr($worker['full_name'], 0, 1)) ?>
                </div>
              </div>
              <h5 class="card-title"><?= $worker['full_name'] ?></h5>
              <p class="card-text">
                Email: <?= $worker['email'] ?><br>
                Phone: <?= $worker['phone'] ?? 'N/A' ?><br>
                LinkedIn: <?= $worker['linkedin'] ?? 'N/A' ?><br>
                <?php if(!empty($worker['cv'])): ?>
                    <a href="<?= $baseUrl . '/../' . $worker['cv'] ?>" target="_blank" class="btn btn-sm btn-primary mt-2">View CV</a>
                <?php else: ?>
                    CV not uploaded
                <?php endif; ?>
              </p>
              <!-- Chat Button -->
              <a href="<?= $baseUrl ?>/chat/start/<?= $worker['id'] ?>" class="btn btn-sm btn-success mt-2">
                  Chat
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No workers assigned to this service yet.</p>
    <?php endif; ?>

    <a href="<?= $baseUrl ?>/services" class="btn btn-secondary mt-4">Back to Services</a>
  </div>
</section>

<?php
$content = ob_get_clean();
$title = "SkillBox - " . $service['title'];
require __DIR__ . '/layouts/main.php';
