<nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs" data-pagination-ui>
    <!-- Info Ringkasan Item & Kapasitas Halaman -->
    <div class="flex flex-wrap items-center gap-2 text-slate-400">
        @if ($paginator->total() > 0)
            <span>
                Menampilkan
                <strong class="font-semibold text-white font-mono">{{ number_format($paginator->firstItem() ?? 0) }}</strong>&ndash;<strong class="font-semibold text-white font-mono">{{ number_format($paginator->lastItem() ?? 0) }}</strong>
                dari
                <strong class="font-semibold text-white font-mono">{{ number_format($paginator->total()) }}</strong>
                data
            </span>
        @else
            <span>Menampilkan <strong class="font-semibold text-white font-mono">0</strong> data</span>
        @endif
        <span class="pill-blue text-[10px] font-mono">{{ $paginator->perPage() }} item / halaman</span>
    </div>

    <!-- Kontrol Navigasi Halaman -->
    <div class="flex items-center gap-1 self-end sm:self-auto">
        {{-- Tombol Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="px-2.5 py-1.5 rounded-md bg-slate-900/50 border border-slate-800/80 text-slate-600 cursor-not-allowed select-none">
                &lsaquo; Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-2.5 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-slate-200 hover:text-white transition">
                &lsaquo; Sebelumnya
            </a>
        @endif

        {{-- Nomor Halaman --}}
        @if (isset($elements) && is_array($elements) && $paginator->total() > 0)
            <div class="hidden sm:flex items-center gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 py-1.5 text-slate-500 select-none">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="min-w-[30px] text-center px-2.5 py-1.5 rounded-md bg-blue-600 text-white font-bold font-mono shadow-sm shadow-blue-600/30">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="min-w-[30px] text-center px-2.5 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white font-mono transition">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
        @else
            <span class="min-w-[30px] text-center px-2.5 py-1.5 rounded-md bg-blue-600 text-white font-bold font-mono">
                {{ $paginator->currentPage() }}
            </span>
        @endif

        {{-- Indikator Mobile --}}
        <span class="sm:hidden px-2 py-1 text-[11px] font-mono text-slate-400">
            Hal {{ $paginator->currentPage() }}/{{ max($paginator->lastPage(), 1) }}
        </span>

        {{-- Tombol Berikutnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-2.5 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-slate-200 hover:text-white transition">
                Berikutnya &rsaquo;
            </a>
        @else
            <span aria-disabled="true" class="px-2.5 py-1.5 rounded-md bg-slate-900/50 border border-slate-800/80 text-slate-600 cursor-not-allowed select-none">
                Berikutnya &rsaquo;
            </span>
        @endif
    </div>
</nav>
