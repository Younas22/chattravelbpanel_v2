@extends('admin.layouts.app')
@section('title', 'Countries')

@section('content')
@php
    $fetchUrl   = route('admin.countries.fetch-holidays', ['country' => '__ID__']);
    $holidaysUrl = route('admin.countries.holidays', ['country' => '__ID__']);
@endphp

<div class="space-y-4" x-data="countriesPage()"
    @keydown.escape.window="drawerOpen = false; daysModalOpen = false">

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $countries->count() }} countries in database</p>
        <div class="flex items-center gap-2">
            {{-- Days by country modal button --}}
            <button @click="daysModalOpen = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium transition-colors cursor-pointer shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                View by Days
            </button>
            {{-- Holidays drawer --}}
            <button @click="drawerOpen = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors cursor-pointer shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                View Holidays
            </button>
        </div>
    </div>

    {{-- Countries Table --}}
    <div class="card !p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Countries List</h2>
            <span class="text-xs text-slate-400">Select year then click "Fetch Holidays" to import from API</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr class="text-left text-xs text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3 font-medium">#</th>
                        <th class="px-5 py-3 font-medium">Country</th>
                        <th class="px-5 py-3 font-medium">ISO2</th>
                        <th class="px-5 py-3 font-medium">ISO3</th>
                        <th class="px-5 py-3 font-medium">Holidays Saved</th>
                        <th class="px-5 py-3 font-medium">Year</th>
                        <th class="px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($countries as $country)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5 text-slate-400 text-xs">{{ $country->id }}</td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-800">{{ $country->name }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600 font-mono text-xs">{{ $country->iso2 }}</td>
                        <td class="px-5 py-3.5 text-slate-600 font-mono text-xs">{{ $country->iso3 }}</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $country->holidays_count > 0 ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}"
                                id="count-{{ $country->id }}">
                                {{ $country->holidays_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <input type="number" value="{{ date('Y') }}" min="2020" max="2030"
                                id="year-{{ $country->id }}"
                                class="w-20 text-xs border border-slate-200 rounded-lg px-2 py-1 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </td>
                        <td class="px-5 py-3.5 flex items-center gap-2">
                            <button
                                @click="fetchHolidays({{ $country->id }}, '{{ addslashes($country->name) }}')"
                                :disabled="loading[{{ $country->id }}]"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                                <svg x-show="!loading[{{ $country->id }}]" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <svg x-show="loading[{{ $country->id }}]" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                                <span x-text="loading[{{ $country->id }}] ? 'Fetching...' : 'Fetch Holidays'"></span>
                            </button>

                            @if($country->holidays_count > 0)
                            <button
                                @click="openDrawerFor({{ $country->id }}, '{{ addslashes($country->name) }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-medium transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                View
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400 text-sm">No countries found in database.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" x-transition
        :class="toast.error ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'"
        class="fixed bottom-6 right-6 z-50 border rounded-xl px-4 py-3 text-sm shadow-lg flex items-center gap-2 max-w-sm"
        style="display:none">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path x-show="!toast.error" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            <path x-show="toast.error" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span x-text="toast.message"></span>
    </div>

    {{-- ===================== Days Modal ===================== --}}
    <div x-show="daysModalOpen"
        x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
        @click.self="daysModalOpen = false"
        style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col"
            @click.stop>
            {{-- Modal header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h2 class="font-semibold text-slate-900">Holidays by Day of Week</h2>
                </div>
                <button @click="daysModalOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal filters --}}
            <div class="px-6 py-3 border-b border-slate-100 flex items-end gap-3 shrink-0">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Country</label>
                    <select x-model="daysCountryId" @change="loadDaysHolidays()"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                        <option value="">— Select country —</option>
                        @foreach($countries->where('holidays_count', '>', 0) as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-28">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Year</label>
                    <select x-model="daysYear" @change="loadDaysHolidays()"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                        @for($y = 2024; $y <= 2030; $y++)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            {{-- Modal body --}}
            <div class="flex-1 overflow-y-auto px-6 py-4">
                {{-- loading --}}
                <div x-show="daysLoading" class="flex justify-center py-10">
                    <svg class="w-6 h-6 animate-spin text-amber-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                {{-- no country --}}
                <div x-show="!daysLoading && !daysCountryId" class="text-center py-10 text-slate-400 text-sm">
                    Select a country to see holidays grouped by day of week.
                </div>

                {{-- day groups --}}
                <div x-show="!daysLoading && daysCountryId" class="space-y-3">
                    <template x-for="group in dayGroups" :key="group.day">
                        <div x-show="group.holidays.length > 0"
                            class="border border-slate-100 rounded-xl overflow-hidden">
                            {{-- day header --}}
                            <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white"
                                        :class="['bg-red-500','bg-blue-500','bg-violet-500','bg-green-500','bg-orange-500','bg-teal-500','bg-pink-500'][group.dayIndex]"
                                        x-text="group.dayShort"></span>
                                    <span class="text-sm font-semibold text-slate-700" x-text="group.day"></span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"
                                    x-text="group.holidays.length + ' holiday' + (group.holidays.length > 1 ? 's' : '')"></span>
                            </div>
                            {{-- holidays list --}}
                            <div class="divide-y divide-slate-50">
                                <template x-for="h in group.holidays" :key="h.id">
                                    <div class="flex items-center gap-3 px-4 py-2.5">
                                        <span class="text-xs font-mono text-slate-400 w-20 shrink-0" x-text="h.date"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-800 truncate" x-text="h.name"></p>
                                            <p class="text-xs text-slate-400 truncate" x-text="h.local_name"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- empty --}}
                    <div x-show="daysCountryId && !daysLoading && dayGroups.every(g => g.holidays.length === 0)"
                        class="text-center py-10 text-slate-400 text-sm">
                        No holidays found for this country / year.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Backdrop --}}
    <div x-show="drawerOpen" x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click="drawerOpen = false"
        class="fixed inset-0 bg-black/30 z-40 backdrop-blur-sm"
        style="display:none">
    </div>

    {{-- Holidays Drawer --}}
    <div x-show="drawerOpen"
        x-transition:enter="transition-transform duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 w-full sm:w-96 bg-white shadow-2xl z-50 flex flex-col"
        style="display:none">

        {{-- Drawer Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="font-semibold text-slate-900 text-sm">National Holidays</h2>
            </div>
            <button @click="drawerOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Filters --}}
        <div class="px-5 py-3 border-b border-slate-100 space-y-2.5 shrink-0">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Country</label>
                <select x-model="drawerCountryId" @change="loadDrawerHolidays()"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                    <option value="">— Select country —</option>
                    @foreach($countries->where('holidays_count', '>', 0) as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->holidays_count }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Day Filter</label>
                    <select x-model="drawerDayFilter" @change="applyFilters()"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        <option value="">All days</option>
                        <option value="0">Sunday</option>
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Year</label>
                    <select x-model="drawerYear" @change="loadDrawerHolidays()"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        @for($y = 2024; $y <= 2030; $y++)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        {{-- Summary bar --}}
        <div x-show="drawerHolidays.length > 0" class="px-5 py-2 bg-indigo-50 border-b border-indigo-100 shrink-0">
            <p class="text-xs text-indigo-600 font-medium">
                <span x-text="filteredHolidays.length"></span> holidays
                <span x-show="drawerDayFilter !== ''" x-text="' on ' + dayName(drawerDayFilter)"></span>
            </p>
        </div>

        {{-- Holiday List --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Loading --}}
            <div x-show="drawerLoading" class="flex items-center justify-center py-16">
                <svg class="w-6 h-6 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
            </div>

            {{-- Empty state --}}
            <div x-show="!drawerLoading && drawerCountryId && filteredHolidays.length === 0"
                class="flex flex-col items-center justify-center py-16 text-slate-400">
                <svg class="w-10 h-10 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm">No holidays found</p>
            </div>

            {{-- No country selected --}}
            <div x-show="!drawerLoading && !drawerCountryId"
                class="flex flex-col items-center justify-center py-16 text-slate-400">
                <svg class="w-10 h-10 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm">Select a country above</p>
            </div>

            {{-- List --}}
            <div x-show="!drawerLoading && filteredHolidays.length > 0" class="divide-y divide-slate-50">
                <template x-for="(h, i) in filteredHolidays" :key="h.id">
                    <div class="px-5 py-3.5 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start gap-3">
                            {{-- Day badge --}}
                            <div class="shrink-0 text-center w-12">
                                <div class="text-[10px] font-semibold uppercase text-indigo-500" x-text="dayShort(h.date)"></div>
                                <div class="text-lg font-bold text-slate-800 leading-tight" x-text="dayNum(h.date)"></div>
                                <div class="text-[10px] text-slate-400" x-text="monthShort(h.date)"></div>
                            </div>
                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800" x-text="h.name"></p>
                                <p class="text-xs text-slate-400 mt-0.5" x-text="h.local_name"></p>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2" x-text="h.description"></p>
                            </div>
                            {{-- Type tag --}}
                            <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-600 capitalize" x-text="h.type"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const FETCH_URL     = @json($fetchUrl);
const HOLIDAYS_URL  = @json($holidaysUrl);

const DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
const DAYS_SHORT = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

function countriesPage() {
    return {
        loading: {},
        toast: { show: false, message: '', error: false },

        // Days modal
        daysModalOpen: false,
        daysLoading: false,
        daysCountryId: '',
        daysYear: '{{ date("Y") }}',
        dayGroups: DAYS.map((d, i) => ({ day: d, dayShort: DAYS_SHORT[i], dayIndex: i, holidays: [] })),

        loadDaysHolidays() {
            if (!this.daysCountryId) {
                this.dayGroups = DAYS.map((d, i) => ({ day: d, dayShort: DAYS_SHORT[i], dayIndex: i, holidays: [] }));
                return;
            }
            this.daysLoading = true;
            const url = HOLIDAYS_URL.replace('__ID__', this.daysCountryId) + '?year=' + this.daysYear;
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    const groups = DAYS.map((d, i) => ({ day: d, dayShort: DAYS_SHORT[i], dayIndex: i, holidays: [] }));
                    data.forEach(h => {
                        const dayIdx = new Date(h.date).getDay();
                        groups[dayIdx].holidays.push(h);
                    });
                    this.dayGroups = groups;
                })
                .catch(() => this.showToast('Failed to load holidays.', true))
                .finally(() => this.daysLoading = false);
        },

        drawerOpen: false,
        drawerLoading: false,
        drawerCountryId: '',
        drawerYear: '{{ date("Y") }}',
        drawerDayFilter: '',
        drawerHolidays: [],
        filteredHolidays: [],

        showToast(message, error = false) {
            this.toast = { show: true, message, error };
            setTimeout(() => this.toast.show = false, 4000);
        },

        fetchHolidays(countryId, countryName) {
            const yearInput = document.getElementById('year-' + countryId);
            const year = yearInput ? yearInput.value : new Date().getFullYear();

            this.loading = { ...this.loading, [countryId]: true };

            const url = FETCH_URL.replace('__ID__', countryId) + '?year=' + year;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.error) { this.showToast(data.error, true); return; }
                this.showToast(data.message);

                const badge = document.getElementById('count-' + countryId);
                if (badge) {
                    const holidaysUrl = HOLIDAYS_URL.replace('__ID__', countryId);
                    fetch(holidaysUrl)
                        .then(r => r.json())
                        .then(holidays => {
                            badge.textContent = holidays.length;
                            badge.className = 'px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700';
                        });
                }
            })
            .catch(() => this.showToast('Network error. Please try again.', true))
            .finally(() => {
                this.loading = { ...this.loading, [countryId]: false };
            });
        },

        openDrawerFor(countryId, countryName) {
            this.drawerCountryId = String(countryId);
            this.drawerOpen = true;
            this.loadDrawerHolidays();
        },

        loadDrawerHolidays() {
            if (!this.drawerCountryId) { this.drawerHolidays = []; this.filteredHolidays = []; return; }
            this.drawerLoading = true;

            const url = HOLIDAYS_URL.replace('__ID__', this.drawerCountryId) + '?year=' + this.drawerYear;
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    this.drawerHolidays = data;
                    this.applyFilters();
                })
                .catch(() => this.showToast('Failed to load holidays.', true))
                .finally(() => this.drawerLoading = false);
        },

        applyFilters() {
            if (this.drawerDayFilter === '') {
                this.filteredHolidays = this.drawerHolidays;
            } else {
                const dayNum = parseInt(this.drawerDayFilter);
                this.filteredHolidays = this.drawerHolidays.filter(h => {
                    return new Date(h.date).getDay() === dayNum;
                });
            }
        },

        dayName(num) {
            return ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][parseInt(num)] || '';
        },
        dayShort(dateStr) {
            return new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short' });
        },
        dayNum(dateStr) {
            return new Date(dateStr).getDate();
        },
        monthShort(dateStr) {
            return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
        },
    };
}
</script>
@endpush
