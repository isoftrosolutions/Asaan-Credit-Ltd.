<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];
$pageTitle = 'Messages';

$useStitchHeader = true;
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/stitch-header.php';
?>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

<script>
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "outline": "#887271",
        "on-surface": "#1c1b1b",
        "surface-container-high": "#eae7e7",
        "primary": "#4d060f",
        "outline-variant": "#dbc0bf",
        "inverse-on-surface": "#f3f0ef",
        "surface-container-highest": "#e5e2e1",
        "primary-container": "#6b1d22",
        "on-background": "#1c1b1b",
        "error": "#ba1a1a",
        "on-surface-variant": "#554242",
        "background": "#fcf9f8",
        "secondary": "#3b6281",
        "surface-container": "#f0eded",
        "secondary-container": "#b1d9fd",
        "on-primary": "#ffffff",
        "surface-container-low": "#f6f3f2",
        "surface": "#fcf9f8",
        "surface-container-lowest": "#ffffff",
        "brand-navy": "#0B3A5E",
      },
      borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", "2xl": "1rem", full: "9999px" },
      fontFamily: { "body": ["Inter"], "heading": ["Montserrat"] }
    }
  }
}
</script>

<style>
.material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
  display: inline-block;
  vertical-align: middle;
}

.stitch-chat-scroll::-webkit-scrollbar { width: 4px; }
.stitch-chat-scroll::-webkit-scrollbar-thumb { background: #E5E2E1; border-radius: 10px; }

/* ── Mobile-first: chat panel off-screen by default ── */
.stitch-chat-panel {
  position: fixed;
  inset: 0;
  z-index: 30;
  transform: translateX(100%);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
}
.chat-shell.show-conv .stitch-chat-panel {
  transform: translateX(0);
}
.chat-shell.show-conv .stitch-chat-sidebar {
  display: none;
}

/* ── Desktop: side-by-side split ── */
@media (min-width: 768px) {
  .stitch-chat-panel {
    position: relative;
    transform: none;
    z-index: auto;
  }
  .stitch-chat-sidebar {
    display: flex !important;
  }
  .chat-shell.show-conv .stitch-chat-sidebar {
    display: flex !important;
  }
  .chat-shell.show-conv .stitch-chat-panel {
    transform: none;
  }
}

/* ── Typing indicator ── */
#chatTyping .dot {
  animation: typing 1.4s infinite ease-in-out;
}
#chatTyping .dot:nth-child(2) { animation-delay: 0.2s; }
#chatTyping .dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.3; }
  30% { transform: translateY(-5px); opacity: 1; }
}

.stitch-chat-glass {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* ── Conversation list items (JS-generated) ── */
.chat-list-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  width: 100%;
  text-align: left;
  cursor: pointer;
  border: none;
  background: transparent;
  border-left: 3px solid transparent;
  -webkit-tap-highlight-color: transparent;
}
.chat-list-item:active { background: #eae7e7; }
.chat-list-item.active {
  background: rgba(177, 217, 253, 0.25);
  border-left-color: #3b6281;
}
.chat-list-item.has-unread { background: rgba(177, 217, 253, 0.1); }

.chat-list-avatar {
  width: 44px;
  height: 44px;
  border-radius: 9999px;
  background: #003d60;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 15px;
  flex-shrink: 0;
  line-height: 1;
}
.chat-list-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 9999px;
  object-fit: cover;
}

.chat-list-info { flex: 1; min-width: 0; }

.chat-list-name {
  font-weight: 600;
  color: #1c1b1b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 15px;
}
.has-unread .chat-list-name { font-weight: 700; }

.chat-list-preview {
  font-size: 13px;
  color: #554242;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: 2px;
}
.has-unread .chat-list-preview {
  color: #1c1b1b;
  font-weight: 500;
}

.chat-list-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  flex-shrink: 0;
}

