<?php 
$baseUrl ??= '/skillbox/public'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentUserId = $_SESSION['user_id'] ?? null;
?>

<div class="chat-conversation-container">
    <div class="chat-wrapper">
        <!-- Modern Chat Header -->
        <div class="chat-header">
            <a href="<?= $baseUrl ?>/chat" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="chat-user-info">
                <div class="chat-avatar">
                    <?= strtoupper(substr($otherUser['full_name'], 0, 1)) ?>
                </div>
                <div class="chat-user-details">
                    <h5 class="chat-user-name"><?= htmlspecialchars($otherUser['full_name']) ?></h5>
                    <span class="chat-user-status">Online</span>
                </div>
            </div>
            <div class="chat-header-actions">
                <button class="header-action-btn" title="More options">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <!-- Messages Container -->
        <div class="chat-messages" id="messagesContainer">
            <?php if (empty($messages)): ?>
                <div class="empty-messages">
                    <div class="empty-messages-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h4>No messages yet</h4>
                    <p>Start the conversation by sending a message below!</p>
                </div>
            <?php else: ?>
                <div class="messages-list">
                    <?php 
                    $prevDate = '';
                    foreach ($messages as $msg): 
                        $isMine = $msg['sender_id'] == $currentUserId;
                        $msgDate = date('Y-m-d', strtotime($msg['send_at']));
                        $showDate = $msgDate !== $prevDate;
                        $prevDate = $msgDate;
                    ?>
                        <?php if ($showDate): ?>
                            <div class="message-date-divider">
                                <span><?= date('F j, Y', strtotime($msg['send_at'])) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="message-item <?= $isMine ? 'message-sent' : 'message-received' ?>" data-message-id="<?= $msg['id'] ?>">
                            <?php if (!$isMine): ?>
                                <div class="message-avatar">
                                    <?= strtoupper(substr($msg['sender_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="message-content">
                                <?php if (!$isMine): ?>
                                    <div class="message-sender-name"><?= htmlspecialchars($msg['sender_name']) ?></div>
                                <?php endif; ?>
                                
                                <div class="message-bubble-modern <?= $isMine ? 'bubble-sent' : 'bubble-received' ?>">
                                    <?php if (!empty($msg['attachment_path'])): ?>
                                        <?php
                                        $extension = strtolower(pathinfo($msg['attachment_path'], PATHINFO_EXTENSION));
                                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        ?>
                                        
                                        <?php if ($isImage): ?>
                                            <div class="message-image">
                                                <img src="<?= $baseUrl ?>/<?= $msg['attachment_path'] ?>" 
                                                     alt="Attachment"
                                                     onclick="window.open(this.src, '_blank')">
                                            </div>
                                        <?php else: ?>
                                            <div class="message-file">
                                                <i class="fas fa-file-alt"></i>
                                                <a href="<?= $baseUrl ?>/<?= $msg['attachment_path'] ?>" 
                                                   target="_blank" 
                                                   class="file-link">
                                                    <?= basename($msg['attachment_path']) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($msg['text'])): ?>
                                        <div class="message-text"><?= nl2br(htmlspecialchars($msg['text'])) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="message-meta">
                                    <span class="message-time"><?= date('g:i A', strtotime($msg['send_at'])) ?></span>
                                    <?php if ($isMine): ?>
                                        <span class="message-status">
                                            <?php if ($msg['is_readed']): ?>
                                                <i class="fas fa-check-double read"></i>
                                            <?php else: ?>
                                                <i class="fas fa-check sent"></i>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Message Input -->
        <div class="chat-input-container">
            <form id="messageForm" class="chat-input-form" enctype="multipart/form-data">
                <input type="hidden" name="conversation_id" value="<?= $conversation['id'] ?>">

                <!-- File Upload Button -->
                <label for="fileInput" class="attach-button" title="Attach file">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" id="fileInput" name="attachment" class="d-none" 
                           accept="image/*,.pdf,.doc,.docx">
                </label>
                
                <!-- Message Input -->
                <div class="input-wrapper">
                    <textarea class="message-input" 
                              id="messageInput" 
                              name="message" 
                              rows="1" 
                              placeholder="Type a message..."></textarea>
                </div>
                
                <!-- Send Button -->
                <button type="submit" class="send-button" id="sendBtn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            
            <!-- File Preview -->
            <div id="filePreview" class="file-preview d-none">
                <div class="file-preview-content">
                    <i class="fas fa-file"></i>
                    <span id="fileName"></span>
                    <button type="button" class="remove-file-btn" id="removeFile">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass PHP variables to JavaScript -->
<script>
    const CONVERSATION_ID = <?= $conversation['id'] ?>;
    const CURRENT_USER_ID = <?= $currentUserId ?>;
    const OTHER_USER_ID = <?= $otherUser['id'] ?>;
    window.BASE_URL = <?= json_encode($baseUrl) ?>;
    window.PUSHER_KEY = <?= json_encode($_ENV['PUSHER_APP_KEY']) ?>;
    window.PUSHER_CLUSTER = <?= json_encode($_ENV['PUSHER_APP_CLUSTER']) ?>;
</script>

<script src="<?= $baseUrl ?>/js/chat.js"></script>