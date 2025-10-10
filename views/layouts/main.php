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
</body>
</html>
