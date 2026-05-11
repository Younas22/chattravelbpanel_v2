@extends('admin.layouts.app')
@section('title', 'Global Offer')

@section('content')
<div class="max-w-2xl space-y-5" x-data="{
    originalPrice: '{{ old('original_price', $offer?->original_price ?? '') }}',
    discountPrice: '{{ old('discount_price', $offer?->discount_price ?? '') }}',
    isActive: {{ old('is_active', $offer?->is_active ?? true) ? 'true' : 'false' }},
    get savings() {
        const o = parseFloat(this.originalPrice) || 0;
        const d = parseFloat(this.discountPrice) || 0;
        if (!o || !d || d >= o) return null;
        return { amount: (o - d).toFixed(2), pct: Math.round(((o - d) / o) * 100) };
    }
}">

    {{-- Page header --}}
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <div>
            <h1 class="font-semibold text-slate-900">Global Offer</h1>
            <p class="text-xs text-slate-400 mt-0.5">Set the sitewide pricing offer shown to customers</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="card">
        <form method="POST" action="{{ route('admin.offers.save') }}">
            @csrf

            {{-- Offer Label --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Offer Label
                    <span class="text-slate-400 font-normal text-xs ml-1">optional</span>
                </label>
                <input type="text" name="label"
                    value="{{ old('label', $offer?->label) }}"
                    placeholder="e.g. Eid Special, Summer Sale, Black Friday…"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('label')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- Prices --}}
            <div class="grid grid-cols-2 gap-4 mb-5">
                {{-- Original Price --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Original Price</label>
                    <div class="flex rounded-xl border border-slate-200 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent">
                        <span class="flex items-center px-3 bg-slate-50 border-r border-slate-200 text-slate-500 text-sm font-semibold select-none">$</span>
                        <input type="number" name="original_price" step="0.01" min="0"
                            x-model="originalPrice"
                            value="{{ old('original_price', $offer?->original_price) }}"
                            placeholder="0.00"
                            class="flex-1 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none bg-white">
                    </div>
                    @error('original_price')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Discount Price --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Discount Price</label>
                    <div class="flex rounded-xl border border-slate-200 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent">
                        <span class="flex items-center px-3 bg-slate-50 border-r border-slate-200 text-slate-500 text-sm font-semibold select-none">$</span>
                        <input type="number" name="discount_price" step="0.01" min="0"
                            x-model="discountPrice"
                            value="{{ old('discount_price', $offer?->discount_price) }}"
                            placeholder="0.00"
                            class="flex-1 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none bg-white">
                    </div>
                    @error('discount_price')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Live savings bar --}}
            <div x-show="savings" x-transition
                class="mb-5 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-100 rounded-xl"
                style="display:none">
                <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-green-700">
                    Customer saves <strong x-text="savings ? '$' + savings.amount : ''"></strong>
                    &mdash; <strong x-text="savings ? savings.pct + '% off' : ''"></strong>
                </p>
            </div>

            {{-- Active toggle --}}
            <div class="flex items-center justify-between px-4 py-3.5 bg-slate-50 rounded-xl mb-5">
                <div>
                    <p class="text-sm font-medium text-slate-700">Offer Active</p>
                    <p class="text-xs text-slate-400 mt-0.5">Show this offer across the platform</p>
                </div>
                {{-- hidden fallback --}}
                <input type="hidden" name="is_active" value="0">
                {{-- toggle --}}
                <button type="button" @click="isActive = !isActive" role="switch" :aria-checked="isActive"
                    class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    :style="isActive ? 'background-color:#2563eb' : 'background-color:#cbd5e1'">
                    <span
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md transition-transform duration-200"
                        :style="isActive ? 'transform:translateX(20px)' : 'transform:translateX(1px)'">
                    </span>
                    <input type="hidden" name="is_active" :value="isActive ? 1 : 0">
                </button>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-4">
                <button type="submit" class="btn-primary cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Offer
                </button>
                @if($offer)
                    <span class="text-xs text-slate-400">Last saved {{ $offer->updated_at->diffForHumans() }}</span>
                @endif
            </div>
        </form>
    </div>

    {{-- Current offer preview --}}
    @if($offer)
    @php
        $savePct = $offer->original_price > 0
            ? round((($offer->original_price - $offer->discount_price) / $offer->original_price) * 100)
            : 0;
    @endphp
    <div class="card" style="background:linear-gradient(135deg,#fff7ed,#fef3c7);border-color:#fed7aa">
        <div class="flex items-center justify-between mb-4">
            <p class="text-xs font-semibold text-orange-700 uppercase tracking-wider">Current Offer Preview</p>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                {{ $offer->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                {{ $offer->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if($offer->label)
                <span class="px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full">{{ $offer->label }}</span>
            @endif
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-slate-900">${{ number_format($offer->discount_price, 2) }}</span>
                <span class="text-sm text-slate-400 line-through">${{ number_format($offer->original_price, 2) }}</span>
            </div>
            @if($savePct > 0)
                <span class="px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full">{{ $savePct }}% OFF</span>
            @endif
        </div>
        @if($offer->original_price > $offer->discount_price)
            <p class="text-xs text-orange-700 mt-2 font-medium">
                You save ${{ number_format($offer->original_price - $offer->discount_price, 2) }}
            </p>
        @endif
    </div>
    @endif

</div>
@endsection
