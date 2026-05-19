@extends('layouts.mobile')
@section('title', 'Recherche')
@section('page-title', 'Recherche')

@section('header-right')
<button onclick="toggleFilters()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.2rem;padding:4px" id="filterToggle">
    <i class="bi bi-sliders"></i>
</button>
@endsection

@push('styles')
<style>
    .filter-panel {
        background: #fff; border-bottom: 1px solid var(--border);
        padding: 14px 16px; display: none;
    }
    .filter-panel.open { display: block; }
    .filter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
    .filter-select {
        padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 10px;
        font-size: .82rem; font-family: inherit; background: #F9FAFB;
        outline: none; color: var(--text-main); width: 100%;
    }
    .filter-select:focus { border-color: var(--primary); }
    .results-header {
        padding: 12px 16px; display: flex; align-items: center;
        justify-content: space-between; background: #fff; border-bottom: 1px solid var(--border);
    }
    .results-count { font-size: .8rem; color: var(--text-muted); }
    .results-count strong { color: var(--text-main); }
    .sort-select {
        font-size: .75rem; border: 1px solid var(--border); border-radius: 8px;
        padding: 5px 8px; font-family: inherit; color: var(--text-main);
        background: #fff; outline: none;
    }
    .listing-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 360px) { .listing-grid { grid-template-columns: 1fr; } }
    .listing-grid .card-mob-img img { height: 140px; }
    .listing-grid .card-mob-img .placeholder-img { height: 140px; }
    .listing-grid .card-mob-body { padding: 10px 12px 12px; }
    .listing-grid .card-mob-price { font-size: .95rem; }
    .listing-grid .card-mob-title { font-size: .78rem; }
    .listing-grid .card-mob-loc { font-size: .7rem; }
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 3rem; opacity: .3; display: block; margin-bottom: 12px; }
    .empty-state p { font-size: .85rem; }
</style>
@endpush

