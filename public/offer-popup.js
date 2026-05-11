/**
 * TravelBookingPanel Offer Popup Widget
 * Embed: <script src="YOUR_SITE/offer-popup.js"></script>
 */
(function () {
  'use strict';

  var _s = document.currentScript || (function () {
    var scripts = document.getElementsByTagName('script');
    return scripts[scripts.length - 1];
  })();
  var BASE        = _s ? new URL(_s.src).origin + new URL(_s.src).pathname.replace(/\/offer-popup\.js(\?.*)?$/, '') : '';
  var OFFER_API   = BASE + '/api/chat/offer';
  var SESSION_KEY  = 'tbp_chat_session';
  var DEADLINE_KEY = 'tbp_offer_deadline';   // 24-h cycle end timestamp (ms)
  var MINIMIZED_KEY = 'tbp_offer_minimized'; // '1' when user minimized it
  var DEMO_URL    = 'https://travelbookingpanel.com/demo';
  var CONTACT_URL = 'https://travelbookingpanel.com/contact';
  var TWENTY_FOUR_H = 24 * 60 * 60 * 1000;
  var countdownInterval = null;

  function fmt2(n) { return String(n).padStart(2, '0'); }

  function getDeadline() {
    var stored   = parseInt(localStorage.getItem(DEADLINE_KEY) || '0', 10);
    var deadline = (stored && stored > Date.now()) ? stored : Date.now() + TWENTY_FOUR_H;
    if (!stored || stored <= Date.now()) localStorage.setItem(DEADLINE_KEY, String(deadline));
    return deadline;
  }

  function secondsUntil(ms) { return Math.max(0, Math.floor((ms - Date.now()) / 1000)); }

  function renderCountdown(secs) {
    return fmt2(Math.floor(secs / 3600)) + ':' + fmt2(Math.floor((secs % 3600) / 60)) + ':' + fmt2(secs % 60);
  }

  function buildPopup(data) {
    var savePct = data.original_price > 0
      ? Math.round(((data.original_price - data.discount_price) / data.original_price) * 100) : 0;

    // Title: holiday name > label > fallback
    var title = data.holiday ? data.holiday.name : (data.label || 'Special Offer');
    var sub   = data.holiday ? (data.holiday.local_name || '') : '';

    // Tag badge: user label if set, else 'Limited Offer' when discount exists
    var tagText = data.label || (savePct > 0 ? 'Limited Offer' : '');

    var deadline    = getDeadline();
    var remaining   = secondsUntil(deadline);
    var isMinimized = localStorage.getItem(MINIMIZED_KEY) === '1';

    if (!document.getElementById('tbp-offer-style')) {
      var st = document.createElement('style');
      st.id = 'tbp-offer-style';
      st.textContent = [
        '@keyframes tbpSlideIn{from{transform:translateY(-50%) translateX(-110%)}to{transform:translateY(-50%) translateX(0)}}',
        '#tbp-offer-wrap{position:fixed;left:0;top:50%;z-index:2147483647;font-family:Inter,system-ui,sans-serif;display:flex;align-items:center;transition:transform .35s cubic-bezier(.4,0,.2,1)}',
        '#tbp-offer-wrap.tbp-entering{animation:tbpSlideIn .4s cubic-bezier(.34,1.56,.64,1) both}',
        '#tbp-offer-wrap.tbp-minimized{transform:translateY(-50%) translateX(calc(-100% + 28px))}',
        '#tbp-offer-card{width:285px;background:#fff;border-radius:0 20px 20px 0;box-shadow:4px 8px 40px rgba(0,0,0,.18);overflow:hidden;flex-shrink:0}',
        '#tbp-offer-tab{width:28px;height:72px;background:linear-gradient(160deg,#f97316,#ef4444);border-radius:0 10px 10px 0;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;box-shadow:3px 4px 16px rgba(239,68,68,.35);transition:width .15s}',
        '#tbp-offer-tab:hover{width:33px}',
        '.tbp-oh{background:#fff;padding:14px 14px 10px;position:relative;text-align:center;border-bottom:1px solid #f1f5f9}',
        '.tbp-oh-close{position:absolute;top:8px;right:8px;background:rgba(0,0,0,.07);border:none;border-radius:50%;width:24px;height:24px;cursor:pointer;color:#64748b;font-size:14px;display:flex;align-items:center;justify-content:center;transition:.15s;line-height:1}',
        '.tbp-oh-close:hover{background:rgba(0,0,0,.12);transform:scale(1.1)}',
        '.tbp-oh-tag{display:inline-block;background:linear-gradient(135deg,#fff7ed,#fef2f2);color:#f97316;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:3px 10px;border-radius:20px;margin-bottom:7px;border:1px solid #fed7aa}',
        '.tbp-oh-title{color:#0f172a;font-size:16px;font-weight:700;line-height:1.3;margin-bottom:2px;padding-right:0}',
        '.tbp-oh-sub{color:#94a3b8;font-size:11px;margin-top:2px}',
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

    var saveHtml = savePct > 0
      ? '<div class="tbp-save-badge">' + savePct + '% OFF &mdash; Save $' + (data.original_price - data.discount_price).toFixed(2) + '</div>'
      : '';
    var oldHtml  = data.original_price > data.discount_price
      ? '<span class="tbp-price-old">$' + data.original_price.toFixed(2) + '</span>'
      : '';
    var tagHtml  = tagText ? '<div class="tbp-oh-tag">' + tagText + '</div>' : '';
    var subHtml  = sub ? '<div class="tbp-oh-sub">' + sub + '</div>' : '';

    var iconRight = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>';
    var iconLeft  = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>';

    wrap.innerHTML =
      '<div id="tbp-offer-card">' +
        '<div class="tbp-oh">' +
          '<button class="tbp-oh-close" id="tbp-offer-close" title="Hide offer">&#10005;</button>' +
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
      '</div>' +
      '<div id="tbp-offer-tab" title="View Offer">' + iconRight + '</div>';

    if (isMinimized) {
      // Start already minimized — no slide-in animation
      wrap.style.transform = 'translateY(-50%) translateX(calc(-100% + 28px))';
    } else {
      wrap.classList.add('tbp-entering');
      wrap.style.transform = '';
    }

    document.body.appendChild(wrap);

    function updateTab() {
      var tab = document.getElementById('tbp-offer-tab');
      if (!tab) return;
      var minimized = wrap.classList.contains('tbp-minimized') ||
                      wrap.style.transform.indexOf('translateX(calc') !== -1;
      // After initial minimized state, sync to class-driven approach
      tab.innerHTML = minimized ? iconRight : iconLeft;
      tab.title     = minimized ? 'View Offer' : 'Hide';
    }

    updateTab();

    // 24-h countdown tick — auto-resets when deadline passes
    countdownInterval = setInterval(function () {
      var dl  = parseInt(localStorage.getItem(DEADLINE_KEY) || '0', 10);
      if (!dl || dl <= Date.now()) {
        dl = Date.now() + TWENTY_FOUR_H;
        localStorage.setItem(DEADLINE_KEY, String(dl));
      }
      var el = document.getElementById('tbp-offer-timer');
      if (el) el.textContent = renderCountdown(secondsUntil(dl));
    }, 1000);

    // Close → minimize to tab
    document.getElementById('tbp-offer-close').addEventListener('click', function () {
      wrap.style.transform = '';  // let CSS class control from here
      wrap.classList.remove('tbp-entering');
      wrap.classList.add('tbp-minimized');
      localStorage.setItem(MINIMIZED_KEY, '1');
      updateTab();
    });

    // Tab → toggle minimized / expanded
    document.getElementById('tbp-offer-tab').addEventListener('click', function () {
      wrap.style.transform = '';
      wrap.classList.remove('tbp-entering');
      if (wrap.classList.contains('tbp-minimized')) {
        wrap.classList.remove('tbp-minimized');
        localStorage.setItem(MINIMIZED_KEY, '0');
      } else {
        wrap.classList.add('tbp-minimized');
        localStorage.setItem(MINIMIZED_KEY, '1');
      }
      updateTab();
    });

    // Once entering animation ends, hand control fully to CSS transition
    wrap.addEventListener('animationend', function () {
      wrap.classList.remove('tbp-entering');
    });
  }

  function init() {
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
