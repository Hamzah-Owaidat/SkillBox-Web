<?php $baseUrl ??= '/skillbox/public'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fas fa-comments"></i> My Conversations
            </h2>
            
            <?php if (empty($conversations)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    You don't have any conversations yet. Start chatting with a worker from the services page!
                </div>
                <a href="<?= $baseUrl ?>/services" class="btn btn-primary">
                    <i class="fas fa-search"></i> Browse Services
                </a>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($conversations as $conv): ?>
                        <a href="<?= $baseUrl ?>/chat/conversation/<?= $conv['id'] ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; font-size: 1.2rem;">
                                            <?= strtoupper(substr($conv['other_user_name'], 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">
                                            <?= htmlspecialchars($conv['other_user_name']) ?>
                                            <?php if ($conv['unread_count'] > 0): ?>
                                                <span class="badge bg-danger rounded-pill ms-2">
                                                    <?= $conv['unread_count'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </h5>
                                        <p class="mb-0 text-muted small">
                                            <?= htmlspecialchars($conv['last_message_snippet'] ?? 'No messages yet') ?>
                                        </p>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <?= date('M d, Y', strtotime($conv['updated_at'])) ?>
                                </small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>