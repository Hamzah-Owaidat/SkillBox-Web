<?php 
  $baseUrl = '/skillbox/public';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $title ?? 'SkillBox' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?= $baseUrl ?>/styles.css" rel="stylesheet"/>
</head>
<body>

    <?php include __DIR__ . '/../partials/toast.php'; ?>

    <!-- Navbar -->
    <?php require __DIR__ . '/../partials/navbar.php'; ?>

    <!-- Page Content -->
    <?= $content ?>

    <!-- Footer -->
    <?php require __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    <script>
      window.NOTIFICATION_USER_ID = <?= json_encode($userId) ?>;
      window.BASE_URL = <?= json_encode($baseUrl) ?>;
      window.PUSHER_KEY = <?= json_encode($_ENV['PUSHER_APP_KEY']) ?>;
      window.PUSHER_CLUSTER = <?= json_encode($_ENV['PUSHER_APP_CLUSTER']) ?>;
  </script>
  <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
  <script src="<?= $baseUrl ?>/js/notifications.js"></script>

</body>
</html>
