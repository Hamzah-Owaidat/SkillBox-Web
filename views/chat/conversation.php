<?php 
$baseUrl ??= '/skillbox/public'; 
$currentUserId = $_SESSION['user_id'] ?? null;
?>

<div class="container-fluid py-4" style="height: calc(100vh - 120px);">
    <div class="row h-100">
        <div class="col-12">
            <div class="card h-100 shadow">
                <!-- Chat Header -->
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <a href="<?= $baseUrl ?>/chat" class="btn btn-sm btn-light me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center me-3" 
                             style="width: 40px; height: 40px;">
                            <?= strtoupper(substr($otherUser['full_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= htmlspecialchars($otherUser['full_name']) ?></h5>
                            <small><?= htmlspecialchars($otherUser['email']) ?></small>
                        </div>
                    </div>
                </div>

                <!-- Messages Container -->
                <div class="card-body overflow-auto" id="messagesContainer" style="flex: 1; max-height: calc(100vh - 300px);">
                    <?php if (empty($messages)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <?php $isMine = $msg['sender_id'] == $currentUserId; ?>
                            <div class="message-wrapper <?= $isMine ? 'text-end' : 'text-start' ?> mb-3" data-message-id="<?= $msg['id'] ?>">
                                <div class="d-inline-block" style="max-width: 70%;">
                                    <?php if (!$isMine): ?>
                                        <small class="text-muted"><?= htmlspecialchars($msg['sender_name']) ?></small>
                                    <?php endif; ?>
                                    
                                    <div class="message-bubble p-3 rounded <?= $isMine ? 'bg-primary text-white' : 'bg-light' ?>">
                                        <?php if (!empty($msg['attachment_path'])): ?>
                                            <?php
                                            $extension = strtolower(pathinfo($msg['attachment_path'], PATHINFO_EXTENSION));
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            ?>
                                            
                                            <?php if ($isImage): ?>
                                                <img src="<?= $baseUrl ?>/../<?= $msg['attachment_path'] ?>" 
                                                     class="img-fluid rounded mb-2" 
                                                     style="max-width: 300px; cursor: pointer;"
                                                     onclick="window.open(this.src, '_blank')">
                                            <?php else: ?>
                                                <a href="<?= $baseUrl ?>/../<?= $msg['attachment_path'] ?>" 
                                                   target="_blank" 
                                                   class="text-decoration-none <?= $isMine ? 'text-white' : 'text-primary' ?>">
                                                    <i class="fas fa-file"></i> 
                                                    <?= basename($msg['attachment_path']) ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($msg['text'])): ?>
                                            <div><?= nl2br(htmlspecialchars($msg['text'])) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <small class="text-muted d-block mt-1">
                                        <?= date('g:i A', strtotime($msg['send_at'])) ?>
                                        <?php if ($isMine && $msg['is_readed']): ?>
                                            <i class="fas fa-check-double text-primary"></i>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Message Input -->
                <div class="card-footer bg-light">
                    <form id="messageForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data">
                        <input type="hidden" name="conversation_id" value="<?= $conversation['id'] ?>">
                        
                        <!-- Emoji Picker Button -->
                        <button type="button" class="btn btn-outline-secondary" id="emojiBtn" title="Add emoji">
                            <i class="fas fa-smile"></i>
                        </button>
                        
                        <!-- File Upload Button -->
                        <label for="fileInput" class="btn btn-outline-secondary mb-0" title="Attach file">
                            <i class="fas fa-paperclip"></i>
                            <input type="file" id="fileInput" name="attachment" class="d-none" 
                                   accept="image/*,.pdf,.doc,.docx">
                        </label>
                        
                        <!-- Message Input -->
                        <textarea class="form-control" 
                                  id="messageInput" 
                                  name="message" 
                                  rows="1" 
                                  placeholder="Type a message..."
                                  style="resize: none;"></textarea>
                        
                        <!-- Send Button -->
                        <button type="submit" class="btn btn-primary" id="sendBtn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    
                    <!-- File Preview -->
                    <div id="filePreview" class="mt-2 d-none">
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                            <span><i class="fas fa-file"></i> <span id="fileName"></span></span>
                            <button type="button" class="btn btn-sm btn-close" id="removeFile"></button>
                        </div>
                    </div>
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

<style>
#messagesContainer {
    background: linear-gradient(to bottom, #f8f9fa, #ffffff);
}

.message-wrapper {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-bubble {
    word-wrap: break-word;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message-bubble img {
    border: 2px solid rgba(255,255,255,0.2);
}

#messageInput:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-outline-secondary:hover {
    transform: scale(1.1);
    transition: transform 0.2s;
}
</style>