{{-- Gear icon + colour theme dropdown. Choice is saved per browser (localStorage). --}}
<div class="relative" x-data="themePicker()" @keydown.escape.window="open = false">
    <button type="button" @click="open = !open" class="icon-btn" :class="open && 'bg-slate-100 text-slate-700'"
            title="Theme" aria-label="Change colour theme" :aria-expanded="open">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         class="absolute right-0 top-full mt-2 z-50 w-64 origin-top-right rounded-2xl bg-white p-4 shadow-xl ring-1 ring-slate-200">
        <p class="text-sm font-semibold text-slate-900">Theme colour</p>
        <p class="mb-3 text-xs text-slate-500">Saved on this browser</p>
        <div class="grid grid-cols-4 gap-2">
            <template x-for="t in themes" :key="t.key">
                <button type="button" @click="set(t.key)" :title="t.name"
                        class="group flex flex-col items-center gap-1 rounded-xl p-1.5 transition-colors hover:bg-slate-50 cursor-pointer">
                    <span class="grid h-9 w-9 place-items-center rounded-full ring-2 ring-offset-2 transition"
                          :style="`background:${t.color}`"
                          :class="current === t.key ? 'ring-slate-900' : 'ring-transparent group-hover:ring-slate-200'">
                        <svg x-show="current === t.key" class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-[10px] font-medium text-slate-600" x-text="t.name"></span>
                </button>
            </template>
        </div>
    </div>
</div>

@once
<script>
    function themePicker() {
        return {
            open: false,
            current: (function () { try { return localStorage.getItem('tbp-theme') || 'blue'; } catch (e) { return 'blue'; } })(),
            themes: [
                { key: 'blue',     name: 'Blue',     color: '#2563eb' },
                { key: 'indigo',   name: 'Indigo',   color: '#4f46e5' },
                { key: 'violet',   name: 'Violet',   color: '#7c3aed' },
                { key: 'emerald',  name: 'Emerald',  color: '#059669' },
                { key: 'teal',     name: 'Teal',     color: '#0d9488' },
                { key: 'rose',     name: 'Rose',     color: '#e11d48' },
                { key: 'orange',   name: 'Orange',   color: '#ea580c' },
                { key: 'graphite', name: 'Graphite', color: '#334155' },
            ],
            set(key) {
                this.current = key;
                if (key === 'blue') document.documentElement.removeAttribute('data-theme');
                else document.documentElement.setAttribute('data-theme', key);
                try { localStorage.setItem('tbp-theme', key); } catch (e) {}
            },
        };
    }
</script>
@endonce
