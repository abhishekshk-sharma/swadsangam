@if($staff->hasPages())
<div style="padding:1rem 1.5rem;border-top:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
    <span style="font-size:0.8rem;color:var(--gray-500);">
        Showing {{ $staff->firstItem() }}–{{ $staff->lastItem() }} of {{ $staff->total() }}
    </span>
    <div style="display:flex;gap:0.25rem;">
        @if($staff->onFirstPage())
            <span class="page-btn disabled">‹</span>
        @else
            <a href="#" class="page-btn" data-page="{{ $staff->currentPage() - 1 }}">‹</a>
        @endif
        @foreach($staff->getUrlRange(1, $staff->lastPage()) as $page => $url)
            @if($page == $staff->currentPage())
                <span class="page-btn active">{{ $page }}</span>
            @elseif(abs($page - $staff->currentPage()) <= 2 || $page == 1 || $page == $staff->lastPage())
                <a href="#" class="page-btn" data-page="{{ $page }}">{{ $page }}</a>
            @elseif(abs($page - $staff->currentPage()) == 3)
                <span class="page-btn disabled">…</span>
            @endif
        @endforeach
        @if($staff->hasMorePages())
            <a href="#" class="page-btn" data-page="{{ $staff->currentPage() + 1 }}">›</a>
        @else
            <span class="page-btn disabled">›</span>
        @endif
    </div>
</div>
@endif