@section('content')
{{-- Search bar --}}
<form id="searchForm" action="{{ route('mobile.listings') }}" method="GET">
    <div style="padding:12px 16px;background:#fff;border-bottom:1px solid var(--border)">
        <div class="search-bar">
            <i class="bi bi-search search-icon"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Ville, titre, description…">
            <button type="submit" class="search-btn">OK</button>
        </div>
    </div>

    {{-- Chips rapides --}}
    <div style="padding:10px 16px;background:#fff;border-bottom:1px solid var(--border)">
        <div class="chip-scroll">
            <a href="{{ route('mobile.listings') }}" class="chip {{ !request()->anyFilled(['type','mode','bien_type']) ? 'active' : '' }}">Tous</a>
            <a href="{{ route('mobile.listings', ['type'=>'location']) }}" class="chip {{ request('type') === 'location' && request('mode') !== 'court_terme' ? 'active' : '' }}">📋 Location</a>
            <a href="{{ route('mobile.listings', ['type'=>'vente']) }}" class="chip {{ request('type') === 'vente' ? 'active' : '' }}">🏷️ Vente</a>
            <a href="{{ route('mobile.listings', ['mode'=>'court_terme']) }}" class="chip {{ request('mode') === 'court_terme' ? 'active' : '' }}">🛎️ Court terme</a>
            <a href="{{ route('mobile.listings', ['bien_type'=>'appartement']) }}" class="chip {{ request('bien_type') === 'appartement' ? 'active' : '' }}">🏢 Appart</a>
            <a href="{{ route('mobile.listings', ['bien_type'=>'villa']) }}" class="chip {{ request('bien_type') === 'villa' ? 'active' : '' }}">🏖️ Villa</a>
        </div>
    </div>

    {{-- Filtres avancés --}}
    <div class="filter-panel" id="filterPanel">
        <div class="filter-row">
            <select name="ville" class="filter-select">
                <option value="">Toutes villes</option>
                @foreach($villes as $v)
                <option value="{{ $v }}" {{ request('ville') === $v ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
            <select name="bien_type" class="filter-select">
                <option value="">Tous types</option>
                <option value="appartement" {{ request('bien_type') === 'appartement' ? 'selected' : '' }}>Appartement</option>
                <option value="maison"      {{ request('bien_type') === 'maison' ? 'selected' : '' }}>Maison</option>
                <option value="villa"       {{ request('bien_type') === 'villa' ? 'selected' : '' }}>Villa</option>
                <option value="studio"      {{ request('bien_type') === 'studio' ? 'selected' : '' }}>Studio</option>
                <option value="bureau"      {{ request('bien_type') === 'bureau' ? 'selected' : '' }}>Bureau</option>
            </select>
        </div>
        <div class="filter-row">
            <input type="number" name="prix_max" class="filter-select" placeholder="Prix max"
                   value="{{ request('prix_max') }}" min="0">
            <input type="number" name="voyageurs" class="filter-select" placeholder="Nb personnes"
                   value="{{ request('voyageurs') }}" min="1" max="20">
        </div>
        @if(request('mode') === 'court_terme')
        <div class="filter-row">
            <div>
                <label style="font-size:.72rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Arrivée</label>
                <input type="date" name="debut" class="filter-select" value="{{ request('debut') }}" min="{{ today()->format('Y-m-d') }}">
            </div>
            <div>
                <label style="font-size:.72rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Départ</label>
                <input type="date" name="fin" class="filter-select" value="{{ request('fin') }}" min="{{ today()->addDay()->format('Y-m-d') }}">
            </div>
        </div>
        @endif

        {{-- Conserver les paramètres cachés --}}
        @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
        @if(request('mode'))<input type="hidden" name="mode" value="{{ request('mode') }}">@endif

        <button type="submit" class="btn-mob-primary" style="margin-top:4px;border-radius:10px;padding:11px">
            <i class="bi bi-search"></i> Appliquer les filtres
        </button>
    </div>
</form>

{{-- Résultats --}}
<div class="results-header">
    <span class="results-count"><strong>{{ $annonces->total() }}</strong> résultat{{ $annonces->total() > 1 ? 's' : '' }}</span>
    <select class="sort-select" onchange="window.location.search = window.location.search.replace(/sort=[^&]*/,'') + '&sort=' + this.value">
        <option>Récents</option>
        <option>Prix ↑</option>
        <option>Prix ↓</option>
        <option>Populaires</option>
    </select>
</div>

<div class="mob-page" style="padding-top:14px">
    @if($annonces->isEmpty())
    <div class="empty-state">
        <i class="bi bi-house-slash"></i>
        <p>Aucune annonce ne correspond à votre recherche.</p>
        <a href="{{ route('mobile.listings') }}" class="btn-mob-outline" style="width:auto;display:inline-flex;padding:10px 20px">
            Réinitialiser les filtres
        </a>
    </div>
    @else
    <div class="listing-grid">
        @foreach($annonces as $a)
        @php $photos = $a->allPhotos(); @endphp
        <div class="card-mob">
            <div class="card-mob-img">
                @if(count($photos) > 0)
                    <img src="{{ $photos[0] }}" alt="{{ $a->titre }}" loading="lazy">
                @else
                    <div class="placeholder-img"><i class="bi bi-house-door"></i></div>
                @endif
                <span class="card-mob-badge {{ $a->type === 'vente' ? 'badge-vente' : ($a->mode_location === 'court_terme' ? 'badge-court-terme' : 'badge-location') }}">
                    {{ $a->type === 'vente' ? 'Vente' : ($a->mode_location === 'court_terme' ? '🛎️' : 'Loc.') }}
                </span>
                <button class="card-mob-fav" data-fav-id="{{ $a->id }}"
                        onclick="toggleFav({{ $a->id }}, this); event.preventDefault();">
                    <i class="bi bi-heart"></i>
                </button>
            </div>
            <a href="{{ route('mobile.detail', $a) }}" style="text-decoration:none;display:block">
                <div class="card-mob-body">
                    <div class="card-mob-price">
                        {{ number_format($a->mode_location === 'court_terme' ? $a->prix_nuit : $a->prix, 0, ',', ' ') }}
                        <span class="price-unit">{{ \App\Models\Parametre::get('paiement_devise','XOF') }}{{ $a->mode_location === 'court_terme' ? '/nuit' : ($a->type === 'location' ? ('/'.$a->type_tarif) : '') }}</span>
                    </div>
                    <div class="card-mob-title">{{ Str::limit($a->titre, 35) }}</div>
                    <div class="card-mob-loc"><i class="bi bi-geo-alt"></i>{{ $a->bien->ville ?? '—' }}</div>
                    @if($a->bien)
                    <div class="card-mob-meta">
                        @if($a->bien->surface)<span><i class="bi bi-square"></i>{{ round($a->bien->surface) }}m²</span>@endif
                        @if($a->bien->nb_chambres)<span><i class="bi bi-door-open"></i>{{ $a->bien->nb_chambres }}</span>@endif
                    </div>
                    @endif
                </div>
            </a>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($annonces->hasPages())
    <div style="display:flex;gap:8px;justify-content:center;margin-top:20px;flex-wrap:wrap">
        @if($annonces->onFirstPage())
            <span class="chip" style="opacity:.4">← Préc.</span>
        @else
            <a href="{{ $annonces->previousPageUrl() }}" class="chip active">← Préc.</a>
        @endif
        <span class="chip" style="border-color:var(--primary);color:var(--primary)">
            Page {{ $annonces->currentPage() }} / {{ $annonces->lastPage() }}
        </span>
        @if($annonces->hasMorePages())
            <a href="{{ $annonces->nextPageUrl() }}" class="chip active">Suiv. →</a>
        @else
            <span class="chip" style="opacity:.4">Suiv. →</span>
        @endif
    </div>
    @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleFilters() {
    const p = document.getElementById('filterPanel');
    p.classList.toggle('open');
    document.getElementById('filterToggle').style.color = p.classList.contains('open') ? 'var(--primary)' : 'var(--text-muted)';
}
@if(request()->anyFilled(['prix_max','voyageurs','debut','fin','ville','bien_type']))
document.getElementById('filterPanel').classList.add('open');
document.getElementById('filterToggle').style.color = 'var(--primary)';
@endif
</script>
@endpush