.chat-list-time {
  font-size: 11px;
  color: #887271;
  white-space: nowrap;
}

.chat-list-badge {
  min-width: 20px;
  height: 20px;
  background: #4d060f;
  color: #fff;
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  font-weight: 700;
  padding: 0 5px;
  line-height: 1;
}

/* ── Message bubbles (JS-generated) ── */
.chat-msg {
  display: flex;
  flex-direction: column;
  max-width: 88%;
}
@media (min-width: 768px) { .chat-msg { max-width: 70%; } }

.msg-mine { align-items: flex-end; margin-left: auto; }
.msg-theirs { align-items: flex-start; }

.chat-msg-text {
  padding: 10px 14px;
  border-radius: 16px;
  font-size: 15px;
  line-height: 1.5;
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: pre-wrap;
}
.msg-mine .chat-msg-text {
  background: #0B3A5E;
  color: #fff;
  border-bottom-right-radius: 4px;
}
.msg-theirs .chat-msg-text {
  background: #fff;
  color: #1c1b1b;
  border-bottom-left-radius: 4px;
}

.chat-msg-time {
  font-size: 10px;
  color: #887271;
  padding: 0 4px;
  margin-top: 3px;
  white-space: nowrap;
}

.chat-list-empty, .chat-loading {
  padding: 48px 24px;
  text-align: center;
  color: #554242;
  font-size: 14px;
}
.chat-list-empty-ico { display: block; margin-bottom: 12px; opacity: 0.4; }
</style>

