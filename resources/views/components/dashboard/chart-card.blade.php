@props([
    'title',
    'subtitle' => null,
    'type' => 'bar',
    'labels' => [],
    'datasets' => [],
    'options' => [],
    'links' => [],
    'height' => 'h-64',
    'emptyMessage' => 'Belum ada data untuk ditampilkan pada grafik ini.',
])

@php
    $hasData = count($labels) > 0
        && collect($datasets)->flatMap(fn ($dataset) => $dataset['data'] ?? [])->contains(fn ($value) => (float) $value !== 0.0);

    $chartConfig = [
        'type' => $type,
        'labels' => array_values($labels),
        'datasets' => array_map(fn ($dataset) => array_merge($dataset, ['data' => array_values($dataset['data'] ?? [])]), $datasets),
        'options' => (object) $options,
        'links' => array_values($links),
    ];
@endphp

<div {{ $attributes->merge(['class' => 'panel-card p-5 flex flex-col min-w-0']) }}>
    <div class="flex items-start justify-between gap-3 pb-3 mb-3 border-b border-slate-800">
        <div class="min-w-0">
            <h2 class="text-sm font-bold text-white">{{ $title }}</h2>
            @if($subtitle)
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="shrink-0">{{ $actions }}</div>
        @endisset
    </div>

    @if($hasData)
        <div class="relative w-full {{ $height }}">
            <canvas data-chart="{{ json_encode($chartConfig) }}" role="img" aria-label="Grafik {{ $title }}"></canvas>
        </div>
    @else
        <div class="{{ $height }} flex flex-col items-center justify-center text-center rounded-lg border border-dashed border-slate-800 bg-slate-900/40 px-4" data-chart-empty>
            <svg class="w-8 h-8 text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <p class="text-xs text-slate-500">{{ $emptyMessage }}</p>
        </div>
    @endif

    {{ $slot }}
</div>

