<?php 
$baseUrl = '/skillbox/public';
$title = "Unauthorized Access";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | SkillBox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link href="<?= $baseUrl ?>/styles.css" rel="stylesheet"/>
    <style>
        .unauthorized-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .unauthorized-card {
            max-width: 500px;
            text-align: center;
            padding: 3rem 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border-radius: 15px;
            background: white;
        }
        .unauthorized-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
        .unauthorized-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1F3440;
            margin-bottom: 1rem;
        }
        .unauthorized-message {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="unauthorized-container">
        <div class="unauthorized-card">
            <div class="unauthorized-icon">
                <i class="fas fa-ban"></i>
            </div>
            <h1 class="unauthorized-title">Access Denied</h1>
            <p class="unauthorized-message">
                You don't have permission to access this page. This area is restricted to administrators only.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="<?= $baseUrl ?>/" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Go to Home
                </a>
                <a href="<?= $baseUrl ?>/profile" class="btn btn-outline-secondary">
                    <i class="fas fa-user me-2"></i>My Profile
                </a>
            </div>
        </div>
    </div>
</body>
</html>

