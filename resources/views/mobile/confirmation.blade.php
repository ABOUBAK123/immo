@extends('layouts.mobile')
@section('title', 'Confirmation')
@section('page-title', 'Confirmation')

@push('styles')
<style>
    body { background: #fff; }
    .confetti-wrap { position: fixed; inset: 0; pointer-events: none; z-index: 200; }

    .success-hero {
        text-align: center; padding: 40px 20px 30px;
        background: linear-gradient(180deg, #DCFCE7 0%, #fff 100%);
    }
    .success-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg,#16A34A,#22C55E);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; margin: 0 auto 16px;
        box-shadow: 0 0 0 12px rgba(22,163,74,.1), 0 0 0 24px rgba(22,163,74,.05);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%,100% { box-shadow: 0 0 0 12px rgba(22,163,74,.1), 0 0 0 24px rgba(22,163,74,.05); }
        50%      { box-shadow: 0 0 0 16px rgba(22,163,74,.15), 0 0 0 32px rgba(22,163,74,.07); }
    }
    .success-hero h2 { font-size: 1.3rem; font-weight: 800; color: #166534; margin: 0 0 6px; }
    .success-hero p  { font-size: .85rem; color: #4B5563; margin: 0; }
    .ref-badge {
        display: inline-block; margin-top: 14px;
        background: #166534; color: #fff; padding: 6px 16px;
        border-radius: 20px; font-size: .78rem; font-weight: 700;
        font-family: monospace; letter-spacing: .05em;
    }

    .detail-card { margin: 0 16px 12px; background: #fff; border-radius: 14px; overflow: hidden; border: 1.5px solid var(--border); }
    .dc-head { padding: 12px 16px; background: var(--bg); border-bottom: 1px solid var(--border); }
    .dc-head span { font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); }
    .dc-body { padding: 14px 16px; }
    .dc-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid var(--bg); font-size: .83rem; }
    .dc-row:last-child { border: none; }
    .dc-row .label { color: var(--text-muted); }
    .dc-row .value { font-weight: 600; text-align: right; max-width: 60%; }

    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 20px; font-size: .75rem; font-weight: 700;
    }
    .status-payee    { background: #DCFCE7; color: #166534; }
    .status-confirmee{ background: #DCFCE7; color: #166534; }
    .status-en_attente     { background: #FEF3C7; color: #92400E; }
    .status-paiement_initie{ background: #DBEAFE; color: #1E40AF; }
    .status-annulee  { background: #FEE2E2; color: #991B1B; }

    .actions-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 0 16px 16px; }
    .share-btn { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 14px 10px; background: #fff; border: 1.5px solid var(--border); border-radius: 12px; cursor: pointer; font-size: .75rem; font-weight: 600; color: var(--text-muted); transition: all .15s; text-decoration: none; }
    .share-btn i { font-size: 1.3rem; }
    .share-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-lt); }
    .share-btn.primary { background: var(--primary); border-color: var(--primary); color: #fff; }
    .share-btn.primary:hover { background: var(--primary-dk); }
</style>
@endpush

@section('content')

@php
$devise = \App\Models\Parametre::get('paiement_devise','XOF');
$statusClass = 'status-' . $reservation->statut;
@endphp

{{-- Icône succès --}}
<div class="success-hero">
    <div class="success-icon">✅</div>
    @if(in_array($reservation->statut, ['payee','confirmee']))
    <h2>Réservation confirmée !</h2>
    <p>Votre séjour est réservé. Un email de confirmation vous a été envoyé.</p>
    @elseif($reservation->statut === 'paiement_initie')
    <h2>Paiement en cours…</h2>
    <p>Votre paiement est en cours de traitement. Vérifiez votre téléphone.</p>
    @else
    <h2>Réservation créée</h2>
    <p>Finalisez votre paiement pour confirmer le séjour.</p>
    @endif
    <div class="ref-badge">RÉF. #{{ strtoupper(substr($reservation->token, 0, 8)) }}</div>
</div>

{{-- Statut --}}
<div style="text-align:center;padding:8px 0 16px">
    <span class="status-badge {{ $statusClass }}">
        @if(in_array($reservation->statut,['payee','confirmee']))<i class="bi bi-check-circle-fill"></i>
        @elseif($reservation->statut === 'paiement_initie')<i class="bi bi-arrow-repeat"></i>
        @else<i class="bi bi-clock"></i>@endif
        {{ $reservation->statutLabel() }}
    </span>
</div>

{{-- Détails séjour --}}
<div class="detail-card">
    <div class="dc-head"><span>📅 Détails du séjour</span></div>
    <div class="dc-body">
        <div class="dc-row">
            <span class="label">Logement</span>
            <span class="value">{{ Str::limit($reservation->annonce->titre, 30) }}</span>
        </div>
        <div class="dc-row">
            <span class="label">Arrivée</span>
            <span class="value">{{ $reservation->date_debut->format('D d M Y') }}</span>
        </div>
        <div class="dc-row">
            <span class="label">Départ</span>
            <span class="value">{{ $reservation->date_fin->format('D d M Y') }}</span>
        </div>
        <div class="dc-row">
            <span class="label">Durée</span>
            <span class="value">{{ $reservation->nb_nuits }} nuit{{ $reservation->nb_nuits > 1 ? 's' : '' }}</span>
        </div>
        <div class="dc-row">
            <span class="label">Voyageurs</span>
            <span class="value">{{ $reservation->nb_voyageurs }}</span>
        </div>
        @if($reservation->annonce->bien)
        <div class="dc-row">
            <span class="label">Adresse</span>
            <span class="value">{{ $reservation->annonce->bien->adresse }}, {{ $reservation->annonce->bien->ville }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Détails paiement --}}
<div class="detail-card">
    <div class="dc-head"><span>💳 Paiement</span></div>
    <div class="dc-body">
        <div class="dc-row">
            <span class="label">{{ $reservation->nb_nuits }} nuit{{ $reservation->nb_nuits > 1 ? 's' : '' }} × {{ number_format($reservation->prix_nuit,0,',',' ') }}</span>
            <span class="value">{{ number_format($reservation->prix_nuit * $reservation->nb_nuits, 0, ',', ' ') }} {{ $devise }}</span>
        </div>
        <div class="dc-row">
            <span class="label">Frais de service</span>
            <span class="value">{{ number_format($reservation->frais_service, 0, ',', ' ') }} {{ $devise }}</span>
        </div>
        <div class="dc-row" style="font-weight:800;font-size:.9rem">
            <span>Total payé</span>
            <span style="color:var(--primary)">{{ number_format($reservation->montant_total, 0, ',', ' ') }} {{ $devise }}</span>
        </div>
        @if($reservation->canal_paiement)
        <div class="dc-row">
            <span class="label">Via</span>
            <span class="value">{{ $reservation->canalLabel() }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Infos voyageur --}}
<div class="detail-card">
    <div class="dc-head"><span>👤 Vos informations</span></div>
    <div class="dc-body">
        <div class="dc-row"><span class="label">Nom</span><span class="value">{{ $reservation->prenom }} {{ $reservation->nom }}</span></div>
        <div class="dc-row"><span class="label">Email</span><span class="value">{{ $reservation->email }}</span></div>
        <div class="dc-row"><span class="label">Téléphone</span><span class="value">{{ $reservation->telephone }}</span></div>
    </div>
</div>

{{-- Actions --}}
<div class="actions-grid">
    <button class="share-btn" onclick="partagerReservation()">
        <i class="bi bi-share"></i> Partager
    </button>
    <button class="share-btn" onclick="copierRef()">
        <i class="bi bi-clipboard"></i> Copier réf.
    </button>
    <a href="{{ route('mobile.mes-reservations') }}?email={{ urlencode($reservation->email) }}" class="share-btn">
        <i class="bi bi-bag"></i> Mes réservations
    </a>
    <a href="{{ route('mobile.index') }}" class="share-btn primary">
        <i class="bi bi-house-fill"></i> Accueil
    </a>
</div>

@if(!in_array($reservation->statut, ['payee','confirmee']))
<div style="padding:0 16px 20px">
    <a href="{{ route('mobile.paiement', $reservation->token) }}" class="btn-mob-primary">
        <i class="bi bi-credit-card"></i> Finaliser le paiement
    </a>
</div>
@endif
@endsection

@push('scripts')
<script>
function copierRef() {
    navigator.clipboard.writeText('#{{ strtoupper(substr($reservation->token, 0, 8)) }}').then(() => {
        alert('Référence copiée !');
    });
}
function partagerReservation() {
    if (navigator.share) {
        navigator.share({
            title: 'Ma réservation ImmoGest',
            text: 'Réservation {{ strtoupper(substr($reservation->token, 0, 8)) }} — {{ Str::limit($reservation->annonce->titre, 40) }}',
            url: window.location.href
        });
    } else {
        copierRef();
    }
}

// Confettis si payée
@if(in_array($reservation->statut,['payee','confirmee']))
(function() {
    const colors = ['#F97316','#EA580C','#16A34A','#2563EB','#7C3AED','#FBBF24'];
    for (let i = 0; i < 60; i++) {
        const c = document.createElement('div');
        c.style.cssText = `position:fixed;width:8px;height:8px;border-radius:2px;
            background:${colors[i%colors.length]};top:-10px;
            left:${Math.random()*100}%;
            animation:fall ${1.5 + Math.random()*2}s ease-in ${Math.random()*1}s forwards;
            transform:rotate(${Math.random()*360}deg);z-index:9999;pointer-events:none`;
        document.body.appendChild(c);
    }
    const style = document.createElement('style');
    style.textContent = '@keyframes fall { to { transform: translateY(100vh) rotate(720deg); opacity: 0; } }';
    document.head.appendChild(style);
})();
@endif
</script>
@endpush
