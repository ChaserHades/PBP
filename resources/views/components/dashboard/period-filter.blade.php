@props([
    'periode',
    'options' => \App\Services\DashboardStatisticsService::PERIOD_OPTIONS,
])

<form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2" data-period-filter>
    <label for="periode" class="text-[11px] font-medium text-slate-400 uppercase tracking-wider whitespace-nowrap">Periode Grafik</label>
    <select id="periode" name="periode" class="ui-input text-xs bg-slate-900 !py-1.5" onchange="this.form.submit()">
        @foreach($options as $option)
            <option value="{{ $option }}" @selected($periode === $option)>{{ $option }} bulan terakhir</option>
        @endforeach
    </select>
    <noscript><button type="submit" class="btn-secondary text-xs">Terapkan</button></noscript>
</form>

