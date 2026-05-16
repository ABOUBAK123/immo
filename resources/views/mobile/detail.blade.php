@extends('layouts.mobile')
@section('title', $annonce->titre)

@section('header-left')
<a href="{{ url()->previous() }}" class="btn-mob-ghost" style="font-size:1.2rem"><i class="bi bi-arrow-left"></i></a>
@endsection
@section('header-right')
<button class="card-mob-fav" data-fav-id="{{ $annonce->id }}"
        onclick="toggleFav({{ $annonce->id }}, this)"
        style="position:static;width:36px;height:36px;background:var(--bg);font-size:1.1rem">
    <i class="bi bi-heart"></i>
</button>
@endsection

@push('styles')
<style>
    body { padding-top: 0; }
    .mob-header { position: relative; }

    /* Gallery */
    .gallery { position: relative; overflow: hidden; background: #F3F4F6; }
    .gallery-track { display: flex; transition: transform .3s ease; }
    .gallery-track img, .gallery-track .gallery-placeholder {
        min-width: 100%; width: 100%; height: 280px; object-fit: cover; flex-shrink: 0;
    }
    .gallery-track .gallery-placeholder {
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg,#FED7AA,#FEF3C7);
        font-size: 4rem; color: var(--primary);
    }
    .gallery-dots {
        position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%);
        display: flex; gap: 5px;
    }
    .gallery-dot {
        width: 6px; height: 6px; border-radius: 50%;
        background: rgba(255,255,255,.5); transition: all .2s;
    }
    .gallery-dot.active { background: #fff; width: 18px; border-radius: 3px; }
    .gallery-count {
        position: absolute; top: 12px; right: 12px;
        background: rgba(0,0,0,.5); color: #fff; border-radius: 8px;
        padding: 3px 8px; font-size: .72rem; font-weight: 600;
    }
    .gallery-badge {
        position: absolute; top: 12px; left: 12px;
        padding: 4px 10px; border-radius: 8px; font-size: .72rem; font-weight: 700;
    }

    /* Info block */
    .detail-block { padding: 18px 16px 14px; background: #fff; border-bottom: 1px solid var(--border); }
    .detail-price { font-size: 1.5rem; font-weight: 800; color: var(--primary); }
    .detail-price small { font-size: .8rem; font-weight: 500; color: var(--text-muted); }
    .detail-title { font-size: 1.05rem; font-weight: 700; margin: 4px 0 8px; }
    .detail-loc { font-size: .82rem; color: var(--text-muted); display: flex; align-items: center; gap: 5px; }

    .meta-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 8px; margin: 14px 0; }
    .meta-item { text-align: center; padding: 10px 6px; background: var(--bg); border-radius: 10px; }
    .meta-item i { font-size: 1.1rem; color: var(--primary); display: block; margin-bottom: 3px; }
    .meta-item .meta-val { font-size: .82rem; font-weight: 700; }
    .meta-item .meta-lbl { font-size: .62rem; color: var(--text-muted); }

    /* Équipements */
    .equip-grid { display: flex; flex-wrap: wrap; gap: 8px; }
    .equip-item {
        display: flex; align-items: center; gap: 5px;
        background: var(--primary-lt); color: #C2410C;
        padding: 5px 10px; border-radius: 8px; font-size: .75rem; font-weight: 600;
    }

    /* Description */
    .description-text { font-size: .85rem; line-height: 1.7; color: #374151; }
    .description-text.collapsed { display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
    .show-more { color: var(--primary); font-size: .82rem; font-weight: 600; background: none; border: none; padding: 6px 0; cursor: pointer; }

    /* Booking sticky */
    .booking-sticky {
        position: fixed; bottom: var(--nav-h); left: 0; right: 0; z-index: 90;
        background: #fff; border-top: 1px solid var(--border); padding: 12px 16px;
        display: flex; align-items: center; gap: 12px;
        padding-bottom: calc(12px + var(--safe-b));
    }
    .booking-price { flex: 1; }
    .booking-price .price { font-size: 1.2rem; font-weight: 800; color: var(--primary); }
    .booking-price .unit  { font-size: .72rem; color: var(--text-muted); }

    /* Calendar widget */
    .booking-widget { background: #fff; border-radius: 16px; overflow: hidden; }
    .bw-head { padding: 14px 16px; background: var(--primary-lt); border-bottom: 1px solid #FED7AA; }
    .bw-head h4 { margin: 0; font-size: .9rem; font-weight: 700; color: var(--text-main); }
    .bw-head p  { margin: 3px 0 0; font-size: .75rem; color: var(--text-muted); }
    .bw-body { padding: 14px 16px; }
    .bw-dates { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px; }
    .bw-date input {
        padding: 11px 12px; border: 1.5px solid var(--border); border-radius: 10px;
        font-size: .82rem; width: 100%; font-family: inherit; outline: none;
        color: var(--text-main); background: #F9FAFB;
    }
    .bw-date input:focus { border-color: var(--primary); background: #fff; }
    .bw-date label { font-size: .7rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 4px; }
    .bw-summary { background: var(--bg); border-radius: 10px; padding: 12px; margin: 10px 0; }
    .bw-sum-row { display: flex; justify-content: space-between; font-size: .82rem; padding: 3px 0; }
    .bw-sum-total { font-weight: 800; font-size: .9rem; border-top: 1px solid var(--border); padding-top: 6px; margin-top: 4px; }

    /* Section separator */
    .detail-section { padding: 16px; background: #fff; border-bottom: 1px solid var(--border); }
    .detail-section h5 { font-size: .88rem; font-weight: 700; margin: 0 0 12px; }

    /* Similaires */
    .similaires-scroll { display: flex; gap: 12px; overflow-x: auto; padding: 2px 0; scrollbar-width: none; }
    .similaires-scroll::-webkit-scrollbar { display: none; }
    .similaire-card { min-width: 160px; border-radius: 12px; overflow: hidden; background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.07); text-decoration: none; color: var(--text-main); }
    .similaire-card img, .similaire-card .sim-placeholder {
        width: 100%; height: 110px; object-fit: cover;
        background: linear-gradient(135deg,#FED7AA,#FEF3C7);
        display: flex; align-items: center; justify-content: center; font-size: 1.8rem;
    }
    .similaire-info { padding: 8px 10px; }
    .similaire-info .sim-price { font-size: .85rem; font-weight: 800; color: var(--primary); }
    .similaire-info .sim-title { font-size: .75rem; font-weight: 600; }
    .similaire-info .sim-loc   { font-size: .68rem; color: var(--text-muted); }
</style>
@endpush

@section('content')

@php
$photos  = $annonce->allPhotos();
$devise  = \App\Models\Parametre::get('paiement_devise','XOF');
$equips  = $annonce->equipements ?? [];
$equipsMap = [
    'wifi' => '📶 Wi-Fi', 'piscine' => '🏊 Piscine', 'parking' => '🅿️ Parking',
    'clim' => '❄️ Climatisation', 'cuisine' => '🍳 Cuisine équipée',
    'tv' => '📺 Télévision', 'lave_linge' => '🧺 Lave-linge',
    'balcon' => '🏢 Balcon', 'jardin' => '🌿 Jardin',
    'securite' => '🔒 Sécurité', 'ascenseur' => '🛗 Ascenseur',
];
@endphp

{{-- ── Galerie photos ─────────────────────────────────────────────────────── --}}
<div class="gallery" id="gallery">
    <div class="gallery-track" id="galleryTrack">
        @if(count($photos) > 0)
            @foreach($photos as $p)
            <img src="{{ $p }}" alt="{{ $annonce->titre }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
            @endforeach
        @else
            <div class="gallery-placeholder"><i class="bi bi-house-door"></i></div>
        @endif
    </div>
    @if(count($photos) > 1)
    <div class="gallery-dots">
        @foreach($photos as $i => $p)
        <div class="gallery-dot {{ $i === 0 ? 'active' : '' }}" id="dot-{{ $i }}"></div>
        @endforeach
    </div>
    <div class="gallery-count" id="galleryCount">1 / {{ count($photos) }}</div>
    @endif
    <span class="gallery-badge {{ $annonce->type === 'vente' ? 'badge-vente' : ($annonce->mode_location === 'court_terme' ? 'badge-court-terme' : 'badge-location') }}">
        {{ $annonce->type === 'vente' ? 'Vente' : ($annonce->mode_location === 'court_terme' ? '🛎️ Court terme' : '📋 Location') }}
    </span>
</div>

{{-- ── Infos principales ──────────────────────────────────────────────────── --}}
<div class="detail-block">
    <div class="detail-price">
        {{ number_format($annonce->mode_location === 'court_terme' ? $annonce->prix_nuit : $annonce->prix, 0, ',', ' ') }}
        <small>{{ $devise }}{{ $annonce->mode_location === 'court_terme' ? '/nuit' : ($annonce->type === 'location' ? '/mois' : '') }}</small>
    </div>
    <div class="detail-title">{{ $annonce->titre }}</div>
    <div class="detail-loc">
        <i class="bi bi-geo-alt-fill" style="color:var(--primary)"></i>
        {{ $annonce->bien->adresse ?? '' }}{{ $annonce->bien->adresse ? ', ' : '' }}{{ $annonce->bien->ville ?? '—' }}
        @if($annonce->bien->pays && $annonce->bien->pays !== 'France'), {{ $annonce->bien->pays }}@endif
    </div>

    {{-- Méta grid --}}
    @if($annonce->bien)
    <div class="meta-grid">
        @if($annonce->bien->surface)
        <div class="meta-item">
            <i class="bi bi-rulers"></i>
            <div class="meta-val">{{ round($annonce->bien->surface) }}</div>
            <div class="meta-lbl">m²</div>
        </div>
        @endif
        @if($annonce->bien->nb_pieces)
        <div class="meta-item">
            <i class="bi bi-grid-3x3-gap"></i>
            <div class="meta-val">{{ $annonce->bien->nb_pieces }}</div>
            <div class="meta-lbl">pièces</div>
        </div>
        @endif
        @if($annonce->bien->nb_chambres)
        <div class="meta-item">
            <i class="bi bi-door-open"></i>
            <div class="meta-val">{{ $annonce->bien->nb_chambres }}</div>
            <div class="meta-lbl">chambre{{ $annonce->bien->nb_chambres > 1 ? 's' : '' }}</div>
        </div>
        @endif
        @if($annonce->nb_max_voyageurs)
        <div class="meta-item">
            <i class="bi bi-people"></i>
            <div class="meta-val">{{ $annonce->nb_max_voyageurs }}</div>
            <div class="meta-lbl">pers. max</div>
        </div>
        @elseif($annonce->bien->nb_sdb)
        <div class="meta-item">
            <i class="bi bi-droplet"></i>
            <div class="meta-val">{{ $annonce->bien->nb_sdb }}</div>
            <div class="meta-lbl">sdb</div>
        </div>
        @endif
    </div>
    @endif

    {{-- Tags --}}
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px">
        @if($annonce->bien->meuble)<span class="equip-item">🛋️ Meublé</span>@endif
        @if($annonce->prix_negociable)<span class="equip-item">💬 Prix négociable</span>@endif
        @if($annonce->bien->dpe)<span class="equip-item">⚡ DPE {{ $annonce->bien->dpe }}</span>@endif
    </div>
</div>

{{-- ── Description ────────────────────────────────────────────────────────── --}}
@if($annonce->description || $annonce->bien->description)
<div class="detail-section">
    <h5>Description</h5>
    <p class="description-text collapsed" id="descText">
        {{ $annonce->description ?? $annonce->bien->description }}
    </p>
    <button class="show-more" id="descBtn" onclick="toggleDesc()">Lire la suite</button>
</div>
@endif

{{-- ── Équipements ─────────────────────────────────────────────────────────── --}}
@if(count($equips) > 0)
<div class="detail-section">
    <h5>Équipements inclus</h5>
    <div class="equip-grid">
        @foreach($equips as $e)
        <span class="equip-item">{{ $equipsMap[$e] ?? ucfirst($e) }}</span>
        @endforeach
    </div>
</div>
@endif

{{-- ── Réservation court terme ─────────────────────────────────────────────── --}}
@if($annonce->estCourtTerme())
<div class="detail-section" id="reservationSection">
    <div class="booking-widget">
        <div class="bw-head">
            <h4>🗓️ Réserver ce séjour</h4>
            <p>Disponibilité en temps réel — Paiement sécurisé</p>
        </div>
        <div class="bw-body">
            <div class="bw-dates">
                <div class="bw-date">
                    <label>📅 Arrivée</label>
                    <input type="date" id="dateDebut" min="{{ today()->addDay()->format('Y-m-d') }}"
                           value="{{ today()->addDay()->format('Y-m-d') }}" onchange="calculerPrix()">
                </div>
                <div class="bw-date">
                    <label>📅 Départ</label>
                    <input type="date" id="dateFin" min="{{ today()->addDays(2)->format('Y-m-d') }}"
                           value="{{ today()->addDays(2)->format('Y-m-d') }}" onchange="calculerPrix()">
                </div>
            </div>
            <div class="bw-date" style="margin-bottom:10px">
                <label>👥 Nombre de voyageurs</label>
                <div style="display:flex;align-items:center;gap:10px">
                    <button type="button" onclick="chgVoyageurs(-1)" style="width:36px;height:36px;border-radius:8px;border:1.5px solid var(--border);background:#fff;font-size:1.1rem;cursor:pointer">−</button>
                    <span id="nbVoyageurs" style="font-size:.95rem;font-weight:700;flex:1;text-align:center">1</span>
                    <button type="button" onclick="chgVoyageurs(1)" style="width:36px;height:36px;border-radius:8px;border:1.5px solid var(--border);background:#fff;font-size:1.1rem;cursor:pointer">+</button>
                </div>
            </div>

            <div class="bw-summary" id="bwSummary">
                <div class="bw-sum-row"><span id="bwNuitLabel">1 nuit × {{ number_format($annonce->prix_nuit,0,',',' ') }} {{ $devise }}</span><span id="bwNuitTotal">{{ number_format($annonce->prix_nuit,0,',',' ') }}</span></div>
                <div class="bw-sum-row" style="color:var(--text-muted)"><span>Frais de service (5%)</span><span id="bwFrais">{{ number_format($annonce->prix_nuit * 0.05,0,',',' ') }}</span></div>
                <div class="bw-sum-row bw-sum-total"><span>Total</span><span id="bwTotal" style="color:var(--primary)">{{ number_format($annonce->prix_nuit * 1.05,0,',',' ') }} {{ $devise }}</span></div>
            </div>

            <div id="indispoAlert" class="alert-mob alert-mob-danger" style="display:none">
                <i class="bi bi-x-circle-fill"></i> Ces dates ne sont pas disponibles.
            </div>

            <a id="reserverBtn" href="#" class="btn-mob-primary" onclick="goReserver(event)">
                <i class="bi bi-calendar-check"></i> Réserver maintenant
            </a>
            <div style="text-align:center;margin-top:8px;font-size:.72rem;color:var(--text-muted)">
                <i class="bi bi-lock-fill" style="color:#16A34A"></i> Paiement sécurisé — Annulation gratuite 24h avant
            </div>
        </div>
    </div>
</div>
@else
{{-- Long terme / vente --}}
<div class="detail-section">
    <h5>{{ $annonce->type === 'vente' ? 'Intéressé par ce bien ?' : 'Location longue durée' }}</h5>
    @if($annonce->date_disponibilite)
    <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:12px">
        <i class="bi bi-calendar me-1"></i> Disponible à partir du {{ $annonce->date_disponibilite->format('d/m/Y') }}
    </p>
    @endif
    <a href="mailto:{{ $annonce->bien->proprietaire->email ?? '' }}" class="btn-mob-primary">
        <i class="bi bi-envelope"></i> Contacter le propriétaire
    </a>
    @if($annonce->bien->proprietaire->phone ?? null)
    <a href="tel:{{ $annonce->bien->proprietaire->phone }}" class="btn-mob-outline" style="margin-top:8px">
        <i class="bi bi-telephone"></i> Appeler
    </a>
    @endif
</div>
@endif

{{-- ── Annonces similaires ─────────────────────────────────────────────────── --}}
@if($similaires->count() > 0)
<div class="detail-section">
    <h5>Annonces similaires</h5>
    <div class="similaires-scroll">
        @foreach($similaires as $s)
        @php $sp = $s->allPhotos(); @endphp
        <a href="{{ route('mobile.detail', $s) }}" class="similaire-card">
            @if(count($sp) > 0)
                <img src="{{ $sp[0] }}" alt="{{ $s->titre }}" loading="lazy">
            @else
                <div class="sim-placeholder"><i class="bi bi-house-door"></i></div>
            @endif
            <div class="similaire-info">
                <div class="sim-price">{{ number_format($s->mode_location === 'court_terme' ? $s->prix_nuit : $s->prix, 0, ',', ' ') }} <small style="font-size:.6rem;font-weight:400">{{ $devise }}</small></div>
                <div class="sim-title">{{ Str::limit($s->titre, 25) }}</div>
                <div class="sim-loc"><i class="bi bi-geo-alt" style="font-size:.65rem"></i> {{ $s->bien->ville ?? '—' }}</div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

<div style="height:80px"></div>

{{-- ── Sticky booking bar (pour long terme / vente) ─────────────────────── --}}
@unless($annonce->estCourtTerme())
<div class="booking-sticky">
    <div class="booking-price">
        <div class="price">{{ number_format($annonce->prix, 0, ',', ' ') }} {{ $devise }}</div>
        <div class="unit">{{ $annonce->type === 'location' ? '/mois' : '' }}</div>
    </div>
    <a href="mailto:{{ $annonce->bien->proprietaire->email ?? '' }}" class="btn-mob-primary" style="width:auto;padding:12px 20px">
        <i class="bi bi-send"></i> Contacter
    </a>
</div>
@endunless

@endsection

@push('scripts')
<script>
const PRIX_NUIT = {{ $annonce->prix_nuit ?? 0 }};
const DEVISE = '{{ \App\Models\Parametre::get("paiement_devise","XOF") }}';
const NB_MAX = {{ $annonce->nb_max_voyageurs ?? 20 }};
const DISPO_URL = '{{ route("mobile.dispo", $annonce) }}';
const RESERVER_URL = '{{ route("mobile.reserver", $annonce) }}';
const DATES_OCCUPEES = @json($datesOccupees);
let nbVoyageurs = 1;

// ── Galerie swipe ──
const track = document.getElementById('galleryTrack');
const photos = {{ count($photos) }};
let currentSlide = 0, startX = 0;

if (track) {
    track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, {passive:true});
    track.addEventListener('touchend', e => {
        const diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            if (diff > 0 && currentSlide < photos - 1) currentSlide++;
            else if (diff < 0 && currentSlide > 0) currentSlide--;
            updateGallery();
        }
    });
}

function updateGallery() {
    if (!track) return;
    track.style.transform = `translateX(-${currentSlide * 100}%)`;
    document.querySelectorAll('.gallery-dot').forEach((d, i) => {
        d.classList.toggle('active', i === currentSlide);
    });
    const cnt = document.getElementById('galleryCount');
    if (cnt) cnt.textContent = (currentSlide + 1) + ' / ' + photos;
}

// ── Description expand ──
function toggleDesc() {
    const t = document.getElementById('descText');
    const b = document.getElementById('descBtn');
    t.classList.toggle('collapsed');
    b.textContent = t.classList.contains('collapsed') ? 'Lire la suite' : 'Réduire';
}

// ── Voyageurs ──
function chgVoyageurs(d) {
    nbVoyageurs = Math.max(1, Math.min(NB_MAX, nbVoyageurs + d));
    document.getElementById('nbVoyageurs').textContent = nbVoyageurs;
}

// ── Calcul prix ──
function calculerPrix() {
    const debut = document.getElementById('dateDebut')?.value;
    const fin   = document.getElementById('dateFin')?.value;
    if (!debut || !fin) return;

    const d = new Date(debut), f = new Date(fin);
    if (f <= d) {
        const newFin = new Date(d); newFin.setDate(newFin.getDate() + 1);
        document.getElementById('dateFin').value = newFin.toISOString().split('T')[0];
        return calculerPrix();
    }
    document.getElementById('dateFin').min = new Date(d.getTime() + 86400000).toISOString().split('T')[0];

    const nuits = Math.round((f - d) / 86400000);
    const sousTot = PRIX_NUIT * nuits;
    const frais   = Math.round(sousTot * 0.05);
    const total   = sousTot + frais;

    document.getElementById('bwNuitLabel').textContent = nuits + ' nuit' + (nuits > 1 ? 's' : '') + ' × ' + sousTot.toLocaleString('fr-FR') + ' ' + DEVISE;
    document.getElementById('bwNuitTotal').textContent = sousTot.toLocaleString('fr-FR');
    document.getElementById('bwFrais').textContent = frais.toLocaleString('fr-FR');
    document.getElementById('bwTotal').textContent = total.toLocaleString('fr-FR') + ' ' + DEVISE;

    // Vérifier disponibilité
    const indispo = checkDatesOccupees(debut, fin);
    document.getElementById('indispoAlert').style.display = indispo ? 'flex' : 'none';
    document.getElementById('reserverBtn').style.opacity = indispo ? '.4' : '1';
    document.getElementById('reserverBtn').style.pointerEvents = indispo ? 'none' : '';
}

function checkDatesOccupees(debut, fin) {
    const d = new Date(debut), f = new Date(fin);
    let cur = new Date(d);
    while (cur < f) {
        if (DATES_OCCUPEES.includes(cur.toISOString().split('T')[0])) return true;
        cur.setDate(cur.getDate() + 1);
    }
    return false;
}

function goReserver(e) {
    e.preventDefault();
    const debut = document.getElementById('dateDebut')?.value;
    const fin   = document.getElementById('dateFin')?.value;
    if (!debut || !fin) return;
    window.location.href = RESERVER_URL + '?debut=' + debut + '&fin=' + fin + '&voyageurs=' + nbVoyageurs;
}

// Init
document.addEventListener('DOMContentLoaded', calculerPrix);
</script>
@endpush
