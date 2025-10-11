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
<body>

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
</body>
</html>
