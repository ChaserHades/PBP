@props([
    'label',
    'value',
    'hint' => null,
    'accent' => 'border-l-blue-500',
    'valueClass' => 'text-2xl font-black text-white',
])

<div {{ $attributes->merge(['class' => "panel-card p-4 border-l-2 {$accent}"]) }}>
    <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">{{ $label }}</div>
    <div class="{{ $valueClass }} mt-1">{{ $value }}</div>
    @if($hint || $slot->isNotEmpty())
        <div class="text-[11px] text-slate-500 mt-1">{{ $slot->isNotEmpty() ? $slot : $hint }}</div>
    @endif
</div>

