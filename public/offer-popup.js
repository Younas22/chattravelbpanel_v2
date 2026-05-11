/**
 * TravelBookingPanel Offer Popup Widget
 * Embed: <script src="YOUR_SITE/offer-popup.js"></script>
 * Works independently from the chat widget.
 */
(function () {
  'use strict';

  var _s = document.currentScript || (function () {
    var scripts = document.getElementsByTagName('script');
    return scripts[scripts.length - 1];
  })();
  var BASE = _s ? new URL(_s.src).origin + new URL(_s.src).pathname.replace(/\/offer-popup\.js(\?.*)?$/, '') : '';
  var OFFER_API   = BASE + '/api/chat/offer';
  var STORAGE_KEY = 'tbp_offer_closed';
  var SESSION_KEY = 'tbp_chat_session';
  var DEMO_URL    = 'https://travelbookingpanel.com/demo';
  var CONTACT_URL = 'https://travelbookingpanel.com/contact';
  var countdownInterval = null;

  function fmt2(n) { return String(n).padStart(2, '0'); }
  function secondsUntil(iso) { return Math.max(0, Math.floor((new Date(iso) - Date.now()) / 1000)); }
  function renderCountdown(secs) {
    return fmt2(Math.floor(secs / 3600)) + ':' + fmt2(Math.floor((secs % 3600) / 60)) + ':' + fmt2(secs % 60);
  }

  function buildPopup(data) {
    var savePct = data.original_price > 0
      ? Math.round(((data.original_price - data.discount_price) / data.original_price) * 100) : 0;
    var title = data.holiday ? data.holiday.name : (data.label || 'Special Offer');
    var sub   = data.holiday ? (data.holiday.local_name || '') : '';
    var remaining = secondsUntil(data.countdown_to);

    if (!document.getElementById('tbp-offer-style')) {
      var st = document.createElement('style');
      st.id = 'tbp-offer-style';
      st.textContent = [
        '@keyframes tbpSlideInLeft{from{transform:translateY(-50%) translateX(-110%)}to{transform:translateY(-50%) translateX(0)}}',
        '#tbp-offer-wrap{position:fixed;left:0;top:50%;transform:translateY(-50%);z-index:2147483647;font-family:Inter,system-ui,sans-serif;animation:tbpSlideInLeft .4s cubic-bezier(.34,1.56,.64,1) both}',
        '#tbp-offer-card{width:285px;background:#fff;border-radius:0 20px 20px 0;box-shadow:4px 8px 40px rgba(0,0,0,.18);overflow:hidden}',
        '.tbp-oh{background:linear-gradient(135deg,#f97316,#ef4444);padding:14px 14px 10px;position:relative}',
        '.tbp-oh-close{position:absolute;top:8px;right:8px;background:rgba(255,255,255,.25);border:none;border-radius:50%;width:24px;height:24px;cursor:pointer;color:#fff;font-size:14px;display:flex;align-items:center;justify-content:center;transition:.15s;line-height:1}',
        '.tbp-oh-close:hover{background:rgba(255,255,255,.45);transform:scale(1.1)}',
        '.tbp-oh-tag{display:inline-block;background:rgba(255,255,255,.2);color:#fff;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:2px 8px;border-radius:20px;margin-bottom:6px}',
        '.tbp-oh-title{color:#fff;font-size:16px;font-weight:700;line-height:1.3;margin-bottom:2px;padding-right:28px}',
        '.tbp-oh-sub{color:rgba(255,255,255,.78);font-size:11px;margin-top:2px}',
        '.tbp-ob{padding:13px 14px}',
        '.tbp-prices{display:flex;align-items:baseline;gap:8px;margin-bottom:6px}',
        '.tbp-price-new{font-size:28px;font-weight:800;color:#0f172a;line-height:1}',
        '.tbp-price-old{font-size:14px;color:#94a3b8;text-decoration:line-through}',
        '.tbp-save-badge{display:inline-block;background:#fef2f2;color:#ef4444;font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;margin-bottom:10px}',
        '.tbp-cdown-wrap{background:#f8fafc;border-radius:12px;padding:9px 10px;margin-bottom:11px;text-align:center}',
        '.tbp-cdown-label{font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}',
        '.tbp-cdown{font-size:24px;font-weight:800;color:#0f172a;letter-spacing:.06em;font-variant-numeric:tabular-nums}',
        '.tbp-actions{display:flex;gap:8px}',
        '.tbp-btn-demo{flex:1;padding:9px 0;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;color:#374151;font-size:12px;font-weight:600;cursor:pointer;transition:.15s;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center}',
        '.tbp-btn-demo:hover{border-color:#f97316;color:#f97316;background:#fff7ed}',
        '.tbp-btn-contact{flex:1;padding:9px 0;border-radius:10px;border:none;background:linear-gradient(135deg,#f97316,#ef4444);color:#fff;font-size:12px;font-weight:600;cursor:pointer;transition:.15s;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center}',
        '.tbp-btn-contact:hover{opacity:.85}'
      ].join('');
      document.head.appendChild(st);
    }

    var wrap = document.createElement('div');
    wrap.id = 'tbp-offer-wrap';

    var saveHtml = savePct > 0 ? '<div class="tbp-save-badge">' + savePct + '% OFF &mdash; Save $' + (data.original_price - data.discount_price).toFixed(2) + '</div>' : '';
    var oldHtml  = data.original_price > data.discount_price ? '<span class="tbp-price-old">$' + data.original_price.toFixed(2) + '</span>' : '';
    var tagHtml  = savePct > 0 ? '<div class="tbp-oh-tag">Limited Offer</div>' : '';
    var subHtml  = sub ? '<div class="tbp-oh-sub">' + sub + '</div>' : '';

    wrap.innerHTML =
      '<div id="tbp-offer-card">' +
        '<div class="tbp-oh">' +
          '<button class="tbp-oh-close" id="tbp-offer-close" title="Close">&#10005;</button>' +
          tagHtml +
          '<div class="tbp-oh-title">' + title + '</div>' +
          subHtml +
        '</div>' +
        '<div class="tbp-ob">' +
          '<div class="tbp-prices">' +
            '<span class="tbp-price-new">$' + data.discount_price.toFixed(2) + '</span>' +
            oldHtml +
          '</div>' +
          saveHtml +
          '<div class="tbp-cdown-wrap">' +
            '<div class="tbp-cdown-label">Offer ends in</div>' +
            '<div class="tbp-cdown" id="tbp-offer-timer">' + renderCountdown(remaining) + '</div>' +
          '</div>' +
          '<div class="tbp-actions">' +
            '<a href="' + DEMO_URL + '" target="_blank" rel="noopener" class="tbp-btn-demo">Demo</a>' +
            '<a href="' + CONTACT_URL + '" target="_blank" rel="noopener" class="tbp-btn-contact">Contact Us</a>' +
          '</div>' +
        '</div>' +
      '</div>';

    document.body.appendChild(wrap);

    countdownInterval = setInterval(function () {
      remaining = secondsUntil(data.countdown_to);
      var el = document.getElementById('tbp-offer-timer');
      if (el) el.textContent = renderCountdown(remaining);
      if (remaining <= 0) clearInterval(countdownInterval);
    }, 1000);

    document.getElementById('tbp-offer-close').addEventListener('click', function () {
      clearInterval(countdownInterval);
      wrap.style.transition = 'transform .3s,opacity .3s';
      wrap.style.transform = 'translateY(-50%) translateX(-110%)';
      wrap.style.opacity = '0';
      setTimeout(function () { if (wrap.parentNode) wrap.parentNode.removeChild(wrap); }, 320);
      sessionStorage.setItem(STORAGE_KEY, '1');
    });
  }

  function init() {
    if (sessionStorage.getItem(STORAGE_KEY)) return;
    var sessionId = localStorage.getItem(SESSION_KEY) || '';
    fetch(OFFER_API + (sessionId ? '?session_id=' + encodeURIComponent(sessionId) : ''))
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d.active) buildPopup(d); })
      .catch(function () {});
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { setTimeout(init, 1500); });
  } else {
    setTimeout(init, 1500);
  }
})();