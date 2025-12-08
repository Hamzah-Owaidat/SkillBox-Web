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
                    <a href="<?= $baseUrl ?>/chat/conversation/<?= $conv['id'] ?>" 
                       class="conversation-card" 
                       data-conv-id="<?= $conv['id'] ?>"
                       data-other-id="<?= $conv['other_user_id'] ?>">
                        <div class="conversation-avatar">
                            <?= strtoupper(substr($conv['other_user_name'], 0, 1)) ?>
                            <span class="unread-badge <?= $conv['unread_count'] > 0 ? '' : 'd-none' ?>">
                                <?= $conv['unread_count'] > 0 ? $conv['unread_count'] : '0' ?>
                            </span>
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
                        <div class="conversation-indicator <?= $conv['unread_count'] > 0 ? '' : 'd-none' ?>"></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
(function() {
    'use strict';
    
    const currentUserId = <?= json_encode($_SESSION['user_id'] ?? null) ?>;
    const baseUrl = <?= json_encode($baseUrl) ?>;
    const pusherKey = <?= json_encode($_ENV['PUSHER_APP_KEY'] ?? '') ?>;
    const pusherCluster = <?= json_encode($_ENV['PUSHER_APP_CLUSTER'] ?? '') ?>;
    
    if (!currentUserId || !pusherKey) {
        return;
    }
    
    let pusher = null;
    const channels = {};
    
    // Get all conversation cards and their data
    function getConversationsData() {
        const cards = document.querySelectorAll('.conversation-card');
        const data = [];
        cards.forEach(card => {
            data.push({
                id: parseInt(card.getAttribute('data-conv-id')),
                otherId: parseInt(card.getAttribute('data-other-id'))
            });
        });
        return data;
    }
    
    // Initialize Pusher
    function initPusher() {
        try {
            pusher = new Pusher(pusherKey, {
                cluster: pusherCluster,
                encrypted: true,
                authEndpoint: `${baseUrl}/pusher/auth`,
                auth: {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }
            });
            
            const conversations = getConversationsData();
            conversations.forEach(conv => {
                subscribeToConversation(conv);
            });
            
            console.log('✅ Chat list Pusher initialized');
        } catch (error) {
            console.error('❌ Pusher error:', error);
        }
    }
    
    // Subscribe to a conversation channel
    function subscribeToConversation(conv) {
        if (!conv.otherId) return;
        
        const users = [currentUserId, conv.otherId].sort((a, b) => a - b);
        const channelName = `private-chat-${users[0]}-${users[1]}`;
        
        if (channels[channelName]) return;
        
        try {
            const channel = pusher.subscribe(channelName);
            channels[channelName] = channel;
            
            channel.bind('chat.message', function(data) {
                if (data.sender_id != currentUserId && data.conversation_id == conv.id) {
                    updateConversationCard(conv.id, data);
                }
            });
        } catch (error) {
            console.error('Subscription error:', error);
        }
    }
    
    // Update conversation card when new message arrives
    function updateConversationCard(conversationId, messageData) {
        const card = document.querySelector(`[data-conv-id="${conversationId}"]`);
        if (!card) return;
        
        // Update last message
        const preview = card.querySelector('.conversation-preview');
        if (preview) {
            let text = messageData.text || '';
            if (messageData.attachment_path) {
                text = '[Attachment]';
            }
            if (text.length > 50) {
                text = text.substring(0, 50) + '...';
            }
            preview.textContent = text || 'No messages yet';
        }
        
        // Update time
        const timeEl = card.querySelector('.conversation-time');
        if (timeEl) {
            timeEl.textContent = 'Just now';
        }
        
        // Update unread badge
        const badge = card.querySelector('.unread-badge');
        if (badge) {
            let count = parseInt(badge.textContent) || 0;
            count++;
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('d-none');
        }
        
        // Show indicator
        const indicator = card.querySelector('.conversation-indicator');
        if (indicator) {
            indicator.classList.remove('d-none');
        }
        
        // Move to top
        const grid = document.querySelector('.conversations-grid');
        if (grid && card.parentNode === grid) {
            grid.insertBefore(card, grid.firstChild);
        }
    }
    
    // Refresh conversations from server
    async function refreshConversations() {
        try {
            const response = await fetch(`${baseUrl}/api/chat/conversations`);
            const data = await response.json();
            
            if (data.success && data.conversations) {
                data.conversations.forEach(conv => {
                    const card = document.querySelector(`[data-conv-id="${conv.id}"]`);
                    if (card) {
                        // Update unread count
                        const badge = card.querySelector('.unread-badge');
                        if (badge) {
                            const count = conv.unread_count || 0;
                            badge.textContent = count > 99 ? '99+' : count;
                            if (count > 0) {
                                badge.classList.remove('d-none');
                            } else {
                                badge.classList.add('d-none');
                            }
                        }
                        
                        // Update last message
                        const preview = card.querySelector('.conversation-preview');
                        if (preview && conv.last_message_snippet) {
                            preview.textContent = conv.last_message_snippet;
                        }
                        
                        // Update indicator
                        const indicator = card.querySelector('.conversation-indicator');
                        if (indicator) {
                            if (conv.unread_count > 0) {
                                indicator.classList.remove('d-none');
                            } else {
                                indicator.classList.add('d-none');
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Refresh error:', error);
        }
    }
    
    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initPusher();
            setInterval(refreshConversations, 30000); // Refresh every 30 seconds
        });
    } else {
        initPusher();
        setInterval(refreshConversations, 30000);
    }
    
    // Refresh when page becomes visible
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            refreshConversations();
        }
    });
})();
</script>