@extends('layouts.mobile')
@section('title', 'Mes Réservations')
@section('page-title', 'Mes Réservations')

@push('styles')
<style>
    .lookup-box { padding: 16px; background: #fff; border-bottom: 1px solid var(--border); }
    .lookup-box p { font-size: .8rem; color: var(--text-muted); margin: 0 0 10px; }

    .resa-card { background: #fff; border-radius: 14px; overflow: hidden;
        border: 1.5px solid var(--border); margin-bottom: 12px; text-decoration: none; display: block; color: var(--text-main); }
    .resa-card-head { display: flex; align-items: center; gap: 12px; padding: 12px 14px;
        border-bottom: 1px solid var(--bg); }
    .resa-thumb { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; flex-shrink: 0; background: var(--bg); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .resa-info { flex: 1; min-width: 0; }
    .resa-titre { font-size: .85rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .resa-dates { font-size: .72rem; color: var(--text-muted); margin-top: 2px; }
    .resa-card-foot { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; }
    .resa-montant { font-size: .9rem; font-weight: 800; color: var(--primary); }
    .resa-ref { font-size: .68rem; color: var(--text-muted); font-family: monospace; }

    .status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: .7rem; font-weight: 700; }
    .status-payee, .status-confirmee { background: #DCFCE7; color: #166534; }
    .status-en_attente { background: #FEF3C7; color: #92400E; }
    .status-paiement_initie { background: #DBEAFE; color: #1E40AF; }
    .status-annulee { background: #FEE2E2; color: #991B1B; }

    .empty-state { text-align: center; padding: 50px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 3rem; opacity: .25; display: block; margin-bottom: 12px; }
</style>
@endpush

@section('content')
<div class="lookup-box">
    <p>Retrouvez vos réservations en saisissant votre adresse email</p>
    <form method="GET" action="{{ route('mobile.mes-reservations') }}" style="display:flex;gap:8px">
        <input type="email" name="email" class="input-mob" placeholder="votre@email.com"
               value="{{ request('email') }}" style="flex:1" required>
        <button type="submit" class="btn-mob-primary" style="width:auto;padding:13px 16px;flex-shrink:0">
            <i class="bi bi-search"></i>
        </button>
    </form>
</div>

<div class="mob-page">
    @if(request()->filled('email'))
        @if($reservations->isEmpty())
        <div class="empty-state">
            <i class="bi bi-bag-x"></i>
            <p>Aucune réservation trouvée pour <strong>{{ request('email') }}</strong></p>
        </div>
        @else
        <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:14px">
            {{ $reservations->count() }} réservation{{ $reservations->count() > 1 ? 's' : '' }} trouvée{{ $reservations->count() > 1 ? 's' : '' }}
        </div>
        @foreach($reservations as $r)
        @php $photos = $r->annonce->allPhotos(); $devise = \App\Models\Parametre::get('paiement_devise','XOF'); @endphp
        <a href="{{ route('mobile.confirmation', $r->token) }}" class="resa-card">
            <div class="resa-card-head">
                @if(count($photos) > 0)
                    <img src="{{ $photos[0] }}" class="resa-thumb" alt="">
                @else
                    <div class="resa-thumb"><i class="bi bi-house-door"></i></div>
                @endif
                <div class="resa-info">
                    <div class="resa-titre">{{ $r->annonce->titre }}</div>
                    <div class="resa-dates">
                        {{ $r->date_debut->format('d/m/Y') }} → {{ $r->date_fin->format('d/m/Y') }}
                        · {{ $r->nb_nuits }} nuit{{ $r->nb_nuits > 1 ? 's' : '' }}
                    </div>
                    <div class="resa-dates" style="margin-top:2px">
                        {{ $r->annonce->bien->ville ?? '' }}
                    </div>
                </div>
            </div>
            <div class="resa-card-foot">
                <div>
                    <div class="resa-montant">{{ number_format($r->montant_total,0,',',' ') }} {{ $devise }}</div>
                    <div class="resa-ref">RÉF. #{{ strtoupper(substr($r->token,0,8)) }}</div>
                </div>
                <span class="status-badge status-{{ $r->statut }}">
                    @if(in_array($r->statut,['payee','confirmee']))<i class="bi bi-check-circle-fill"></i>
                    @elseif($r->statut === 'paiement_initie')<i class="bi bi-hourglass-split"></i>
                    @elseif($r->statut === 'annulee')<i class="bi bi-x-circle-fill"></i>
                    @else<i class="bi bi-clock"></i>@endif
                    {{ $r->statutLabel() }}
                </span>
            </div>
        </a>
        @endforeach
        @endif
    @else
    <div class="empty-state">
        <i class="bi bi-bag"></i>
        <p>Entrez votre email pour retrouver vos réservations.</p>
    </div>
    @endif
</div>
@endsection
