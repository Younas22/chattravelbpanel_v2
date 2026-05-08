@extends('admin.layouts.app')
@section('title', 'Analytics')

@section('content')
<div class="space-y-6">

    {{-- Period picker --}}
    <div class="flex items-center gap-3">
        @foreach([['7', '7 Days'], ['30', '30 Days'], ['90', '90 Days']] as [$val, $label])
        <a href="?period={{ $val }}" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ $period == $val ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
        @endforeach
    </div>

    {{-- Charts row --}}
    <div class="grid lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 card">
            <h2 class="font-semibold text-slate-900 mb-4">Conversations & Messages</h2>
            <canvas id="mainChart" height="100"></canvas>
        </div>

        <div class="card">
            <h2 class="font-semibold text-slate-900 mb-4">Devices</h2>
            <canvas id="deviceChart" height="200"></canvas>
            <div class="mt-4 space-y-2">
                @foreach($devices as $device => $count)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600 capitalize">{{ $device }}</span>
                    <span class="font-semibold text-slate-800">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">

        {{-- Top Countries --}}
        <div class="card">
            <h2 class="font-semibold text-slate-900 mb-4">Top Countries</h2>
            @if($topCountries->isEmpty())
                <p class="text-slate-400 text-sm text-center py-8">No data yet</p>
            @else
            <div class="space-y-3">
                @foreach($topCountries as $row)
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-700 flex-1">{{ $row->country }}</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($row->count / $topCountries->max('count')) * 100 }}%"></div>
                    </div>
                    <span class="text-sm font-semibold text-slate-800 w-10 text-right">{{ $row->count }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Top Landing Pages --}}
        <div class="card">
            <h2 class="font-semibold text-slate-900 mb-4">Top Landing Pages</h2>
            @if($topPages->isEmpty())
                <p class="text-slate-400 text-sm text-center py-8">No data yet</p>
            @else
            <div class="space-y-2">
                @foreach($topPages as $row)
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-slate-600 flex-1 truncate">{{ $row->landing_page }}</span>
                    <span class="font-semibold text-slate-800 shrink-0">{{ $row->count }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chartDays = [];
const convData = [];
const msgData = [];
const period = {{ $period }};

for (let i = period - 1; i >= 0; i--) {
    const d = new Date();
    d.setDate(d.getDate() - i);
    const key = d.toISOString().split('T')[0];
    chartDays.push(d.toLocaleDateString('en', { month: 'short', day: 'numeric' }));
    convData.push({{ json_encode($conversations) }}[key] || 0);
    msgData.push({{ json_encode($messages) }}[key] || 0);
}

new Chart(document.getElementById('mainChart'), {
    type: 'line',
    data: {
        labels: chartDays,
        datasets: [
            { label: 'Conversations', data: convData, borderColor: '#2563eb', backgroundColor: '#2563eb20', tension: 0.4, fill: true },
            { label: 'Messages', data: msgData, borderColor: '#7c3aed', backgroundColor: '#7c3aed20', tension: 0.4, fill: true },
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
});

const deviceData = @json($devices);
new Chart(document.getElementById('deviceChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(deviceData).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
        datasets: [{ data: Object.values(deviceData), backgroundColor: ['#2563eb', '#7c3aed', '#10b981'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, cutout: '70%' }
});
</script>
@endpush
