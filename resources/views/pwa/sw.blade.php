/* Service worker for the installable admin app. Served by PwaController@serviceWorker. */
const BASE = @json(rtrim(url('/'), '/'));
const APP_ICON = @json(\App\Services\PwaIcons::url('icon-192.png'));

const OFFLINE_HTML = `<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#2563eb"><title>Offline</title>
<style>
  body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;color:#334155;text-align:center;padding:24px;box-sizing:border-box}
  .i{width:64px;height:64px;border-radius:18px;background:#2563eb;display:grid;place-items:center;margin:0 auto 16px}
  h1{font-size:18px;margin:0 0 6px;color:#0f172a} p{font-size:14px;margin:0 0 20px;color:#64748b}
  button{border:0;border-radius:12px;background:#2563eb;color:#fff;font-size:15px;font-weight:600;padding:12px 22px}
</style></head><body><div>
  <div class="i"><svg width="32" height="32" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg></div>
  <h1>You're offline</h1><p>Check your internet connection and try again.</p>
  <button onclick="location.reload()">Try again</button>
</div></body></html>`;

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => {
    // No page caching at all: clear anything an older version may have stored.
    event.waitUntil((async () => {
        for (const key of await caches.keys()) await caches.delete(key);
        await self.clients.claim();
    })());
});

// Pages always come fresh from the server. Only if the network is down do we
// show the offline screen. Everything else (assets, AJAX) is left to the browser.
self.addEventListener('fetch', (event) => {
    if (event.request.mode !== 'navigate') return;
    const req = event.request;
    const offline = () => new Response(OFFLINE_HTML, { headers: { 'Content-Type': 'text/html; charset=utf-8' } });
    event.respondWith(
        fetch(req).catch(async () => {
            // One brief network blip shouldn't show "offline": retry page loads once.
            // (Never retry a POST - that could submit a form twice.)
            if (req.method !== 'GET') return offline();
            await new Promise((r) => setTimeout(r, 800));
            return fetch(req.url, { credentials: 'include', cache: 'no-store' }).catch(offline);
        })
    );
});

// ── Push: a new message / chat / ticket arrived ──
self.addEventListener('push', (event) => {
    let data = {};
    try { data = event.data ? event.data.json() : {}; }
    catch (e) { data = { title: 'New message', body: event.data ? event.data.text() : '' }; }

    const title = data.title || 'New message';
    const options = {
        body: data.body || '',
        icon: APP_ICON,
        badge: BASE + '/pwa/badge-96.png',
        tag: data.tag || 'tbp-message',
        renotify: true,                 // ring again even if a notification with this tag is showing
        silent: false,                  // use the phone's notification sound
        vibrate: [250, 120, 250, 120, 250],
        timestamp: Date.now(),
        data: { url: data.url || (BASE + '/admin/dashboard') },
    };

    event.waitUntil((async () => {
        await self.registration.showNotification(title, options);
        // Let any open app window play its in-app bell too.
        const wins = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        wins.forEach((c) => c.postMessage({ type: 'tbp-push', payload: data }));
    })());
});

// Tap on a notification -> open (or focus) the app on that chat.
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const target = (event.notification.data && event.notification.data.url) || (BASE + '/admin/dashboard');

    event.waitUntil((async () => {
        const wins = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        for (const c of wins) {
            if (c.url.startsWith(BASE + '/admin')) {
                await c.focus();
                if ('navigate' in c) return c.navigate(target);
                return;
            }
        }
        return self.clients.openWindow(target);
    })());
});
