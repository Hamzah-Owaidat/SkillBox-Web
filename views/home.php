<?php ob_start(); ?>
<?php $baseUrl = '/skillbox/public'; ?>

<!-- Hero Section -->
<section class="hero-section" style="background: url('<?= $baseUrl ?>/images/background.jpg') center/cover no-repeat; height:100vh; position:relative; display:flex; align-items:center; justify-content:center; text-align:center;">
  <div class="container" style="position:relative; z-index:2; color:white;">
    <h1 class="display-4 fw-bold">Your Project Is Ready… Let People See It!</h1>
    <p>If people don’t hear about it, it won’t succeed. That’s where SkillBox helps!
      <br> A platform that gives you simple and fast digital marketing services.</p>
    <a href="<?= $baseUrl ?>/services" class="btn btn-warning btn-lg mt-3">Explore Services</a>
  </div>
</section>

<!-- Services Section -->
<section class="py-5">
  <div class="container text-center">
    <h2 class="mb-4 section-title">Top Mini Services</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
          <img src="<?= $baseUrl ?>/images/design.jpg" class="card-img-top" alt="Design" loading="lazy">
          <div class="card-body">
            <h5 class="card-title">Social Media Design</h5>
            <p class="card-text">Get custom post designs in 24h. Perfect for Instagram & Facebook.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
          <img src="<?= $baseUrl ?>/images/content.jpg" class="card-img-top" alt="Content Writing" loading="lazy">
          <div class="card-body">
            <h5 class="card-title">Copywriting</h5>
            <p class="card-text">Short texts that convert. Ad captions, product descriptions & more.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
          <img src="<?= $baseUrl ?>/images/Ads.jpg" class="card-img-top" alt="Marketing" loading="lazy">
          <div class="card-body">
            <h5 class="card-title">Mini Ads Setup</h5>
            <p class="card-text">We set up a simple, effective Facebook campaign for you – fast!</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Why Us Section -->
<section class="py-5 bg-light">
  <div class="container text-center">
    <h2 class="mb-4 section-title">Why Skillbox?</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <h5 class="fw-bold text-teal">⚡ Fast Delivery</h5>
        <p>All services delivered within 24–48 hours. No delays.</p>
      </div>
      <div class="col-md-4">
        <h5 class="fw-bold text-teal">💡 Clear Pricing</h5>
        <p>No surprises. You always know what you're paying for.</p>
      </div>
      <div class="col-md-4">
        <h5 class="fw-bold text-teal">🛡️ Quality Control</h5>
        <p>Services reviewed and approved before publishing.</p>
      </div>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
$title = "SkillBox - Home";
require __DIR__ . '/layouts/main.php';
