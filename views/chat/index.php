<?php 
$baseUrl ??= '/skillbox/public';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<section class="chat-list-section">
    <div class="container py-5">
        <div class="chat-list-header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fas fa-comments me-3"></i>
                    My Conversations
                </h1>
                <p class="page-subtitle">Connect and communicate with your service providers</p>
            </div>
        </div>

        <?php if (empty($conversations)): ?>
            <div class="empty-chat-state">
                <div class="empty-chat-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>No Conversations Yet</h3>
                <p>Start chatting with workers from the services page to get help with your projects!</p>
                <a href="<?= $baseUrl ?>/services" class="btn-browse-services">
                    <i class="fas fa-search me-2"></i>
                    Browse Services
                </a>
            </div>
        <?php else: ?>
            <div class="conversations-grid">
                <?php foreach ($conversations as $conv): 
                    $lastMessageTime = strtotime($conv['updated_at']);
                    $timeAgo = '';
                    $now = time();
                    $diff = $now - $lastMessageTime;
                    
                    if ($diff < 3600) {
                        $minutes = floor($diff / 60);
                        $timeAgo = $minutes < 1 ? 'Just now' : ($minutes . 'm ago');
                    } elseif ($diff < 86400) {
                        $hours = floor($diff / 3600);
                        $timeAgo = $hours . 'h ago';
                    } elseif ($diff < 604800) {
                        $days = floor($diff / 86400);
                        $timeAgo = $days . 'd ago';
                    } else {
                        $timeAgo = date('M d', $lastMessageTime);
                    }
                ?>
                    <a href="<?= $baseUrl ?>/chat/conversation/<?= $conv['id'] ?>" class="conversation-card">
                        <div class="conversation-avatar">
                            <?= strtoupper(substr($conv['other_user_name'], 0, 1)) ?>
                            <?php if ($conv['unread_count'] > 0): ?>
                                <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="conversation-content">
                            <div class="conversation-header">
                                <h5 class="conversation-name">
                                    <?= htmlspecialchars($conv['other_user_name']) ?>
                                </h5>
                                <span class="conversation-time"><?= $timeAgo ?></span>
                            </div>
                            <p class="conversation-preview">
                                <?= htmlspecialchars($conv['last_message_snippet'] ?? 'No messages yet') ?>
                            </p>
                        </div>
                        <?php if ($conv['unread_count'] > 0): ?>
                            <div class="conversation-indicator"></div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>