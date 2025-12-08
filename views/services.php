<?php 
ob_start(); 
$baseUrl = '/skillbox/public';

// Check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
?>

<section class="services-section">
  <div class="container">
    <h2 class="section-title text-center">What We Offer</h2>
    <p class="section-subtitle">Transform your business with our premium creative services</p>
    <div class="row g-4">

      <?php 
      if (empty($services)): ?>
        <div class="col-12 text-center py-5">
          <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
          <p class="text-muted">No services available at the moment. Please check back later.</p>
        </div>
      <?php else:
      $index = 0;
      foreach($services as $service): 
        // Truncate description to 150 characters with ellipsis
        $description = $service['description'] ?? '';
        $truncatedDesc = mb_strlen($description) > 150 
          ? mb_substr($description, 0, 150) . '...' 
          : $description;
        
        // Determine Get Started link - redirect to login if guest
        $getStartedLink = $isLoggedIn 
          ? $baseUrl . '/services/' . $service['id']
          : $baseUrl . '/login?redirect=' . urlencode($baseUrl . '/services/' . $service['id']);
      ?>
        <div class="col-md-4 col-lg-4 mb-4">
          <div class="service-card h-100" style="animation-delay: <?= ($index++)*0.1 ?>s;">
            <div class="icon-wrapper">
              <div class="icon-circle bg-cyan">
                <?php if (!empty($service['image'])): ?>
                  <?php if (filter_var($service['image'], FILTER_VALIDATE_URL)): ?>
                    <!-- If it's a URL, show as image -->
                    <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                  <?php else: ?>
                    <!-- If it's an emoji, display it directly -->
                    <span style="font-size: 2.5rem; line-height: 1;"><?= htmlspecialchars($service['image']) ?></span>
                  <?php endif; ?>
                <?php else: ?>
                  <!-- Fallback to first letter if no image/emoji -->
                  <?= strtoupper(substr($service['title'], 0, 1)) ?>
                <?php endif; ?>
              </div>
            </div>
            <h5 class="card-title"><?= htmlspecialchars($service['title']) ?></h5>
            <p class="card-text"><?= nl2br(htmlspecialchars($truncatedDesc)) ?></p>
            <a href="<?= $getStartedLink ?>" class="btn btn-interested">
              <i class="fas fa-arrow-right me-2"></i>
              Get Started
            </a>
          </div>
        </div>
      <?php 
      endforeach; 
      endif; ?>

    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
$title = "SkillBox - Services";
require __DIR__ . '/layouts/main.php';
