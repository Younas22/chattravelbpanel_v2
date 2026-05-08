/**
 * TravelBookingPanel Chat Widget
 * Embed: <script src="https://chat.travelbookingpanel.com/widget.js"></script>
 */
(function () {
  'use strict';

  const BASE_URL = (function () {
    const scripts = document.getElementsByTagName('script');
    const src = scripts[scripts.length - 1].src;
    return src.replace('/widget.js', '');
  })();

  const API = BASE_URL + '/api/chat';
  const STORAGE_KEY = 'tbp_chat_session';
  const FONT_URL = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap';

  let settings = {
    primary_color: '#2563eb',
    text_color: '#ffffff',
    position: 'bottom-right',
    border_radius: '16',
    dark_mode: 'false',
    welcome_message: 'Hi! How can we help you today? 👋',
    offline_message: 'We are currently offline. Leave a message!',
    widget_title: 'TravelBookingPanel Support',
    widget_subtitle: 'Typically replies within minutes',
    auto_popup: 'false',
    popup_delay: '5',
    sound_enabled: 'true',
    show_online_status: 'true',
    agent_name: 'Support Team',
    show_branding: 'true',
  };

  let state = {
    sessionId: localStorage.getItem(STORAGE_KEY) || null,
    visitorId: null,
    conversationId: null,
    isOpen: false,
    isMinimized: false,
    messages: [],
    lastMessageId: 0,
    typing: false,
    adminTyping: false,
    typingTimer: null,
    pollInterval: null,
    heartbeatInterval: null,
    unread: 0,
    screen: 'home',
    faqs: [],
    selectedFaq: null,
    soundEnabled: true,
    view: 'home',
    inputFocused: false,
    isMobile: window.innerWidth < 640,
  };

  // Shadow DOM root — set during init
  let shadow = null;

  // ─── Shadow DOM query helpers ─────────────────────────────────────────────
  function $id(id) { return shadow ? shadow.getElementById(id) : null; }
  function $all(sel) { return shadow ? shadow.querySelectorAll(sel) : []; }

  // ─── Load Font ────────────────────────────────────────────────────────────
  function loadFont() {
    if (!document.getElementById('tbp-font')) {
      const link = document.createElement('link');
      link.id = 'tbp-font';
      link.rel = 'stylesheet';
      link.href = FONT_URL;
      document.head.appendChild(link);
    }
    // Also inject inside shadow so inherited font works reliably
    if (shadow && !shadow.getElementById('tbp-font-shadow')) {
      const link = document.createElement('link');
      link.id = 'tbp-font-shadow';
      link.rel = 'stylesheet';
      link.href = FONT_URL;
      shadow.appendChild(link);
    }
  }

  // ─── Inject CSS into Shadow DOM ───────────────────────────────────────────
  function injectStyles() {
    const dark = settings.dark_mode === 'true';
    const r = settings.border_radius + 'px';
    const p = settings.primary_color;
    const t = settings.text_color;

    const css = `
      /* Reset — safe inside shadow DOM, won't leak out */
      * { box-sizing: border-box; font-family: 'Inter', sans-serif; margin: 0; padding: 0; }

      #tbp-widget { position: fixed; z-index: 999999; ${settings.position === 'bottom-right' ? 'bottom: 24px; right: 24px;' : 'bottom: 24px; left: 24px;'} }
      #tbp-btn { width: 56px; height: 56px; border-radius: 50%; background: ${p}; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px ${p}55; transition: transform 0.2s, box-shadow 0.2s; position: relative; }
      #tbp-btn:hover { transform: scale(1.08); box-shadow: 0 8px 28px ${p}66; }
      #tbp-btn svg { width: 24px; height: 24px; color: ${t}; transition: transform 0.3s; }
      #tbp-unread { position: absolute; top: -4px; right: -4px; background: #ef4444; color: white; font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; }
      #tbp-window { position: absolute; ${settings.position === 'bottom-right' ? 'bottom: 68px; right: 0;' : 'bottom: 68px; left: 0;'} width: ${state.isMobile ? '100vw' : '380px'}; ${state.isMobile ? 'position: fixed; bottom: 0; right: 0; left: 0; height: 100dvh; border-radius: 0;' : 'max-height: 600px; border-radius: ' + r + ';'} background: ${dark ? '#1e293b' : '#fff'}; box-shadow: 0 20px 60px rgba(0,0,0,0.18); display: flex; flex-direction: column; overflow: hidden; transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1); transform-origin: bottom right; }
      #tbp-window.hidden { transform: scale(0.8); opacity: 0; pointer-events: none; }
      #tbp-header { background: ${p}; padding: 14px 16px 12px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
      #tbp-header-info { display: flex; align-items: center; gap: 10px; }
      #tbp-avatar { width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-weight: 700; color: ${t}; font-size: 15px; flex-shrink: 0; }
      #tbp-header h3 { color: ${t}; font-size: 14px; font-weight: 600; }
      #tbp-header p { color: ${t}; font-size: 11px; opacity: 0.8; margin-top: 1px; }
      #tbp-close { background: rgba(255,255,255,0.2); border: none; border-radius: 8px; width: 28px; height: 28px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: ${t}; transition: background 0.2s; flex-shrink: 0; }
      #tbp-close:hover { background: rgba(255,255,255,0.3); }
      #tbp-body { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }
      .tbp-screen { flex: 1; display: flex; flex-direction: column; }

      /* Home screen */
      .tbp-home { padding: 20px 16px; }
      .tbp-welcome-bubble { background: ${dark ? '#334155' : '#f1f5f9'}; border-radius: 16px; border-top-left-radius: 4px; padding: 12px 14px; font-size: 13px; color: ${dark ? '#e2e8f0' : '#374151'}; line-height: 1.5; margin-bottom: 16px; }
      .tbp-faq-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: ${dark ? '#94a3b8' : '#9ca3af'}; margin-bottom: 8px; }
      .tbp-faq-btn { width: 100%; text-align: left; padding: 10px 14px; border-radius: 12px; border: 1px solid ${dark ? '#334155' : '#e5e7eb'}; background: ${dark ? '#1e293b' : '#fff'}; cursor: pointer; font-size: 13px; color: ${dark ? '#e2e8f0' : '#374151'}; transition: all 0.15s; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between; }
      .tbp-faq-btn:hover { border-color: ${p}; background: ${p}11; }
      .tbp-faq-btn svg { width: 14px; height: 14px; color: ${p}; flex-shrink: 0; }
      .tbp-start-chat { width: 100%; padding: 11px; border-radius: 12px; border: none; background: ${p}; color: ${t}; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; margin-top: 12px; display: flex; align-items: center; justify-content: center; gap: 6px; }
      .tbp-start-chat:hover { opacity: 0.9; }

      /* FAQ answer */
      .tbp-faq-answer { padding: 16px; flex: 1; }
      .tbp-back { background: none; border: none; font-size: 13px; color: ${p}; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 0; margin-bottom: 12px; font-weight: 500; }
      .tbp-faq-q { font-weight: 600; font-size: 15px; color: ${dark ? '#f1f5f9' : '#111827'}; margin-bottom: 10px; }
      .tbp-faq-a { font-size: 13px; color: ${dark ? '#94a3b8' : '#6b7280'}; line-height: 1.6; white-space: pre-wrap; }

      /* Chat screen */
      #tbp-messages { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; }
      .tbp-msg { max-width: 68%; display: flex; flex-direction: column; }
      .tbp-msg.admin { align-self: flex-start; }
      .tbp-msg.visitor { align-self: flex-end; }
      .tbp-bubble { padding: 8px 12px; border-radius: 14px; font-size: 12.5px; line-height: 1.45; word-break: break-word; overflow-wrap: break-word; }
      .tbp-bubble.admin { background: ${dark ? '#334155' : '#f1f5f9'}; color: ${dark ? '#e2e8f0' : '#374151'}; border-bottom-left-radius: 4px; }
      .tbp-bubble.visitor { background: ${p}; color: ${t}; border-bottom-right-radius: 4px; }
      .tbp-bubble.bot { background: ${dark ? '#1e293b' : '#fefce8'}; color: ${dark ? '#fde68a' : '#854d0e'}; border: 1px solid ${dark ? '#334155' : '#fef08a'}; border-bottom-left-radius: 4px; }
      .tbp-time { font-size: 10px; color: ${dark ? '#64748b' : '#9ca3af'}; margin-top: 3px; }
      .tbp-msg.visitor .tbp-time { text-align: right; }
      .tbp-img-attach { max-width: 220px; border-radius: 12px; cursor: pointer; object-fit: cover; }
      .tbp-file-attach { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: ${dark ? '#1e293b' : '#f8fafc'}; border: 1px solid ${dark ? '#334155' : '#e5e7eb'}; border-radius: 12px; text-decoration: none; color: ${dark ? '#e2e8f0' : '#374151'}; font-size: 12px; }

      /* Typing indicator */
      #tbp-typing-indicator { display: flex; align-items: center; gap: 4px; padding: 10px 14px; background: ${dark ? '#334155' : '#f1f5f9'}; border-radius: 16px; border-bottom-left-radius: 4px; width: fit-content; align-self: flex-start; }
      #tbp-typing-indicator span { width: 7px; height: 7px; background: ${dark ? '#94a3b8' : '#9ca3af'}; border-radius: 50%; animation: tbp-bounce 1.2s infinite; }
      #tbp-typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
      #tbp-typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
      @keyframes tbp-bounce { 0%,80%,100%{transform:translateY(0)} 40%{transform:translateY(-6px)} }

      /* Emoji picker */
      #tbp-emoji-btn { width: 36px; height: 36px; border-radius: 10px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: ${dark ? '#334155' : '#f1f5f9'}; color: ${dark ? '#94a3b8' : '#6b7280'}; font-size: 18px; transition: all 0.15s; }
      #tbp-emoji-btn:hover { background: ${dark ? '#475569' : '#e5e7eb'}; }
      #tbp-emoji-panel { position: absolute; bottom: 60px; ${settings.position === 'bottom-right' ? 'right: 12px;' : 'left: 12px;'} background: ${dark ? '#1e293b' : '#fff'}; border: 1px solid ${dark ? '#334155' : '#e5e7eb'}; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); padding: 8px; width: 280px; z-index: 10; }
      #tbp-emoji-panel.hidden { display: none; }
      .tbp-emoji-cats { display: flex; gap: 4px; margin-bottom: 6px; border-bottom: 1px solid ${dark ? '#334155' : '#f1f5f9'}; padding-bottom: 6px; }
      .tbp-emoji-cat-btn { background: none; border: none; cursor: pointer; font-size: 16px; padding: 4px 6px; border-radius: 6px; opacity: 0.6; transition: all 0.15s; }
      .tbp-emoji-cat-btn:hover, .tbp-emoji-cat-btn.active { opacity: 1; background: ${dark ? '#334155' : '#f1f5f9'}; }
      .tbp-emoji-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 2px; max-height: 160px; overflow-y: auto; }
      .tbp-emoji-item { background: none; border: none; cursor: pointer; font-size: 20px; padding: 4px; border-radius: 6px; text-align: center; transition: background 0.1s; line-height: 1; }
      .tbp-emoji-item:hover { background: ${dark ? '#334155' : '#f1f5f9'}; }

      /* Input area */
      #tbp-input-area { padding: 10px 12px; border-top: 1px solid ${dark ? '#334155' : '#f1f5f9'}; display: flex; align-items: flex-end; gap: 8px; flex-shrink: 0; background: ${dark ? '#1e293b' : '#fff'}; position: relative; }
      #tbp-file-preview { padding: 8px 12px; background: ${dark ? '#334155' : '#f8fafc'}; border-top: 1px solid ${dark ? '#475569' : '#e5e7eb'}; font-size: 12px; color: ${dark ? '#94a3b8' : '#6b7280'}; display: flex; align-items: center; justify-content: space-between; }
      #tbp-file-preview button { background: none; border: none; cursor: pointer; color: #ef4444; font-size: 16px; line-height: 1; padding: 0 2px; }
      #tbp-textarea { flex: 1; resize: none; border: 1px solid ${dark ? '#475569' : '#e5e7eb'}; border-radius: 12px; padding: 9px 12px; font-size: 13px; font-family: 'Inter', sans-serif; outline: none; max-height: 100px; line-height: 1.4; color: ${dark ? '#f1f5f9' : '#111827'}; background: ${dark ? '#0f172a' : '#fff'}; transition: border-color 0.15s; }
      #tbp-textarea:focus { border-color: ${p}; }
      #tbp-textarea::placeholder { color: ${dark ? '#64748b' : '#9ca3af'}; }
      #tbp-attach-btn, #tbp-send-btn { width: 36px; height: 36px; border-radius: 10px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; }
      #tbp-attach-btn { background: ${dark ? '#334155' : '#f1f5f9'}; color: ${dark ? '#94a3b8' : '#6b7280'}; }
      #tbp-attach-btn:hover { background: ${dark ? '#475569' : '#e5e7eb'}; }
      #tbp-send-btn { background: ${p}; color: ${t}; }
      #tbp-send-btn:hover { opacity: 0.9; }
      #tbp-send-btn:disabled { opacity: 0.5; cursor: not-allowed; }

      /* Branding */
      .tbp-branding { text-align: center; font-size: 11px; color: ${dark ? '#475569' : '#d1d5db'}; padding: 6px; }
      .tbp-branding a { color: inherit; text-decoration: none; }
      .tbp-branding a:hover { color: ${p}; }

      /* Scrollbar */
      #tbp-messages::-webkit-scrollbar { width: 4px; }
      #tbp-messages::-webkit-scrollbar-track { background: transparent; }
      #tbp-messages::-webkit-scrollbar-thumb { background: ${dark ? '#475569' : '#e2e8f0'}; border-radius: 4px; }
    `;

    let style = shadow.getElementById('tbp-styles');
    if (!style) {
      style = document.createElement('style');
      style.id = 'tbp-styles';
      shadow.appendChild(style);
    }
    style.textContent = css;
  }

  // ─── Render Widget HTML ───────────────────────────────────────────────────
  function render() {
    let widget = shadow.getElementById('tbp-widget');
    if (!widget) {
      widget = document.createElement('div');
      widget.id = 'tbp-widget';
      shadow.appendChild(widget);
    }

    widget.innerHTML = `
      <div id="tbp-window" class="${state.isOpen ? '' : 'hidden'}">
        <div id="tbp-header">
          <div id="tbp-header-info">
            <div id="tbp-avatar">${settings.agent_name.charAt(0)}</div>
            <div>
              <h3>${settings.widget_title}</h3>
              <p>
                ${settings.show_online_status === 'true' ? '<span style="display:inline-flex;align-items:center;gap:4px;"><span style="width:7px;height:7px;background:#4ade80;border-radius:50%;"></span> Online</span>' : settings.widget_subtitle}
              </p>
            </div>
          </div>
          <button id="tbp-close">${iconX()}</button>
        </div>

        <div id="tbp-body">
          ${state.view === 'home' ? renderHome() : renderChat()}
        </div>
      </div>

      <button id="tbp-btn">
        ${state.isOpen ? iconX() : iconChat()}
        ${state.unread > 0 && !state.isOpen ? `<span id="tbp-unread">${state.unread > 9 ? '9+' : state.unread}</span>` : ''}
      </button>
    `;

    bindEvents();

    if (state.view === 'chat' && state.isOpen) {
      scrollBottom();
    }
  }

  function renderHome() {
    if (state.selectedFaq) {
      return `<div class="tbp-screen tbp-faq-answer">
        <button class="tbp-back" id="tbp-back">← Back</button>
        <p class="tbp-faq-q">${esc(state.selectedFaq.question)}</p>
        <p class="tbp-faq-a">${esc(state.selectedFaq.answer)}</p>
        ${state.selectedFaq.show_chat_button ? `<button class="tbp-start-chat" id="tbp-start-from-faq" style="margin-top:16px">${iconChat()} Chat with us</button>` : ''}
      </div>`;
    }

    const faqHtml = state.faqs.map(f => `
      <button class="tbp-faq-btn" data-faq-id="${f.id}">
        <span>${esc(f.question)}</span>
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      </button>
    `).join('');

    return `<div class="tbp-screen tbp-home">
      <div class="tbp-welcome-bubble">${esc(settings.welcome_message)}</div>
      ${state.faqs.length > 0 ? `<p class="tbp-faq-title">Quick answers</p>${faqHtml}` : ''}
      <button class="tbp-start-chat" id="tbp-start-chat">${iconChat()} Start a conversation</button>
      ${settings.show_branding === 'true' ? '<div class="tbp-branding">Powered by <a href="https://travelbookingpanel.com" target="_blank">TravelBookingPanel</a></div>' : ''}
    </div>`;
  }

  function renderChat() {
    const msgs = state.messages.map(m => renderMessage(m)).join('');
    return `<div class="tbp-screen" style="display:flex;flex-direction:column;height:100%">
      <div id="tbp-messages">
        ${msgs}
        <div id="tbp-typing-indicator" style="display:none">
          <span></span><span></span><span></span>
        </div>
      </div>
      <div id="tbp-file-preview" style="display:none">
        <span id="tbp-file-name"></span>
        <button id="tbp-file-clear">×</button>
      </div>
      <div id="tbp-input-area">
        <textarea id="tbp-textarea" rows="1" placeholder="Type a message…"></textarea>
        <input type="file" id="tbp-file-input" style="display:none" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xml,.zip,.mp4,.txt">
        <button id="tbp-emoji-btn" title="Emoji">😊</button>
        <button id="tbp-attach-btn">${iconAttach()}</button>
        <button id="tbp-send-btn">${iconSend()}</button>
      </div>
      <div id="tbp-emoji-panel" class="hidden">
        <div class="tbp-emoji-cats">
          <button class="tbp-emoji-cat-btn active" data-cat="smileys">😊</button>
          <button class="tbp-emoji-cat-btn" data-cat="gestures">👍</button>
          <button class="tbp-emoji-cat-btn" data-cat="travel">✈️</button>
          <button class="tbp-emoji-cat-btn" data-cat="objects">💼</button>
          <button class="tbp-emoji-cat-btn" data-cat="symbols">❤️</button>
        </div>
        <div class="tbp-emoji-grid" id="tbp-emoji-grid"></div>
      </div>
      ${settings.show_branding === 'true' ? '<div class="tbp-branding">Powered by <a href="https://travelbookingpanel.com" target="_blank">TravelBookingPanel</a></div>' : ''}
    </div>`;
  }

  function renderMessage(m) {
    const side = m.sender_type === 'visitor' ? 'visitor' : 'admin';
    const cls = m.sender_type === 'visitor' ? 'visitor' : (m.sender_type === 'bot' ? 'bot' : 'admin');
    const time = new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    let content = '';
    if (m.body) content += `<div class="tbp-bubble ${cls}">${esc(m.body).replace(/\n/g, '<br>')}</div>`;
    if (m.attachment_url) {
      if (m.attachment_type === 'image') {
        content += `<img src="${m.attachment_url}" class="tbp-img-attach" onclick="window.open('${m.attachment_url}','_blank')">`;
      } else {
        content += `<a href="${m.attachment_url}" target="_blank" class="tbp-file-attach">${iconFile()} ${esc(m.attachment_name || 'Attachment')}</a>`;
      }
    }

    return `<div class="tbp-msg ${side}">
      ${content}
      <span class="tbp-time">${time}</span>
    </div>`;
  }

  // ─── Events ───────────────────────────────────────────────────────────────
  function bindEvents() {
    $id('tbp-btn')?.addEventListener('click', toggleWidget);
    $id('tbp-close')?.addEventListener('click', closeWidget);
    $id('tbp-start-chat')?.addEventListener('click', startChat);
    $id('tbp-start-from-faq')?.addEventListener('click', startChat);
    $id('tbp-back')?.addEventListener('click', () => { state.selectedFaq = null; render(); });

    $all('[data-faq-id]').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.faqId);
        state.selectedFaq = state.faqs.find(f => f.id === id);
        render();
      });
    });

    const textarea = $id('tbp-textarea');
    const sendBtn = $id('tbp-send-btn');
    const attachBtn = $id('tbp-attach-btn');
    const fileInput = $id('tbp-file-input');

    if (textarea) {
      textarea.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
      });
      textarea.addEventListener('input', () => {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
        handleTyping();
      });
    }

    sendBtn?.addEventListener('click', sendMessage);
    attachBtn?.addEventListener('click', () => fileInput?.click());
    fileInput?.addEventListener('change', handleFileSelect);
    $id('tbp-file-clear')?.addEventListener('click', clearFile);

    // Emoji picker
    const emojiBtn = $id('tbp-emoji-btn');
    const emojiPanel = $id('tbp-emoji-panel');
    if (emojiBtn && emojiPanel) {
      emojiBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        emojiPanel.classList.toggle('hidden');
        if (!emojiPanel.classList.contains('hidden')) {
          renderEmojiGrid('smileys');
          $all('.tbp-emoji-cat-btn').forEach(b => b.classList.remove('active'));
          emojiPanel.querySelector('[data-cat="smileys"]')?.classList.add('active');
        }
      });

      $all('.tbp-emoji-cat-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          $all('.tbp-emoji-cat-btn').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          renderEmojiGrid(btn.dataset.cat);
        });
      });

      shadow.addEventListener('click', (e) => {
        if (emojiPanel && !emojiPanel.contains(e.target) && e.target !== emojiBtn) {
          emojiPanel.classList.add('hidden');
        }
      });
    }
  }

  const EMOJIS = {
    smileys: ['😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🥸','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡'],
    gestures: ['👍','👎','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👋','🤚','🖐️','✋','🖖','🤝','🙏','✍️','💪','🦵','🦶','👂','🦻','👃','🫀','🫁','🧠','🦷','🦴','👀','👁️','👅','👄'],
    travel: ['✈️','🚀','🛸','🚁','🛶','⛵','🚢','🚂','🚃','🚄','🚅','🚆','🚇','🚈','🚉','🚊','🚝','🚞','🚋','🚌','🚍','🚎','🚐','🚑','🚒','🚓','🚔','🚕','🚖','🚗','🚘','🚙','🛻','🚚','🏖️','🏔️','🗺️','🧭','🏕️','🌍','🌎','🌏','🗼','🗽','🏰','🏯','🎡','🎢'],
    objects: ['💼','💻','📱','⌨️','🖥️','🖨️','🖱️','📷','📸','📹','🎥','📽️','🎬','📞','☎️','📟','📠','📺','📻','🧭','⏱️','⏰','⌚','📡','🔋','🔌','💡','🔦','🕯️','📦','📫','📪','📬','📭','📮','🗳️','✏️','✒️','🖊️','🖋️','📝','📁','📂','🗂️','📅','📆','🗒️','🗓️'],
    symbols: ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','💟','☮️','✝️','☪️','🕉️','✡️','🔯','🕎','☯️','☦️','🛐','⛎','♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓','🆔','⚛️','🉑','☢️','☣️','📴','📳'],
  };

  function renderEmojiGrid(cat) {
    const grid = $id('tbp-emoji-grid');
    if (!grid) return;
    const emojis = EMOJIS[cat] || [];
    grid.innerHTML = emojis.map(e =>
      `<button class="tbp-emoji-item">${e}</button>`
    ).join('');
    grid.querySelectorAll('.tbp-emoji-item').forEach(btn => {
      btn.addEventListener('click', (ev) => {
        ev.stopPropagation();
        const textarea = $id('tbp-textarea');
        if (textarea) {
          const start = textarea.selectionStart;
          const end = textarea.selectionEnd;
          const val = textarea.value;
          textarea.value = val.slice(0, start) + btn.textContent + val.slice(end);
          textarea.selectionStart = textarea.selectionEnd = start + btn.textContent.length;
          textarea.focus();
          textarea.style.height = 'auto';
          textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
        }
        $id('tbp-emoji-panel')?.classList.add('hidden');
      });
    });
  }

  let selectedFile = null;

  function handleFileSelect(e) {
    const file = e.target.files[0];
    if (!file) return;
    selectedFile = file;
    const preview = $id('tbp-file-preview');
    const name = $id('tbp-file-name');
    if (preview) preview.style.display = 'flex';
    if (name) name.textContent = file.name;
  }

  function clearFile() {
    selectedFile = null;
    const fileInput = $id('tbp-file-input');
    if (fileInput) fileInput.value = '';
    const preview = $id('tbp-file-preview');
    if (preview) preview.style.display = 'none';
  }

  // ─── Toggle / Open / Close ────────────────────────────────────────────────
  function toggleWidget() {
    if (state.isOpen) { closeWidget(); } else { openWidget(); }
  }

  function openWidget() {
    state.isOpen = true;
    state.unread = 0;
    render();
    if (state.view === 'chat' && state.conversationId) { startPolling(); }
  }

  function closeWidget() {
    state.isOpen = false;
    stopPolling();
    render();
  }

  // ─── Start Chat ───────────────────────────────────────────────────────────
  function startChat() {
    if (!state.sessionId) {
      identifyVisitor().then(() => startConversation());
    } else {
      startConversation();
    }
  }

  async function startConversation() {
    if (state.conversationId) {
      state.view = 'chat';
      render();
      loadMessages();
      startPolling();
      return;
    }

    const res = await apiFetch('/conversation/start', 'POST', { session_id: state.sessionId });
    if (res.conversation_id) {
      state.conversationId = res.conversation_id;
      state.view = 'chat';
      render();
      state.messages.push({
        id: 'welcome',
        sender_type: 'bot',
        body: settings.welcome_message,
        created_at: new Date().toISOString(),
      });
      render();
      startPolling();
    }
  }

  // ─── Messages ─────────────────────────────────────────────────────────────
  async function loadMessages() {
    if (!state.conversationId) return;
    const res = await apiFetch(`/conversation/${state.conversationId}/messages?session_id=${state.sessionId}&after_id=0`);
    if (res.messages) {
      state.messages = res.messages;
      if (res.messages.length) state.lastMessageId = Math.max(...res.messages.map(m => typeof m.id === 'number' ? m.id : 0));
      render();
      scrollBottom();
    }
  }

  function startPolling() {
    stopPolling();
    if (!state.conversationId) return;
    state.pollInterval = setInterval(pollMessages, 3000);
    state.heartbeatInterval = setInterval(heartbeat, 30000);
  }

  function stopPolling() {
    clearInterval(state.pollInterval);
    clearInterval(state.heartbeatInterval);
  }

  async function pollMessages() {
    if (!state.conversationId || !state.sessionId) return;
    const res = await apiFetch(
      `/conversation/${state.conversationId}/messages?session_id=${state.sessionId}&after_id=${state.lastMessageId}`
    ).catch(() => null);

    if (res?.messages?.length) {
      res.messages.forEach(m => {
        if (!state.messages.find(e => e.id === m.id)) {
          state.messages.push(m);
          if (m.sender_type === 'admin') {
            if (!state.isOpen) { state.unread++; playSound(); }
          }
          state.lastMessageId = Math.max(state.lastMessageId, m.id);
        }
      });
      render();
      scrollBottom();
    }
  }

  async function sendMessage() {
    const textarea = $id('tbp-textarea');
    const body = textarea?.value?.trim();
    if (!body && !selectedFile) return;

    if (!state.conversationId) {
      await startChat();
      return;
    }

    const formData = new FormData();
    formData.append('session_id', state.sessionId);
    if (body) formData.append('body', body);
    if (selectedFile) formData.append('attachment', selectedFile);

    const optimistic = {
      id: 'opt-' + Date.now(),
      sender_type: 'visitor',
      body: body,
      attachment_url: selectedFile ? URL.createObjectURL(selectedFile) : null,
      attachment_name: selectedFile?.name,
      attachment_type: selectedFile ? (selectedFile.type.startsWith('image/') ? 'image' : 'document') : null,
      created_at: new Date().toISOString(),
    };
    state.messages.push(optimistic);
    if (textarea) { textarea.value = ''; textarea.style.height = 'auto'; }
    clearFile();
    render();
    scrollBottom();

    const res = await apiFetchForm(`/conversation/${state.conversationId}/send`, formData).catch(() => null);
    if (res?.id) {
      const idx = state.messages.findIndex(m => m.id === optimistic.id);
      if (idx !== -1) {
        state.messages[idx] = {
          ...optimistic,
          id: res.id,
          created_at: res.created_at,
          attachment_url: res.attachment_url || optimistic.attachment_url,
        };
        state.lastMessageId = Math.max(state.lastMessageId, res.id);
      }
      render();
    }
  }

  // ─── Typing ───────────────────────────────────────────────────────────────
  function handleTyping() {
    if (!state.conversationId) return;
    if (!state.typing) {
      state.typing = true;
      apiFetch(`/conversation/${state.conversationId}/typing`, 'POST', { session_id: state.sessionId, typing: true }).catch(() => {});
    }
    clearTimeout(state.typingTimer);
    state.typingTimer = setTimeout(() => {
      state.typing = false;
      apiFetch(`/conversation/${state.conversationId}/typing`, 'POST', { session_id: state.sessionId, typing: false }).catch(() => {});
    }, 2000);
  }

  // ─── Visitor tracking ─────────────────────────────────────────────────────
  async function identifyVisitor() {
    const res = await apiFetch('/visitor/identify', 'POST', {
      page_url: window.location.href,
      page_title: document.title,
      referrer: document.referrer,
      session_id: state.sessionId,
    }).catch(() => null);

    if (res?.session_id) {
      state.sessionId = res.session_id;
      state.visitorId = res.visitor_id;
      localStorage.setItem(STORAGE_KEY, state.sessionId);
    }
  }

  async function heartbeat() {
    if (!state.sessionId) return;
    apiFetch('/visitor/heartbeat', 'POST', { session_id: state.sessionId }).catch(() => {});
  }

  // ─── API helpers ──────────────────────────────────────────────────────────
  async function apiFetch(path, method = 'GET', body = null) {
    const opts = {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-Visitor-Session': state.sessionId || '',
      },
    };
    if (body && method !== 'GET') opts.body = JSON.stringify(body);
    const res = await fetch(API + path, opts);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
  }

  async function apiFetchForm(path, formData) {
    const res = await fetch(API + path, {
      method: 'POST',
      headers: { 'X-Visitor-Session': state.sessionId || '' },
      body: formData,
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────
  function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function scrollBottom() {
    const msgs = $id('tbp-messages');
    if (msgs) msgs.scrollTop = msgs.scrollHeight;
  }

  function playSound() {
    if (settings.sound_enabled !== 'true' || !state.soundEnabled) return;
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const o = ctx.createOscillator();
      const g = ctx.createGain();
      o.connect(g); g.connect(ctx.destination);
      o.frequency.value = 880;
      g.gain.setValueAtTime(0.2, ctx.currentTime);
      g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
      o.start(); o.stop(ctx.currentTime + 0.25);
    } catch (e) {}
  }

  // ─── Icons ────────────────────────────────────────────────────────────────
  function iconChat() { return `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>`; }
  function iconX() { return `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`; }
  function iconSend() { return `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>`; }
  function iconAttach() { return `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>`; }
  function iconFile() { return `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>`; }

  // ─── Init ─────────────────────────────────────────────────────────────────
  async function init() {
    // Create shadow host and attach Shadow DOM for full CSS isolation
    const host = document.createElement('div');
    host.id = 'tbp-widget-host';
    host.style.cssText = 'all: initial; position: static;';
    document.body.appendChild(host);
    shadow = host.attachShadow({ mode: 'open' });

    loadFont();

    const s = await apiFetch('/settings').catch(() => null);
    if (s) Object.assign(settings, s);

    const faqRes = await apiFetch('/faqs').catch(() => null);
    if (faqRes) state.faqs = faqRes;

    injectStyles();

    await identifyVisitor();

    state.heartbeatInterval = setInterval(heartbeat, 30000);

    window.addEventListener('beforeunload', () => {
      if (state.sessionId) {
        navigator.sendBeacon(API + '/visitor/offline', JSON.stringify({ session_id: state.sessionId }));
      }
    });

    if (settings.auto_popup === 'true') {
      const delay = parseInt(settings.popup_delay) || 5;
      setTimeout(() => { if (!state.isOpen) openWidget(); }, delay * 1000);
    }

    render();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