<main class="chat-shell flex flex-col h-[100dvh] bg-[#fcf9f8]" id="chatApp" data-user-id="<?= $userId ?>">

  <!-- ═══ Sidebar: conversation list ═══ -->
  <aside class="stitch-chat-sidebar flex flex-col flex-1 bg-white">

    <!-- Mobile header -->
    <div class="flex items-center justify-between px-4 h-14 border-b border-[#dbc0bf] bg-white shrink-0">
      <h1 class="text-lg font-bold text-[#1c1b1b]" style="font-family: Montserrat;">Messages</h1>
      <button class="w-10 h-10 flex items-center justify-center text-[#554242] hover:bg-[#eae7e7] rounded-full transition-colors -mr-1" type="button" aria-label="Notifications">
        <span class="material-symbols-outlined" style="font-size:22px;">notifications</span>
      </button>
    </div>

    <!-- Search -->
    <div class="px-3 pt-3 pb-2 bg-[#f6f3f2] shrink-0">
      <div class="relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#887271] text-lg pointer-events-none">search</span>
        <input id="chatSearch" class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#dbc0bf] rounded-xl focus:ring-2 focus:ring-[#3b6281] focus:border-[#3b6281] transition-all outline-none text-sm placeholder:text-[#887271]" placeholder="Search" type="text"/>
      </div>
    </div>

    <!-- Conversation list -->
    <div class="flex-1 overflow-y-auto stitch-chat-scroll" id="chatListBody">
      <div class="chat-loading">Loading conversations…</div>
    </div>
  </aside>

  <!-- ═══ Chat panel ═══ -->
  <section class="stitch-chat-panel bg-[#f0eded]">

    <!-- Empty state (no conversation selected) -->
    <div class="flex-1 flex items-center justify-center px-6" id="chatEmpty">
      <div class="text-center">
        <span class="material-symbols-outlined text-[#887271] mb-4 inline-block" style="font-size:52px;">chat</span>
        <h3 class="text-lg font-bold text-[#1c1b1b] mb-1">Your Messages</h3>
        <p class="text-sm text-[#554242]">Select a conversation to start chatting with your connections.</p>
      </div>
    </div>

    <!-- Active conversation -->
    <div id="chatConv" style="display:none;" class="flex flex-col h-full">

      <!-- Chat header -->
      <header class="flex items-center justify-between px-2 h-14 bg-white border-b border-[#dbc0bf] shrink-0">
        <div class="flex items-center gap-1 min-w-0">
          <button id="chatBackBtn" class="w-11 h-11 flex items-center justify-center hover:bg-[#eae7e7] rounded-full transition-colors -ml-1 shrink-0" type="button" aria-label="Back">
            <span class="material-symbols-outlined text-[#1c1b1b]" style="font-size:24px;">arrow_back</span>
          </button>
          <div id="chatConvAvatar" class="w-10 h-10 rounded-full bg-[#003d60] text-white flex items-center justify-center font-bold text-sm shrink-0">U</div>
          <div class="min-w-0 ml-1">
            <h3 id="chatConvName" class="font-semibold text-[#1c1b1b] text-sm leading-tight truncate">User</h3>
            <span id="chatConvRole" class="text-[10px] text-[#554242] hidden"></span>
          </div>
        </div>
        <div class="flex items-center gap-0">
          <button class="w-11 h-11 flex items-center justify-center text-[#554242] hover:bg-[#eae7e7] rounded-full transition-colors" type="button" aria-label="Video call">
            <span class="material-symbols-outlined" style="font-size:22px;">videocam</span>
          </button>
          <button class="w-11 h-11 flex items-center justify-center text-[#554242] hover:bg-[#eae7e7] rounded-full transition-colors" type="button" aria-label="More options">
            <span class="material-symbols-outlined" style="font-size:22px;">more_vert</span>
          </button>
        </div>
      </header>

      <!-- Messages -->
      <div class="flex-1 overflow-y-auto stitch-chat-scroll px-3 py-3" id="chatConvBody">
        <div class="space-y-3" id="chatMsgs"></div>
      </div>

      <!-- Typing -->
      <div id="chatTyping" style="display:none;" class="flex items-center gap-2 text-[#554242] px-4 pb-1">
        <div class="flex gap-1 bg-white px-3 py-2 rounded-full shadow-sm">
          <div class="dot w-1.5 h-1.5 bg-[#887271] rounded-full"></div>
          <div class="dot w-1.5 h-1.5 bg-[#887271] rounded-full"></div>
          <div class="dot w-1.5 h-1.5 bg-[#887271] rounded-full"></div>
        </div>
        <span class="text-[11px] font-semibold italic">typing…</span>
      </div>

      <!-- Input bar -->
      <div class="px-3 pb-3 pt-1" style="padding-bottom:calc(0.75rem + env(safe-area-inset-bottom, 0px));">
        <div class="stitch-chat-glass border border-[#dbc0bf] rounded-2xl flex items-end p-1.5 gap-1.5 shadow-sm focus-within:shadow-md focus-within:border-[#3b6281] focus-within:ring-2 focus-within:ring-[#3b6281]/20 transition-all">
          <button class="w-10 h-10 flex items-center justify-center text-[#554242] hover:text-[#0B3A5E] transition-colors shrink-0 rounded-full" type="button" aria-label="Attach file">
            <span class="material-symbols-outlined" style="font-size:20px;">attach_file</span>
          </button>
          <textarea id="chatInput" class="flex-1 bg-transparent border-none focus:ring-0 resize-none py-2 text-[15px] leading-5 max-h-[120px] outline-none placeholder:text-[#887271]" placeholder="Message…" rows="1"></textarea>
          <button id="chatSendBtn" class="w-10 h-10 flex items-center justify-center bg-[#0B3A5E] text-white rounded-full transition-all opacity-40 scale-90 pointer-events-none shrink-0" type="button" aria-label="Send">
            <span class="material-symbols-outlined" style="font-size:20px;">send</span>
          </button>
        </div>
      </div>

    </div>
  </section>

</main>

<script src="<?= APP_URL ?>/assets/chat.js?v=<?= filemtime(__DIR__ . '/../assets/chat.js') ?>"></script>

<?php
$hidePublicFooter = true;
require __DIR__ . '/../includes/footer.php';
?>
