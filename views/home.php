<?php 
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = '/skillbox/public';
$isLoggedIn = isset($_SESSION['user_id']);
?>

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

<!-- AI Chatbot Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-robot me-2"></i>
                            AI Assistant - Find Your Perfect Service
                        </h5>
                        <small>Describe what you need, and we'll match you with the best service and worker!</small>
                    </div>
                    <div class="card-body">
                        <?php if (!$isLoggedIn): ?>
                            <!-- Guest User - Login Required -->
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                                </div>
                                <h5 class="text-muted mb-3">Login Required</h5>
                                <p class="text-muted mb-4">Please log in to use the AI Assistant and get personalized service recommendations.</p>
                                <a href="<?= $baseUrl ?>/login" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Login to Continue
                                </a>
                                <p class="mt-3 mb-0">
                                    <small class="text-muted">Don't have an account? <a href="<?= $baseUrl ?>/register" class="text-primary">Sign up here</a></small>
                                </p>
                            </div>
                        <?php else: ?>
                            <!-- Logged In User - Active Chatbot -->
                            <div class="mb-3">
                                <textarea id="aiQuestion" 
                                          class="form-control" 
                                          rows="3"
                                          placeholder="Example: I need someone to design social media posts for my business..."></textarea>
                            </div>
                            <button id="askAiBtn" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i>
                                <span id="btnText">Ask AI</span>
                            </button>
                            
                            <div id="aiAnswer" class="mt-4 d-none">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-lightbulb me-2"></i>
                                        AI Recommendation
                                    </h6>
                                    <div id="aiReply" class="mb-0" style="white-space: pre-line;"></div>
                                </div>
                                
                                <div id="serviceInfo" class="mt-3"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const isLoggedIn = <?= json_encode($isLoggedIn) ?>;

<?php if ($isLoggedIn): ?>
// Only initialize chatbot functionality if user is logged in
document.getElementById('askAiBtn').addEventListener('click', async () => {
    const input = document.getElementById('aiQuestion');
    const out = document.getElementById('aiAnswer');
    const replyDiv = document.getElementById('aiReply');
    const serviceInfo = document.getElementById('serviceInfo');
    const btn = document.getElementById('askAiBtn');
    const btnText = document.getElementById('btnText');
    const text = input.value.trim();
    
    if (!text) {
        alert('Please describe what you need!');
        return;
    }

    // Show loading state
    btn.disabled = true;
    btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Thinking...';
    out.classList.remove('d-none');
    replyDiv.textContent = '🤔 Analyzing your request...';
    serviceInfo.innerHTML = '';

    try {
        const res = await fetch(`${baseUrl}/api/chatbot/query`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        });
        
        const data = await res.json();

        if (!data.success) {
            replyDiv.textContent = '❌ ' + (data.error || 'Something went wrong. Please try again.');
            btn.disabled = false;
            btnText.textContent = 'Ask AI';
            return;
        }

        // Display reply
        replyDiv.textContent = data.reply;

        // Show service and worker info if available
        if (data.service && data.worker) {
            let infoHtml = '<div class="card border-success">';
            infoHtml += '<div class="card-body">';
            infoHtml += '<h6 class="text-success"><i class="fas fa-check-circle me-2"></i>Recommended Match</h6>';
            infoHtml += `<p class="mb-1"><strong>Service:</strong> <a href="${baseUrl}/services/${data.service.id}">${data.service.title}</a></p>`;
            infoHtml += `<p class="mb-1"><strong>Worker:</strong> ${data.worker.full_name}</p>`;
            if (data.worker.email) {
                infoHtml += `<p class="mb-1"><strong>Email:</strong> <a href="mailto:${data.worker.email}">${data.worker.email}</a></p>`;
            }
            if (data.worker.phone) {
                infoHtml += `<p class="mb-0"><strong>Phone:</strong> <a href="tel:${data.worker.phone}">${data.worker.phone}</a></p>`;
            }
            infoHtml += '</div></div>';
            serviceInfo.innerHTML = infoHtml;
        }

    } catch (error) {
        console.error('Error:', error);
        replyDiv.textContent = '❌ Network error. Please check your connection and try again.';
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Ask AI';
    }
});

// Allow Enter key to submit (Shift+Enter for new line)
document.getElementById('aiQuestion').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('askAiBtn').click();
    }
});
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
$title = "SkillBox - Home";
require __DIR__ . '/layouts/main.php';
