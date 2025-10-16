<?php
/**
 * Debug Environment Variables
 * Access: http://localhost/skillbox/public/debug_env.php
 * DELETE THIS FILE AFTER DEBUGGING!
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Try to load .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    $envLoaded = true;
} catch (Exception $e) {
    $envLoaded = false;
    $envError = $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Environment Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        table { background: white; border-collapse: collapse; width: 100%; }
        td, th { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #333; color: white; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Environment Variables Debug</h1>

    <h2>1. Dotenv Loading</h2>
    <?php if ($envLoaded): ?>
        <p class="success">✅ Dotenv loaded successfully</p>
    <?php else: ?>
        <p class="error">❌ Failed to load Dotenv: <?= $envError ?></p>
    <?php endif; ?>

    <h2>2. Pusher Configuration Check</h2>
    <table>
        <tr>
            <th>Variable</th>
            <th>$_ENV</th>
            <th>getenv()</th>
            <th>Status</th>
        </tr>
        <?php
        $vars = ['PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_CLUSTER'];
        foreach ($vars as $var):
            $envValue = $_ENV[$var] ?? null;
            $getenvValue = getenv($var);
            $hasValue = !empty($envValue) || !empty($getenvValue);
        ?>
        <tr>
            <td><strong><?= $var ?></strong></td>
            <td><?= $envValue ? '✅ ' . substr($envValue, 0, 20) . '...' : '❌ Not set' ?></td>
            <td><?= $getenvValue ? '✅ ' . substr($getenvValue, 0, 20) . '...' : '❌ Not set' ?></td>
            <td class="<?= $hasValue ? 'success' : 'error' ?>">
                <?= $hasValue ? '✅ OK' : '❌ Missing' ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>3. Config File Check</h2>
    <?php
    $configPath = __DIR__ . '/../config/pusher.php';
    if (file_exists($configPath)):
        $config = require $configPath;
    ?>
        <p class="success">✅ Config file exists</p>
        <table>
            <tr>
                <th>Key</th>
                <th>Value</th>
            </tr>
            <?php foreach (['app_id', 'key', 'secret', 'cluster'] as $key): ?>
            <tr>
                <td><?= $key ?></td>
                <td><?= !empty($config[$key]) ? '✅ Set (' . substr($config[$key], 0, 10) . '...)' : '❌ Empty' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p class="error">❌ Config file not found at: <?= $configPath ?></p>
    <?php endif; ?>

    <h2>4. .env File Check</h2>
    <?php
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)):
        $envContent = file_get_contents($envPath);
        $hasPusherVars = strpos($envContent, 'PUSHER_APP_KEY') !== false;
    ?>
        <p class="success">✅ .env file exists at: <?= $envPath ?></p>
        <p class="<?= $hasPusherVars ? 'success' : 'warning' ?>">
            <?= $hasPusherVars ? '✅ Contains Pusher variables' : '⚠️ No Pusher variables found' ?>
        </p>
        
        <h3>.env Contents (Pusher section):</h3>
        <pre><?php
            $lines = explode("\n", $envContent);
            $inPusherSection = false;
            foreach ($lines as $line) {
                if (stripos($line, 'PUSHER') !== false) {
                    $inPusherSection = true;
                }
                if ($inPusherSection && trim($line) !== '') {
                    echo htmlspecialchars($line) . "\n";
                    if (empty(trim($line)) || stripos($line, 'PUSHER') === false) {
                        if ($inPusherSection && !stripos($line, '=')) {
                            $inPusherSection = false;
                        }
                    }
                }
            }
        ?></pre>
    <?php else: ?>
        <p class="error">❌ .env file not found at: <?= $envPath ?></p>
        <p>Create a .env file in your project root with:</p>
        <pre>PUSHER_APP_ID=2064007
PUSHER_APP_KEY=1e445d58814b765ccc57
PUSHER_APP_SECRET=ba430afed091143ee982
PUSHER_APP_CLUSTER=eu</pre>
    <?php endif; ?>

    <h2>5. Solution</h2>
    <?php if (!$envLoaded || empty($_ENV['PUSHER_APP_KEY'])): ?>
        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px;">
            <h3>⚠️ How to Fix:</h3>
            <ol>
                <li>Make sure your <code>.env</code> file exists in: <code><?= dirname(__DIR__) ?></code></li>
                <li>Add these lines to your <code>public/index.php</code> (BEFORE any other code):
                    <pre>require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();</pre>
                </li>
                <li>Refresh this page to verify</li>
            </ol>
        </div>
    <?php else: ?>
        <p class="success">✅ Everything looks good! Your Pusher configuration should work.</p>
        <p><strong>Test it:</strong> Go to <a href="test_pusher.php">test_pusher.php</a></p>
    <?php endif; ?>

    <hr>
    <p><small>⚠️ <strong>IMPORTANT:</strong> Delete this file after debugging!</small></p>
</body>
</html>