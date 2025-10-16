<?php
/**
 * Pusher Configuration
 * Get your credentials from https://dashboard.pusher.com/
 * 
 * Make sure your .env file has:
 * PUSHER_APP_ID=your_app_id
 * PUSHER_APP_KEY=your_app_key
 * PUSHER_APP_SECRET=your_app_secret
 * PUSHER_APP_CLUSTER=eu
 * PUSHER_DEBUG=true
 */

return [
    'app_id' => $_ENV['PUSHER_APP_ID'] ?? getenv('PUSHER_APP_ID') ?: '',
    'key' => $_ENV['PUSHER_APP_KEY'] ?? getenv('PUSHER_APP_KEY') ?: '',
    'secret' => $_ENV['PUSHER_APP_SECRET'] ?? getenv('PUSHER_APP_SECRET') ?: '',
    'cluster' => $_ENV['PUSHER_APP_CLUSTER'] ?? getenv('PUSHER_APP_CLUSTER') ?: 'eu',
    
    // Corrected: 'use_tls' instead of 'useTLS' (to match your service config)
    'use_tls' => true,

    // Fixed: 'debig' → 'debug'
    'debug' => filter_var($_ENV['PUSHER_DEBUG'] ?? getenv('PUSHER_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN),

    // Optional: default timeout & logging
    'options' => [
        'timeout' => 5,
        'encrypted' => true,
    ],

    // Channel naming conventions
    'channels' => [
        'notifications' => 'notifications-channel',
        'private_prefix' => 'private-user-', // example: private-user-12
    ],

    // Event naming conventions
    'events' => [
        'service_added' => 'service.added',
        'service_updated' => 'service.updated',
        'service_deleted' => 'service.deleted',
        'notification_received' => 'notification.received',
    ],
];
