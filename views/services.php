<?php ob_start(); ?>
<?php $baseUrl = '/skillbox/public'; ?>

<section class="services-section">
  <div class="container">
    <h2 class="section-title">What We Offer</h2>
    <p class="section-subtitle">Transform your business with our premium creative services</p>
    <div class="row g-4">

      <?php 
      $services = [
        ['icon' => '🖼️', 'title' => 'Post Design', 'text' => 'Posts that make your brand unforgettable. Stand out on every platform with stunning visuals.', 'color' => 'bg-primary-dark'],
        ['icon' => '📲', 'title' => 'Simple Logo Design', 'text' => 'Your brand deserves a face that speaks. Create a memorable identity that resonates.', 'color' => 'bg-teal'],
        ['icon' => '🎬', 'title' => 'Video Editing', 'text' => 'Editing that makes every frame count. Transform raw footage into captivating stories.', 'color' => 'bg-cyan'],
        ['icon' => '📢', 'title' => 'Sponsored Ad Setup', 'text' => 'Ready for more impact? Let\'s boost your presence together with targeted campaigns.', 'color' => 'bg-yellow'],
        ['icon' => '🖌️', 'title' => 'Website Design', 'text' => 'Launch your dream website now. Professional, responsive, and conversion-focused.', 'color' => 'bg-mint'],
        ['icon' => '✏️', 'title' => 'Business Card', 'text' => 'Business cards that speak for you. Make lasting first impressions with elegant designs.', 'color' => 'bg-purple'],
      ];

      foreach($services as $index => $s): ?>
      <div class="col-md-4">
        <div class="service-card h-100" style="animation-delay: <?= ($index+1)*0.1 ?>s;">
          <div class="icon-wrapper">
            <div class="icon-circle <?= $s['color'] ?>"><?= $s['icon'] ?></div>
          </div>
          <h5 class="card-title"><?= $s['title'] ?></h5>
          <p class="card-text"><?= $s['text'] ?></p>
          <a href="<?= $baseUrl ?>/interstedclients.html" class="btn btn-interested">Get Started</a>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </div>
</section>

<!-- <style>
  .services-section {
    padding: 100px 0;
    position: relative;
    background: linear-gradient(135deg, #F4F7F8 0%, #E8EFF1 100%);
  }
  .section-title {
    font-weight: 700;
    text-align: center;
    margin-bottom: 20px;
    font-size: 3rem;
    background: linear-gradient(135deg, #1F3440 0%, #2C6566 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: fadeInDown 0.8s ease-out;
  }
  .section-subtitle {
    text-align: center;
    color: #5c6c75;
    font-size: 1.1rem;
    margin-bottom: 60px;
    animation: fadeInUp 0.8s ease-out;
  }
  @keyframes fadeInDown { from {opacity:0; transform:translateY(-30px);} to {opacity:1; transform:translateY(0);} }
  @keyframes fadeInUp { from {opacity:0; transform:translateY(30px);} to {opacity:1; transform:translateY(0);} }

  .service-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    padding: 40px 30px;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out backwards;
  }
  .service-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 4px;
    background: linear-gradient(90deg, #25BDB0, #56D7B4);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
  }
  .service-card:hover::before { transform: scaleX(1); }
  .service-card:hover { transform: translateY(-12px) scale(1.02); box-shadow: 0 20px 50px rgba(0,0,0,0.12); }

  .icon-wrapper { margin-bottom:25px; }
  .icon-circle {
    width: 80px; height: 80px; border-radius:50%; display:flex; align-items:center; justify-content:center;
    font-size:36px; color:white; transition: all 0.4s ease; box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
  .service-card:hover .icon-circle { transform: rotate(360deg) scale(1.1); box-shadow:0 12px 30px rgba(0,0,0,0.15); }

  .bg-primary-dark { background: linear-gradient(135deg, #1F3440 0%, #2C6566 100%); }
  .bg-teal { background: linear-gradient(135deg, #2C6566 0%, #25BDB0 100%); }
  .bg-cyan { background: linear-gradient(135deg, #25BDB0 0%, #56D7B4 100%); }
  .bg-yellow { background: linear-gradient(135deg, #EDBF43 0%, #F5D576 100%); }
  .bg-mint { background: linear-gradient(135deg, #56D7B4 0%, #25BDB0 100%); }
  .bg-purple { background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); }

  .card-title { font-size:22px; font-weight:700; margin-bottom:12px; color:#1F3440; transition:color 0.3s ease; }
  .service-card:hover .card-title { color:#25BDB0; }
  .card-text { font-size:15px; color:#5c6c75; margin-bottom:25px; line-height:1.6; }

  .btn-interested {
    background: linear-gradient(135deg, #25BDB0 0%, #56D7B4 100%);
    color:#fff; border:none; padding:12px 28px; border-radius:12px; font-weight:600;
    text-decoration:none; display:inline-block; transition:all 0.3s ease; box-shadow:0 4px 15px rgba(37,189,176,0.3);
  }
  .btn-interested:hover { transform: translateY(-2px); box-shadow:0 6px 25px rgba(37,189,176,0.4); }
  @media (max-width:768px){ .section-title{ font-size:2.2rem; } .services-section{ padding:60px 0; } }
</style> -->

<?php
$content = ob_get_clean();
$title = "SkillBox - Services";
require __DIR__ . '/layouts/main.php';
