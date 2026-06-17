(function () {
  'use strict';

  var app = document.getElementById('chatApp');
  if (!app) return;

  var USER_ID = app.dataset.userId;

  var listBody = document.getElementById('chatListBody');
  var chatSearch = document.getElementById('chatSearch');
  var chatEmpty = document.getElementById('chatEmpty');
  var chatConv = document.getElementById('chatConv');
  var chatMsgs = document.getElementById('chatMsgs');
  var chatInput = document.getElementById('chatInput');
  var chatSendBtn = document.getElementById('chatSendBtn');
  var convName = document.getElementById('chatConvName');
  var convRole = document.getElementById('chatConvRole');
  var convAvatar = document.getElementById('chatConvAvatar');
  var chatTyping = document.getElementById('chatTyping');
  var chatBackBtn = document.getElementById('chatBackBtn');

  var activeConvId = null;
  var activeOtherId = null;
  var lastMessageTime = null;
  var typingTimer = null;
  var convPollTimer = null;
  var msgPollTimer = null;

  // ---- Conversations ----
  function loadConversations() {
    fetch(APP_URL + '/api/conversations.php')
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          renderConversations(data.conversations);
        }
      })
      .catch(function () {});
  }

  function renderConversations(list) {
    if (!list || list.length === 0) {
      listBody.innerHTML =
        '<div class="chat-list-empty">' +
        '  <span class="chat-list-empty-ico">' +
        '    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36">' +
        '      <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>' +
        '    </svg>' +
        '  </span>' +
        '  <p>No conversations yet. Express interest to connect with someone!</p>' +
        '</div>';
      return;
    }

    var html = '';
    for (var i = 0; i < list.length; i++) {
      var c = list[i];
      var initial = (c.other_name || 'U').charAt(0).toUpperCase();
      var unread = parseInt(c.unread, 10);
      var unreadBadge = unread > 0
        ? '<span class="chat-list-badge">' + (unread > 9 ? '9+' : unread) + '</span>'
        : '';
      var lastMsg = c.last_message || '';
      var truncated = lastMsg.length > 60 ? lastMsg.substring(0, 60) + '…' : lastMsg;
      var isActive = String(c.id) === activeConvId;

      html +=
        '<div class="chat-list-item' + (unread > 0 ? ' has-unread' : '') + (isActive ? ' active' : '') + '" data-id="' + c.id + '" data-other-id="' + c.other_id + '" onclick="ChatApp.openConversation(' + c.id + ', ' + c.other_id + ')">' +
        '  <div class="chat-list-avatar">' + initial + '</div>' +
        '  <div class="chat-list-info">' +
        '    <div class="chat-list-name">' + esc(unesc(c.other_name || 'Unknown')) + '</div>' +
        '    <div class="chat-list-preview">' + esc(unesc(truncated || 'No messages yet')) + '</div>' +
        '  </div>' +
        '  <div class="chat-list-meta">' +
        '    <span class="chat-list-time">' + (c.last_message_at ? fmtTime(c.last_message_at) : '') + '</span>' +
        '    ' + unreadBadge +
        '  </div>' +
        '</div>';
    }
    listBody.innerHTML = html;
  }

  // ---- Messages ----
  function loadMessages(convId) {
    fetch(APP_URL + '/api/messages-poll.php?conversation_id=' + convId)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          renderMessages(data.messages, false);
          markAsRead(convId);
          if (data.messages.length > 0) {
            lastMessageTime = data.messages[data.messages.length - 1].created_at;
          }
        }
      })
      .catch(function () {});
  }

  function pollNewMessages() {
    if (!activeConvId || !lastMessageTime) return;

    fetch(APP_URL + '/api/messages-poll.php?conversation_id=' + activeConvId + '&since=' + encodeURIComponent(lastMessageTime))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success && data.messages && data.messages.length > 0) {
          for (var i = 0; i < data.messages.length; i++) {
            var msg = data.messages[i];
            var el = makeBubble(msg);
            chatMsgs.appendChild(el);
            lastMessageTime = msg.created_at;
          }
          scrollBottom();
        }
      })
      .catch(function () {});
  }

  function renderMessages(messages, append) {
    if (!append) chatMsgs.innerHTML = '';

    for (var i = 0; i < messages.length; i++) {
      var el = makeBubble(messages[i]);
      chatMsgs.appendChild(el);
    }

    scrollBottom();
  }

  function makeBubble(msg) {
    var isMine = String(msg.sender_id) === USER_ID;
    var div = document.createElement('div');
    div.className = 'chat-msg' + (isMine ? ' msg-mine' : ' msg-theirs');
    div.dataset.id = msg.id;

    var text = document.createElement('div');
    text.className = 'chat-msg-text';
    text.textContent = msg.message;

    var time = document.createElement('span');
    time.className = 'chat-msg-time';
    time.textContent = fmtTime(msg.created_at);

    div.appendChild(text);
    div.appendChild(time);
    return div;
  }

  function sendMessage() {
    var text = chatInput.value.trim();
    if (!text || !activeConvId) return;

    chatInput.value = '';
    autoResize();

    var formData = new FormData();
    formData.append('_csrf', CSRF_TOKEN);
    formData.append('conversation_id', activeConvId);
    formData.append('message', text);

    fetch(APP_URL + '/api/messages.php', {
      method: 'POST',
      body: formData,
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          var el = makeBubble({
            id: data.message_id,
            message: text,
            sender_id: USER_ID,
            created_at: data.created_at,
          });
          chatMsgs.appendChild(el);
          lastMessageTime = data.created_at;
          scrollBottom();
          refreshConversationList();
        }
      })
      .catch(function () {});
  }

  // ---- Mark as read ----
  function markAsRead(convId) {
    var formData = new FormData();
    formData.append('_csrf', CSRF_TOKEN);
    formData.append('conversation_id', convId);

    fetch(APP_URL + '/api/conversation-mark-read.php', {
      method: 'POST',
      body: formData,
    }).catch(function () {});

    var item = document.querySelector('.chat-list-item[data-id="' + convId + '"]');
    if (item) item.classList.remove('has-unread');
  }

  // ---- Open conversation ----
  function openConversation(convId, otherId) {
    if (String(convId) === activeConvId) return;

    activeConvId = String(convId);
    activeOtherId = String(otherId);
    lastMessageTime = null;

    document.querySelectorAll('.chat-list-item').forEach(function (el) {
      el.classList.toggle('active', el.dataset.id === activeConvId);
    });

    chatEmpty.style.display = 'none';
    chatConv.style.display = 'flex';

    var item = document.querySelector('.chat-list-item[data-id="' + convId + '"]');
    if (item) {
      var nameEl = item.querySelector('.chat-list-name');
      var avatarEl = item.querySelector('.chat-list-avatar');
      if (nameEl) convName.textContent = nameEl.textContent;
      if (avatarEl) convAvatar.textContent = avatarEl.textContent;
    }

    convRole.textContent = '';

    fetch(APP_URL + '/api/users.php?id=' + otherId)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success && data.user) {
          var u = data.user;
          convRole.textContent = roleLabel(u.role || '');
          if (u.name) convName.textContent = u.name;
          convAvatar.textContent = (u.name || 'U').charAt(0).toUpperCase();
        }
      })
      .catch(function () {});

    chatMsgs.innerHTML = '';
    loadMessages(convId);

    chatInput.focus();
    document.querySelector('.chat-shell').classList.add('show-conv');
  }

  // ---- Helpers ----
  function scrollBottom() {
    var body = document.getElementById('chatConvBody');
    if (body) body.scrollTop = body.scrollHeight;
  }

  function fmtTime(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr.replace(' ', 'T'));
    if (isNaN(d.getTime())) return dateStr;

    var now = new Date();
    var diffMs = now - d;
    var diffDays = Math.floor(diffMs / 86400000);
    var h = d.getHours().toString().padStart(2, '0');
    var m = d.getMinutes().toString().padStart(2, '0');

    if (diffDays === 0) return h + ':' + m;

    var y = new Date(now);
    y.setDate(y.getDate() - 1);
    if (d.toDateString() === y.toDateString()) return 'Yesterday';

    if (diffDays < 7) {
      var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      return days[d.getDay()] + ' ' + h + ':' + m;
    }

    return (d.getDate()).toString().padStart(2, '0') + '/' + (d.getMonth() + 1).toString().padStart(2, '0') + ' ' + h + ':' + m;
  }

  function roleLabel(role) {
    var map = {
      'investor': 'Investor',
      'individual_investor': 'Individual Investor',
      'investment_manager': 'Investment Manager',
      'business_owner': 'Business Owner',
      'entrepreneur': 'Entrepreneur',
      'franchisor': 'Franchisor',
      'advisor': 'Advisor',
      'broker': 'Broker',
    };
    return map[role] || role || '';
  }

  function autoResize() {
    chatInput.style.height = 'auto';
    chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
  }

  function esc(str) {
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  function unesc(str) {
    if (!str) return '';
    return str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#039;/g, "'");
  }

  function refreshConversationList() {
    loadConversations();
  }

  // ---- Back button (mobile) ----
  chatBackBtn.addEventListener('click', function () {
    activeConvId = null;
    activeOtherId = null;
    chatConv.style.display = 'none';
    chatEmpty.style.display = 'flex';
    document.querySelector('.chat-shell').classList.remove('show-conv');
  });

  // ---- Input events ----
  chatInput.addEventListener('input', function () {
    autoResize();
  });

  chatInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  chatSendBtn.addEventListener('click', sendMessage);

  // ---- Search ----
  chatSearch.addEventListener('input', function () {
    var q = this.value.toLowerCase().trim();
    document.querySelectorAll('.chat-list-item').forEach(function (el) {
      var nameEl = el.querySelector('.chat-list-name');
      var match = !q || (nameEl && nameEl.textContent.toLowerCase().indexOf(q) !== -1);
      el.style.display = match ? '' : 'none';
    });
  });

  // ---- External API ----
  window.ChatApp = {
    openConversation: openConversation,
    openWithUser: function (userId) {
      var formData = new FormData();
      formData.append('_csrf', CSRF_TOKEN);
      formData.append('user_id', userId);

      fetch(APP_URL + '/api/conversations.php', {
        method: 'POST',
        body: formData,
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            loadConversations();
            setTimeout(function () {
              var el = document.querySelector('.chat-list-item[data-id="' + data.conversation_id + '"]');
              if (el) el.click();
            }, 300);
          } else {
            if (data.error) showToast(data.error, 'error');
          }
        })
        .catch(function () {});
    },
  };

  // ---- Init ----
  loadConversations();

  // Poll for new messages every 3 seconds (only in active conversation)
  setInterval(function () {
    pollNewMessages();
  }, 3000);

  // Refresh conversation list every 15 seconds
  setInterval(function () {
    loadConversations();
  }, 15000);

})();
