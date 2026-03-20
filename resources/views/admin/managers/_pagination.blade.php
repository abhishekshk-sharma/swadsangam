@if($managers->hasPages())
<div style="padding:1rem 1.5rem;border-top:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
    <span style="font-size:0.8rem;color:var(--gray-500);">
        Showing {{ $managers->firstItem() }}–{{ $managers->lastItem() }} of {{ $managers->total() }}
    </span>
    <div style="display:flex;gap:0.25rem;">
        @if($managers->onFirstPage())
            <span class="page-btn disabled">‹</span>
        @else
            <a href="#" class="page-btn" data-page="{{ $managers->currentPage() - 1 }}">‹</a>
        @endif
        @foreach($managers->getUrlRange(1, $managers->lastPage()) as $page => $url)
            @if($page == $managers->currentPage())
                <span class="page-btn active">{{ $page }}</span>
            @elseif(abs($page - $managers->currentPage()) <= 2 || $page == 1 || $page == $managers->lastPage())
                <a href="#" class="page-btn" data-page="{{ $page }}">{{ $page }}</a>
            @elseif(abs($page - $managers->currentPage()) == 3)
                <span class="page-btn disabled">…</span>
            @endif
        @endforeach
        @if($managers->hasMorePages())
            <a href="#" class="page-btn" data-page="{{ $managers->currentPage() + 1 }}">›</a>
        @else
            <span class="page-btn disabled">›</span>
        @endif
    </div>
</div>
@endif
