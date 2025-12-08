(function() {
    'use strict';

    const baseUrl = window.BASE_URL;
    console.log(baseUrl);

    // Configuration
    const PUSHER_KEY = window.PUSHER_KEY;
    const PUSHER_CLUSTER = window.PUSHER_CLUSTER;
    console.log("Chat.js");
    console.log(PUSHER_KEY);
    console.log(PUSHER_CLUSTER);

    // Elements
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const messagesContainer = document.getElementById('messagesContainer');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const removeFileBtn = document.getElementById('removeFile');
    const emojiBtn = document.getElementById('emojiBtn');
    const sendBtn = document.getElementById('sendBtn');

    // State
    let selectedFile = null;
    let pusher = null;
    let channel = null;

    /**
     * Initialize Pusher for real-time messaging
     */
    function initPusher() {
        try {
            // Initialize Pusher
            const pusher = new Pusher(PUSHER_KEY, {
                cluster: PUSHER_CLUSTER,
                encrypted: true,
                authEndpoint: `${baseUrl}/pusher/auth`,
                auth: {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            });

            // Create channel name (same format as backend)
            const users = [CURRENT_USER_ID, OTHER_USER_ID].sort((a, b) => a - b);
            const channelName = `private-chat-${users[0]}-${users[1]}`;

            // Subscribe to channel
            channel = pusher.subscribe(channelName);

            // Listen for new messages
            channel.bind('chat.message', function(data) {
                console.log('Received message:', data);
                
                // Only add message if it's from the other user (our own messages are added immediately)
                if (data.sender_id != CURRENT_USER_ID) {
                    appendMessage(data);
                    scrollToBottom();
                    
                    // Mark as read
                    markAsRead();
                }
            });

            console.log('✅ Pusher connected to channel:', channelName);
        } catch (error) {
            console.error('❌ Pusher initialization error:', error);
        }
    }

    /**
     * Send message via AJAX
     */
    async function sendMessage(e) {
        e.preventDefault();

        const message = messageInput.value.trim();
        
        // Validate
        if (!message && !selectedFile) {
            showToast('Please enter a message or select a file', 'warning');
            return;
        }

        // Disable send button
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            // Prepare form data
            const formData = new FormData();
            formData.append('conversation_id', CONVERSATION_ID);
            formData.append('message', message);
            
            if (selectedFile) {
                formData.append('attachment', selectedFile);
            }

            // Send via AJAX
            const response = await fetch(`${baseUrl}/chat/send`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Add message to UI immediately (optimistic update)
                appendMessage(result.message, true);
                
                // Clear input
                messageInput.value = '';
                messageInput.style.height = 'auto'; // Reset textarea height
                clearFileSelection();
                
                // Scroll to bottom
                scrollToBottom();
            } else {
                showToast(result.error || 'Failed to send message', 'danger');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            showToast('Network error. Please try again.', 'danger');
        } finally {
            // Re-enable send button
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        }
    }

    /**
     * Append message to chat
     */
    function appendMessage(message, isOptimistic = false) {
        const isMine = message.sender_id == CURRENT_USER_ID;
        
        // Check if message already exists (avoid duplicates)
        if (document.querySelector(`[data-message-id="${message.id}"]`)) {
            return;
        }

        // Find or create messages list
        let messagesList = messagesContainer.querySelector('.messages-list');
        if (!messagesList) {
            messagesList = document.createElement('div');
            messagesList.className = 'messages-list';
            messagesContainer.innerHTML = '';
            messagesContainer.appendChild(messagesList);
        }

        const messageItem = document.createElement('div');
        messageItem.className = `message-item ${isMine ? 'message-sent' : 'message-received'}`;
        messageItem.setAttribute('data-message-id', message.id);

        let attachmentHtml = '';
        if (message.attachment_path) {
            const extension = message.attachment_path.split('.').pop().toLowerCase();
            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
            
            if (isImage) {
                attachmentHtml = `
                    <div class="message-image">
                        <img src="${baseUrl}/${message.attachment_path}" 
                             alt="Attachment"
                             onclick="window.open(this.src, '_blank')">
                    </div>
                `;
            } else {
                attachmentHtml = `
                    <div class="message-file">
                        <i class="fas fa-file-alt"></i>
                        <a href="${baseUrl}/${message.attachment_path}" 
                           target="_blank" 
                           class="file-link">
                            ${message.attachment_path.split('/').pop()}
                        </a>
                    </div>
                `;
            }
        }

        const textHtml = message.text ? `<div class="message-text">${escapeHtml(message.text).replace(/\n/g, '<br>')}</div>` : '';

        messageItem.innerHTML = `
            ${!isMine ? `
                <div class="message-avatar">
                    ${escapeHtml(message.sender_name).charAt(0).toUpperCase()}
                </div>
            ` : ''}
            <div class="message-content">
                ${!isMine ? `<div class="message-sender-name">${escapeHtml(message.sender_name)}</div>` : ''}
                <div class="message-bubble-modern ${isMine ? 'bubble-sent' : 'bubble-received'}">
                    ${attachmentHtml}
                    ${textHtml}
                </div>
                <div class="message-meta">
                    <span class="message-time">${formatTime(message.send_at)}</span>
                    ${isMine ? `
                        <span class="message-status">
                            ${message.is_readed ? '<i class="fas fa-check-double read"></i>' : '<i class="fas fa-check sent"></i>'}
                        </span>
                    ` : ''}
                </div>
            </div>
        `;

        messagesList.appendChild(messageItem);
        
        // Scroll to bottom after adding message
        scrollToBottom();
    }

    /**
     * Handle file selection
     */
    function handleFileSelect() {
        const file = fileInput.files[0];
        
        if (!file) return;

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            showToast('File size must be less than 5MB', 'warning');
            fileInput.value = '';
            return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 
                             'application/pdf', 'application/msword', 
                             'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (!allowedTypes.includes(file.type)) {
            showToast('File type not allowed', 'warning');
            fileInput.value = '';
            return;
        }

        selectedFile = file;
        fileName.textContent = file.name;
        filePreview.classList.remove('d-none');
    }

    /**
     * Clear file selection
     */
    function clearFileSelection() {
        selectedFile = null;
        fileInput.value = '';
        filePreview.classList.add('d-none');
        fileName.textContent = '';
    }

    /**
     * Mark conversation as read
     */
    async function markAsRead() {
        try {
            await fetch(`${baseUrl}/chat/mark-read/${CONVERSATION_ID}`, {
                method: 'POST'
            });
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }

    /**
     * Scroll to bottom of messages
     */
    function scrollToBottom() {
        if (messagesContainer) {
            // Use smooth scroll with a slight delay to ensure content is rendered
            setTimeout(() => {
                messagesContainer.scrollTo({
                    top: messagesContainer.scrollHeight,
                    behavior: 'smooth'
                });
            }, 100);
        }
    }

    /**
     * Format time
     */
    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'info') {
        // Simple alert for now (you can replace with Bootstrap toast)
        alert(message);
    }

    /**
     * Auto-resize textarea
     */
    function autoResizeTextarea() {
        messageInput.style.height = 'auto';
        messageInput.style.height = Math.min(messageInput.scrollHeight, 120) + 'px';
    }

    /**
     * Handle Enter key (send on Enter, new line on Shift+Enter)
     */
    function handleKeyPress(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    }

    /**
     * Initialize everything
     */
    function init() {
        // Initialize Pusher
        initPusher();

        // Event listeners
        messageForm.addEventListener('submit', sendMessage);
        fileInput.addEventListener('change', handleFileSelect);
        removeFileBtn.addEventListener('click', clearFileSelection);
        if (emojiBtn) {
            emojiBtn.addEventListener('click', function() {
                console.log('Emoji picker not implemented yet');
            });
        }
        messageInput.addEventListener('input', autoResizeTextarea);
        messageInput.addEventListener('keypress', handleKeyPress);

        // Scroll to bottom on load with delay to ensure DOM is ready
        setTimeout(scrollToBottom, 300);

        // Mark messages as read
        markAsRead();

        console.log('✅ Chat initialized');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();