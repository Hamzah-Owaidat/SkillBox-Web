<?php 
  $baseUrl = '/skillbox/public';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $title ?? 'Dashboard | SkillBox' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
  <link href="/skillbox/public/styles.css" rel="stylesheet"/>
</head>
<body class="overflow-hidden">

  <?php include __DIR__ . '/../partials/toast.php'; ?>

  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div class="dashboard-layout">
    <aside class="sidebar">
      <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    </aside>

    <main class="dashboard-content">
      <?= $content ?? '' ?>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
  <script>
    window.NOTIFICATION_USER_ID = <?= json_encode($userId) ?>;
    window.BASE_URL = <?= json_encode($baseUrl) ?>;
    window.PUSHER_KEY = <?= json_encode($_ENV['PUSHER_APP_KEY']) ?>;
    window.PUSHER_CLUSTER = <?= json_encode($_ENV['PUSHER_APP_CLUSTER']) ?>;

    console.log("Pusher Key:", window.PUSHER_KEY);
    console.log("Pusher Cluster:", window.PUSHER_CLUSTER);
  </script>
  <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
  <script src="<?= $baseUrl ?>/js/notifications.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</body>
</html>
