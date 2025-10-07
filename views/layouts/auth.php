<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'SkillBox' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <?php if (!empty($extraCss)) echo $extraCss; ?>
</head>
<body style="min-height:100vh; background: <?= $bgColor ?? 'linear-gradient(135deg, #1F3440 0%, #56D7B4 100%)' ?>;">
    <?= $content ?? '' ?>

    <!-- Toast Notifications -->
    <?php include __DIR__ . '/../partials/toast.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($extraJs)) echo $extraJs; ?>
</body>
</html>
