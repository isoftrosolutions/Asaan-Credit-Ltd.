<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];

$pageTitle = 'Messages';
require __DIR__ . '/../includes/layout-dashboard.php';

ui_page_header('Messages', 'Chat with your connections');
?>

<div class="chat-shell" id="chatApp" data-user-id="<?= $userId ?>">

  <!-- Conversation List -->
  <aside class="chat-list" id="chatList">
    <div class="chat-list-header">
      <input type="text" class="chat-search" id="chatSearch" placeholder="Search conversations…" aria-label="Search conversations">
    </div>
    <div class="chat-list-body" id="chatListBody">
      <div class="chat-loading">Loading conversations…</div>
    </div>
  </aside>

  <!-- Chat Area -->
  <main class="chat-main" id="chatMain">
    <!-- Empty state: no conversation selected -->
    <div class="chat-empty" id="chatEmpty">
      <span class="chat-empty-ico">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
          <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
      </span>
      <h3>Your Messages</h3>
      <p>Select a conversation to start chatting.</p>
    </div>

    <!-- Active conversation -->
    <div class="chat-conv" id="chatConv" style="display:none;">
      <div class="chat-conv-header" id="chatConvHeader">
        <button class="chat-back-btn" id="chatBackBtn" type="button" aria-label="Back to conversations">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
          </svg>
        </button>
        <div class="chat-conv-user">
          <div class="chat-conv-avatar" id="chatConvAvatar">U</div>
          <div>
            <div class="chat-conv-name" id="chatConvName">User</div>
            <div class="chat-conv-role" id="chatConvRole">Role</div>
          </div>
        </div>
      </div>

      <div class="chat-conv-body" id="chatConvBody">
        <div class="chat-msgs" id="chatMsgs"></div>
        <div class="chat-typing" id="chatTyping" style="display:none;">
          <span class="chat-typing-dot"></span>
          <span class="chat-typing-dot"></span>
          <span class="chat-typing-dot"></span>
          <span class="chat-typing-text">typing…</span>
        </div>
      </div>

      <div class="chat-conv-input">
        <textarea class="chat-input" id="chatInput" rows="1" placeholder="Type your message…" aria-label="Type your message"></textarea>
        <button class="chat-send-btn" id="chatSendBtn" type="button" aria-label="Send message">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
          </svg>
        </button>
      </div>
    </div>
  </main>

</div>

<script src="<?= APP_URL ?>/assets/chat.js?v=<?= filemtime(__DIR__ . '/../assets/chat.js') ?>" defer></script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
