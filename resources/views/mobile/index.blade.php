@extends('layouts.mobile')
@section('title', 'Accueil')

@push('styles')
<style>
    body { padding-top: 0; }
    .mob-header { display: none; }

    .hero {
        background: linear-gradient(160deg, #EA580C 0%, #F97316 50%, #FB923C 100%);
        padding: 48px 20px 60px; position: relative; overflow: hidden;
    }
    .hero::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 30px;
        background: var(--bg); border-radius: 30px 30px 0 0;
    }
    .hero-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.2); color: #fff;
        padding: 5px 12px; border-radius: 20px; font-size: .73rem; font-weight: 600;
        margin-bottom: 14px;
    }
    .hero h1 { color: #fff; font-size: 1.65rem; font-weight: 800; line-height: 1.25; margin: 0 0 6px; }
    .hero p   { color: rgba(255,255,255,.85); font-size: .88rem; margin: 0 0 20px; }
    .hero-stats { display: flex; gap: 20px; margin-top: 18px; }
    .hero-stat { text-align: center; }
    .hero-stat .stat-num { color: #fff; font-size: 1.3rem; font-weight: 800; }
    .hero-stat .stat-lbl { color: rgba(255,255,255,.75); font-size: .67rem; }

    .hero-search {
        background: #fff; border-radius: 16px;
        padding: 14px; box-shadow: 0 4px 20px rgba(0,0,0,.15);
        display: flex; flex-direction: column; gap: 10px;
    }
    .hero-search select, .hero-search input {
        padding: 11px 14px; border: 1.5px solid var(--border); border-radius: 10px;
        font-size: .85rem; font-family: inherit; background: #F9FAFB; outline: none;
        color: var(--text-main); width: 100%;
    }
    .hero-search select:focus, .hero-search input:focus { border-color: var(--primary); background: #fff; }
    .hero-search-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .hero-search-btn {
        background: var(--primary); color: #fff; border: none;
        border-radius: 10px; padding: 13px; font-size: .88rem;
        font-weight: 700; cursor: pointer; display: flex;
        align-items: center; justify-content: center; gap: 6px;
        width: 100%; font-family: inherit; transition: background .15s;
    }
    .hero-search-btn:hover { background: var(--primary-dk); }

    .category-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
    .cat-item {
        display: flex; flex-direction: column; align-items: center; gap: 6px;
        padding: 12px 8px; border-radius: 14px; background: #fff;
        cursor: pointer; text-decoration: none; transition: all .15s;
        border: 1.5px solid transparent; box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .cat-item:hover, .cat-item.active { border-color: var(--primary); background: var(--primary-lt); }
    .cat-icon { font-size: 1.4rem; }
    .cat-label { font-size: .65rem; font-weight: 700; text-align: center; color: var(--text-main); }

    .listing-scroll { display: flex; gap: 14px; overflow-x: auto; padding: 4px 2px;
        scrollbar-width: none; }
    .listing-scroll::-webkit-scrollbar { display: none; }
    .listing-scroll .card-mob { min-width: 220px; max-width: 220px; }
    .listing-scroll .card-mob-img img { height: 150px; }
    .listing-scroll .card-mob-img .placeholder-img { height: 150px; }

    .court-terme-card {
        display: flex; gap: 12px; background: #fff;
        border-radius: 14px; overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.07);
        margin-bottom: 12px; text-decoration: none; color: var(--text-main);
    }
    .court-terme-card img { width: 110px; height: 110px; object-fit: cover; flex-shrink: 0; }
    .court-terme-card .ct-placeholder {
        width: 110px; height: 110px; flex-shrink: 0;
        background: linear-gradient(135deg,#FED7AA,#FEF3C7);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: var(--primary);
    }
    .court-terme-info { padding: 12px 12px 12px 0; flex: 1; min-width: 0; }
    .ct-price { font-size: 1rem; font-weight: 800; color: var(--primary); }
    .ct-price small { font-size: .7rem; font-weight: 500; color: var(--text-muted); }
    .ct-title { font-size: .82rem; font-weight: 600; margin: 3px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ct-loc { font-size: .72rem; color: var(--text-muted); display: flex; align-items: center; gap: 3px; }
    .ct-tags { display: flex; gap: 5px; margin-top: 6px; flex-wrap: wrap; }
    .ct-tag { font-size: .63rem; background: var(--primary-lt); color: #C2410C; padding: 2px 7px; border-radius: 10px; font-weight: 600; }
</style>
@endpush

@section('content')
{{-- ── HERO ─────────────────────────────────────────────────────────────────── --}}
<div class="hero">
    <div class="hero-badge"><i class="bi bi-house-door-fill"></i> Plateforme immobilière</div>
    <h1>Trouvez votre<br>logement idéal</h1>
    <p>Location, vente et séjours meublés partout</p>

    {{-- Search widget --}}
    <form action="{{ route('mobile.listings') }}" method="GET" class="hero-search">
        <div class="search-bar" style="margin:0">
            <i class="bi bi-search search-icon"></i>
            <input type="text" name="q" placeholder="Ville, quartier, type de bien…" style="padding-left:40px;border-radius:10px;background:#F9FAFB">
        </div>
        <div class="hero-search-row">
            <select name="type">
                <option value="">Tous types</option>
                <option value="location">Location</option>
                <option value="vente">Vente</option>
            </select>
            <select name="ville">
                <option value="">Toutes villes</option>
                @foreach($villes as $v)
                <option value="{{ $v }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="hero-search-btn">
            <i class="bi bi-search"></i> Rechercher
        </button>
    </form>

    {{-- Stats --}}
    <div class="hero-stats">
        <div class="hero-stat"><div class="stat-num">{{ $stats['biens'] }}</div><div class="stat-lbl">Annonces</div></div>
        <div class="hero-stat"><div class="stat-num">{{ $stats['locations'] }}</div><div class="stat-lbl">Locations</div></div>
        <div class="hero-stat"><div class="stat-num">{{ $stats['ventes'] }}</div><div class="stat-lbl">Ventes</div></div>
        <div class="hero-stat"><div class="stat-num">{{ $stats['meublees'] }}</div><div class="stat-lbl">Séjours</div></div>
    </div>
</div>

<div class="mob-page">

    {{-- ── Catégories ── --}}
    <div class="mb-4">
        <div class="section-head"><h3>Explorer par type</h3></div>
        <div class="category-grid">
            <a href="{{ route('mobile.listings', ['bien_type'=>'appartement']) }}" class="cat-item">
                <span class="cat-icon">🏢</span><span class="cat-label">Appartement</span>
            </a>
            <a href="{{ route('mobile.listings', ['bien_type'=>'maison']) }}" class="cat-item">
                <span class="cat-icon">🏡</span><span class="cat-label">Maison</span>
            </a>
            <a href="{{ route('mobile.listings', ['bien_type'=>'villa']) }}" class="cat-item">
                <span class="cat-icon">🏖️</span><span class="cat-label">Villa</span>
            </a>
            <a href="{{ route('mobile.listings', ['bien_type'=>'studio']) }}" class="cat-item">
                <span class="cat-icon">🛋️</span><span class="cat-label">Studio</span>
            </a>
        </div>
    </div>

    {{-- ── Séjours meublés court terme ── --}}
    @if($courtsTermes->count() > 0)
    <div class="mb-4">
        <div class="section-head">
            <h3>🛎️ Séjours meublés</h3>
            <a href="{{ route('mobile.listings', ['mode'=>'court_terme']) }}">Voir tout</a>
        </div>
        @foreach($courtsTermes as $a)
        @php $photos = $a->allPhotos(); @endphp
        <a href="{{ route('mobile.detail', $a) }}" class="court-terme-card">
            @if(count($photos) > 0)
                <img src="{{ $photos[0] }}" alt="{{ $a->titre }}" loading="lazy">
            @else
                <div class="ct-placeholder"><i class="bi bi-house-door"></i></div>
            @endif
            <div class="court-terme-info">
                <div class="ct-price">{{ number_format($a->prix_nuit, 0, ',', ' ') }} {{ \App\Models\Parametre::get('paiement_devise','XOF') }}<small>/nuit</small></div>
                <div class="ct-title">{{ $a->titre }}</div>
                <div class="ct-loc"><i class="bi bi-geo-alt"></i>{{ $a->bien->ville ?? '—' }}</div>
                <div class="ct-tags">
                    @if($a->bien->nb_chambres)<span class="ct-tag">{{ $a->bien->nb_chambres }} ch.</span>@endif
                    @if($a->nb_max_voyageurs)<span class="ct-tag">{{ $a->nb_max_voyageurs }} pers.</span>@endif
                    @if($a->bien->meuble)<span class="ct-tag">Meublé</span>@endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    {{-- ── À la une ── --}}
    @if($featured->count() > 0)
    <div class="mb-4">
        <div class="section-head">
            <h3>⭐ Annonces populaires</h3>
            <a href="{{ route('mobile.listings') }}">Voir tout</a>
        </div>
        <div class="listing-scroll">
            @foreach($featured as $a)
            @php $photos = $a->allPhotos(); @endphp
            <div class="card-mob">
                <div class="card-mob-img">
                    @if(count($photos) > 0)
                        <img src="{{ $photos[0] }}" alt="{{ $a->titre }}" loading="lazy">
                    @else
                        <div class="placeholder-img"><i class="bi bi-house-door"></i></div>
                    @endif
                    <span class="card-mob-badge {{ $a->type === 'vente' ? 'badge-vente' : ($a->mode_location === 'court_terme' ? 'badge-court-terme' : 'badge-location') }}">
                        {{ $a->type === 'vente' ? 'Vente' : ($a->mode_location === 'court_terme' ? '/nuit' : 'Location') }}
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
                            <span class="price-unit">{{ \App\Models\Parametre::get('paiement_devise','XOF') }}{{ $a->mode_location === 'court_terme' ? '/nuit' : ($a->type === 'location' ? '/mois' : '') }}</span>
                        </div>
                        <div class="card-mob-title">{{ Str::limit($a->titre, 40) }}</div>
                        <div class="card-mob-loc"><i class="bi bi-geo-alt"></i>{{ $a->bien->ville ?? '—' }}</div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── CTA download PWA ── --}}
    <div style="background:linear-gradient(135deg,#F97316,#EA580C);border-radius:16px;padding:20px;color:#fff;text-align:center;margin-bottom:20px">
        <div style="font-size:1.8rem;margin-bottom:8px">📱</div>
        <div style="font-weight:800;font-size:.95rem;margin-bottom:4px">Installez l'application</div>
        <div style="font-size:.78rem;opacity:.85;margin-bottom:14px">Accédez à ImmoGest comme une vraie app mobile</div>
        <button id="installBtn" onclick="installPwa()" style="background:rgba(255,255,255,.2);border:2px solid rgba(255,255,255,.5);color:#fff;border-radius:10px;padding:10px 20px;font-size:.82rem;font-weight:700;cursor:pointer;display:none">
            <i class="bi bi-download me-1"></i> Installer maintenant
        </button>
        <div id="installInstructions" style="font-size:.73rem;opacity:.8">
            Sur iOS : Safari → Partager → Sur l'écran d'accueil
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
let deferredPrompt;
window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
    document.getElementById('installBtn').style.display = 'inline-block';
    document.getElementById('installInstructions').style.display = 'none';
});
function installPwa() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(() => { deferredPrompt = null; });
    }
}
</script>
@endpush
