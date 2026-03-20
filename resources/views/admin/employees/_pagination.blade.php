@php
    $currentPage = $paginator->currentPage();
    $lastPage    = $paginator->lastPage();
    $from        = $paginator->firstItem();
    $to          = $paginator->lastItem();
    $total       = $paginator->total();
    $window      = 2; // pages each side of current
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
    <span style="font-size:0.8rem;color:var(--gray-500);">
        Showing {{ $from }}–{{ $to }} of {{ $total }}
    </span>

    @if($lastPage > 1)
    <div style="display:flex;gap:0.25rem;align-items:center;">

        {{-- Prev --}}
        @if($paginator->onFirstPage())
            <span style="padding:0.3rem 0.65rem;border-radius:0.4rem;border:1px solid var(--gray-200);color:var(--gray-300);font-size:0.85rem;">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}&tab={{ request('tab','managers') }}"
               style="padding:0.3rem 0.65rem;border-radius:0.4rem;border:1px solid var(--gray-200);color:var(--gray-600);font-size:0.85rem;text-decoration:none;">‹</a>
        @endif

        {{-- First page --}}
        @if($currentPage > $window + 1)
            <a href="{{ $paginator->url(1) }}&tab={{ request('tab','managers') }}"
               style="padding:0.3rem 0.65rem;border-radius:0.4rem;border:1px solid var(--gray-200);color:var(--gray-600);font-size:0.85rem;text-decoration:none;">1</a>
            @if($currentPage > $window + 2)
                <span style="color:var(--gray-400);font-size:0.85rem;">…</span>
            @endif
        @endif

        {{-- Window --}}
        @for($p = max(1, $currentPage - $window); $p <= min($lastPage, $currentPage + $window); $p++)
            @if($p === $currentPage)
                <span style="padding:0.3rem 0.65rem;border-radius:0.4rem;border:1px solid var(--blue-400);background:var(--blue-600);color:#fff;font-size:0.85rem;font-weight:600;">{{ $p }}</span>
            @else
                <a href="{{ $paginator->url($p) }}&tab={{ request('tab','managers') }}"
                   style="padding:0.3rem 0.65rem;border-radius:0.4rem;border:1px solid var(--gray-200);color:var(--gray-600);font-size:0.85rem;text-decoration:none;">{{ $p }}</a>
            @endif
        @endfor

        {{-- Last page --}}
        @if($currentPage < $lastPage - $window)
            @if($currentPage < $lastPage - $window - 1)
                <span style="color:var(--gray-400);font-size:0.85rem;">…</span>
            @endif
            <a href="{{ $paginator->url($lastPage) }}&tab={{ request('tab','managers') }}"
               style="padding:0.3rem 0.65rem;border-radius:0.4rem;border:1px solid var(--gray-200);color:var(--gray-600);font-size:0.85rem;text-decoration:none;">{{ $lastPage }}</a>
        @endif

        {{-- Next --}}
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}&tab={{ request('tab','managers') }}"
               style="padding:0.3rem 0.65rem;border-radius:0.4rem;border:1px solid var(--gray-200);color:var(--gray-600);font-size:0.85rem;text-decoration:none;">›</a>
        @else
            <span style="padding:0.3rem 0.65rem;border-radius:0.4rem;border:1px solid var(--gray-200);color:var(--gray-300);font-size:0.85rem;">›</span>
        @endif

    </div>
    @endif
</div>
