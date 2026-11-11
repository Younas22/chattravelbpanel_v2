{{-- Admin app: notification opt-in banner, push subscription, sound unlock, freshness on resume --}}
<div x-data="pushOptIn()" x-cloak x-show="show"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     class="fixed inset-x-3 bottom-[max(0.75rem,env(safe-area-inset-bottom))] sm:left-auto sm:right-4 sm:w-96 z-[60] rounded-2xl bg-white p-4 shadow-2xl ring-1 ring-slate-200">
    <div class="flex items-start gap-3">
        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <template x-if="mode === 'ask'">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Get alerts for new messages</p>
                    <p class="mt-0.5 text-xs text-slate-500">Your phone will ring for every new chat, message and ticket — even when the app is closed.</p>
                    <div class="mt-3 flex gap-2">
                        <button type="button" @click="enable()" :disabled="busy" class="btn-primary !py-2 !text-xs">
                            <span x-text="busy ? 'Turning on…' : 'Turn on notifications'"></span>
                        </button>
                        <button type="button" @click="dismiss()" class="btn-secondary !py-2 !text-xs">Later</button>
                    </div>
                </div>
            </template>
            <template x-if="mode === 'ios-install'">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Install the app for alerts</p>
                    <p class="mt-0.5 text-xs text-slate-500">On iPhone, notifications work only from the installed app: tap
                        <svg class="inline h-4 w-4 -mt-0.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        <b>Share</b> → <b>Add to Home Screen</b>, then open it from your home screen.</p>
                    <button type="button" @click="dismiss()" class="btn-secondary !py-2 !text-xs mt-3">Got it</button>
                </div>
            </template>
            <template x-if="mode === 'blocked'">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Notifications are blocked</p>
                    <p class="mt-0.5 text-xs text-slate-500">Allow notifications for this site in your browser / phone settings, then reload.</p>
                    <button type="button" @click="dismiss()" class="btn-secondary !py-2 !text-xs mt-3">OK</button>
                </div>
            </template>
            <template x-if="mode === 'done'">
                <div>
                    <p class="text-sm font-semibold text-green-700">Notifications are on ✓</p>
                    <p class="mt-0.5 text-xs text-slate-500">A test alert was sent to this device.</p>
                </div>
            </template>
        </div>
        <button type="button" @click="dismiss()" class="icon-btn !p-1 -mr-1 -mt-1" aria-label="Close">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>

<script>
window.TBP_PUSH = {
    vapidKey: @json(config('services.webpush.public_key')),
    subscribeUrl: @json(route('admin.push.subscribe')),
    testUrl: @json(route('admin.push.test')),
    csrf: @json(csrf_token()),
};

function tbpUrlB64ToUint8(base64) {
    const pad = '='.repeat((4 - base64.length % 4) % 4);
    const raw = atob((base64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
    return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
}

// Save this device's subscription on the server (also refreshes it on every visit).
// Calls are serialised so two callers can't create two different subscriptions.
let tbpPushChain = Promise.resolve();
function tbpSyncPush(sendTest) {
    const run = tbpPushChain.then(() => tbpSyncPushNow(sendTest));
    tbpPushChain = run.catch(() => {});
    return run;
}
async function tbpSyncPushNow(sendTest) {
    const P = window.TBP_PUSH;
    if (!P.vapidKey || !('serviceWorker' in navigator) || !('PushManager' in window)) return false;
    if (Notification.permission !== 'granted') return false;
    await (window.TBP_PWA && window.TBP_PWA.ready);
    const reg = await navigator.serviceWorker.ready;
    let sub = await reg.pushManager.getSubscription();
    if (!sub) sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: tbpUrlB64ToUint8(P.vapidKey) });
    const json = sub.toJSON();
    json.contentEncoding = (PushManager.supportedContentEncodings || ['aes128gcm'])[0];
    await fetch(P.subscribeUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': P.csrf, 'Accept': 'application/json' }, body: JSON.stringify(json) });
    if (sendTest) fetch(P.testUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': P.csrf, 'Accept': 'application/json' } });
    return true;
}

function pushOptIn() {
    const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const standalone = window.TBP_PWA && window.TBP_PWA.standalone;
    return {
        show: false, mode: 'ask', busy: false,
        init() {
            let dismissed = false;
            try { dismissed = sessionStorage.getItem('tbp-push-dismissed') === '1'; } catch (e) {}
            if (!window.TBP_PUSH.vapidKey) return;                       // server not configured
            const supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
            if (!supported) {
                if (isIOS && !standalone && !dismissed) { this.mode = 'ios-install'; setTimeout(() => this.show = true, 1500); }
                return;
            }
            if (Notification.permission === 'granted') { tbpSyncPush(false).catch(() => {}); return; }
            if (dismissed) return;
            this.mode = Notification.permission === 'denied' ? 'blocked' : 'ask';
            if (this.mode === 'blocked' && !standalone) return;           // don't nag in a normal tab
            setTimeout(() => this.show = true, 1500);
        },
        async enable() {
            this.busy = true;
            try {
                const perm = await Notification.requestPermission();
                if (perm !== 'granted') { this.mode = 'blocked'; return; }
                await tbpSyncPush(true);
                this.mode = 'done';
                setTimeout(() => this.show = false, 3500);
            } catch (e) {
                console.warn(e); this.mode = 'blocked';
            } finally { this.busy = false; }
        },
        dismiss() {
            this.show = false;
            try { sessionStorage.setItem('tbp-push-dismissed', '1'); } catch (e) {}
        },
    };
}

// Mobile browsers block audio until the user has touched the page once:
// play a silent clip on the first tap so later message bells can ring.
(function () {
    const unlock = () => {
        try { const a = new Audio(@json(url('/voice/chat.wav'))); a.volume = 0; a.play().then(() => a.pause()).catch(() => {}); } catch (e) {}
        window.removeEventListener('touchstart', unlock); window.removeEventListener('click', unlock);
    };
    window.addEventListener('touchstart', unlock, { once: true, passive: true });
    window.addEventListener('click', unlock, { once: true });
})();

// A push arrived while the app is open: ring the in-app bell too.
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', (e) => {
        if (e.data && e.data.type === 'tbp-push' && window.tbpRingBell) window.tbpRingBell(e.data.payload);
    });
}

// Installed app: when it comes back after a while, load a fresh page
// (unless the admin is in the middle of typing something).
(function () {
    if (!(window.TBP_PWA && window.TBP_PWA.standalone)) return;
    let hiddenAt = 0;
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) { hiddenAt = Date.now(); return; }
        if (!hiddenAt || Date.now() - hiddenAt < 60 * 1000) return;
        const typing = [...document.querySelectorAll('textarea, input[type=text]')].some(el => el.value.trim() !== '');
        if (!typing) location.reload();
    });
})();
</script>
