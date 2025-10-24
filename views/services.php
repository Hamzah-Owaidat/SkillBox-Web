<?php ob_start(); ?>
<?php $baseUrl = '/skillbox/public'; ?>

<section class="services-section">
  <div class="container">
    <h2 class="section-title">What We Offer</h2>
    <p class="section-subtitle">Transform your business with our premium creative services</p>
    <div class="row g-4">

      <?php foreach($services as $service): ?>
        <div class="col-md-4">
          <div class="service-card h-100" style="animation-delay: <?= ($index+1)*0.1 ?>s;">
            <div class="icon-wrapper">
              <div class="icon-circle bg-cyan"><?= $service['image'] ?></div>
            </div>
            <h5 class="card-title"><?= $service['title'] ?></h5>
            <p class="card-text"><?= $service['description'] ?></p>
            <a href="<?= $baseUrl ?>/services/<?= $service['id'] ?>" class="btn btn-interested">Get Started</a>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
$title = "SkillBox - Services";
require __DIR__ . '/layouts/main.php';
