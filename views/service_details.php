<?php 
ob_start(); 
$baseUrl = '/skillbox/public';

// Check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);

// Get service emoji/icon
$serviceIcon = $service['image'] ?? '';
$isIconUrl = !empty($serviceIcon) && filter_var($serviceIcon, FILTER_VALIDATE_URL);
?>

<!-- Service Hero Section -->
<section class="service-hero-section">
  <div class="container">
    <div class="service-hero-content">
      <a href="<?= $baseUrl ?>/services" class="back-btn">
        <i class="fas fa-arrow-left me-2"></i>
        Back to Services
      </a>
      
      <div class="service-header">
        <div class="service-icon-large">
          <?php if (!empty($serviceIcon)): ?>
            <?php if ($isIconUrl): ?>
              <img src="<?= htmlspecialchars($serviceIcon) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="service-icon-img">
            <?php else: ?>
              <span class="service-emoji"><?= htmlspecialchars($serviceIcon) ?></span>
            <?php endif; ?>
          <?php else: ?>
            <span class="service-emoji"><?= strtoupper(substr($service['title'], 0, 1)) ?></span>
          <?php endif; ?>
        </div>
        <div class="service-header-text">
          <h1 class="service-title-main"><?= htmlspecialchars($service['title']) ?></h1>
          <p class="service-description-main"><?= nl2br(htmlspecialchars($service['description'] ?? '')) ?></p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Workers Section -->
<section class="workers-section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title-modern">
        <i class="fas fa-users me-3"></i>
        Our Expert Workers
      </h2>
      <p class="section-subtitle-modern">Connect with skilled professionals ready to help you</p>
    </div>

    <?php if (!empty($workers)): ?>
      <div class="row g-4">
        <?php foreach ($workers as $worker): ?>
          <div class="col-md-6 col-lg-4">
            <div class="worker-card">
              <div class="worker-card-header">
                <div class="worker-avatar">
                  <?= strtoupper(substr($worker['full_name'], 0, 1)) ?>
                </div>
                <div class="worker-badge">
                  <i class="fas fa-check-circle"></i>
                  Verified
                </div>
              </div>
              
              <div class="worker-card-body">
                <h5 class="worker-name"><?= htmlspecialchars($worker['full_name']) ?></h5>
                
                <div class="worker-info">
                  <?php if (!empty($worker['email'])): ?>
                    <div class="info-item">
                      <i class="fas fa-envelope"></i>
                      <a href="mailto:<?= htmlspecialchars($worker['email']) ?>"><?= htmlspecialchars($worker['email']) ?></a>
                    </div>
                  <?php endif; ?>
                  
                  <?php if (!empty($worker['phone'])): ?>
                    <div class="info-item">
                      <i class="fas fa-phone"></i>
                      <a href="tel:<?= htmlspecialchars($worker['phone']) ?>"><?= htmlspecialchars($worker['phone']) ?></a>
                    </div>
                  <?php endif; ?>
                  
                  <?php if (!empty($worker['linkedin'])): ?>
                    <div class="info-item">
                      <i class="fab fa-linkedin"></i>
                      <a href="<?= htmlspecialchars($worker['linkedin']) ?>" target="_blank" rel="noopener">LinkedIn Profile</a>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              
              <div class="worker-card-footer">
                <?php if(!empty($worker['cv'])): ?>
                  <?php 
                  // CV path format: 'public/uploads/portfolios/{id}/filename.pdf'
                  $cvPath = $worker['cv'];
                  // Remove 'public/uploads/' prefix for API
                  if (strpos($cvPath, 'public/uploads/') === 0) {
                    $cvPath = substr($cvPath, 16); // Remove 'public/uploads/' prefix
                  } elseif (strpos($cvPath, 'uploads/') === 0) {
                    $cvPath = substr($cvPath, 8); // Remove 'uploads/' prefix
                  }
                  ?>
                  <a href="<?= $baseUrl ?>/api/cv/<?= urlencode($cvPath) ?>" target="_blank" class="btn-worker btn-worker-cv">
                    <i class="fas fa-file-pdf me-2"></i>
                    View CV
                  </a>
                <?php endif; ?>
                
                <a href="<?= $baseUrl ?>/chat/start/<?= $worker['id'] ?>" class="btn-worker btn-worker-chat">
                  <i class="fas fa-comments me-2"></i>
                  Start Chat
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-user-slash"></i>
        </div>
        <h3>No Workers Available</h3>
        <p>We're currently building our team for this service. Check back soon!</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php
$content = ob_get_clean();
$title = "SkillBox - " . $service['title'];
require __DIR__ . '/layouts/main.php';
?>
