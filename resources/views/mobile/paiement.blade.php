@extends('layouts.mobile')
@section('title', 'Paiement')
@section('page-title', 'Paiement')

@section('header-left')
<a href="javascript:history.back()" class="btn-mob-ghost" style="font-size:1.2rem">
    <i class="bi bi-arrow-left"></i>
</a>
@endsection

@push('styles')
<style>
    .pay-summary {
        background: linear-gradient(135deg,#EA580C,#F97316);
        color: #fff; padding: 20px 16px; text-align: center;
    }
    .pay-amount { font-size: 2.2rem; font-weight: 800; }
    .pay-devise  { font-size: .9rem; opacity: .85; margin-top: 2px; }
    .pay-ref { font-size: .72rem; opacity: .7; margin-top: 6px; background: rgba(255,255,255,.15); padding: 4px 12px; border-radius: 20px; display: inline-block; }
    .pay-detail { padding: 14px 16px; background: #fff; border-bottom: 1px solid var(--border); }
    .pay-detail-row { display: flex; justify-content: space-between; font-size: .8rem; padding: 4px 0; color: var(--text-muted); }
    .pay-detail-row strong { color: var(--text-main); }

    .canal-section { padding: 16px; }
    .canal-section h5 { font-size: .88rem; font-weight: 800; margin: 0 0 14px; }

    .canal-card {
        display: flex; align-items: center; gap: 14px;
        padding: 16px; border: 2px solid var(--border); border-radius: 14px;
        margin-bottom: 10px; cursor: pointer; transition: all .15s;
        background: #fff; position: relative;
    }
    .canal-card.selected { border-color: var(--primary); background: var(--primary-lt); }
    .canal-card input[type=radio] { position: absolute; opacity: 0; }
    .canal-icon {
        width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
    }
    .canal-info { flex: 1; }
    .canal-name { font-size: .88rem; font-weight: 700; color: var(--text-main); }
    .canal-desc { font-size: .72rem; color: var(--text-muted); margin-top: 2px; }
    .canal-check {
        width: 22px; height: 22px; border-radius: 50%;
        border: 2px solid var(--border); display: flex; align-items: center;
        justify-content: center; flex-shrink: 0; transition: all .15s;
    }
    .canal-card.selected .canal-check { border-color: var(--primary); background: var(--primary); }
    .canal-card.selected .canal-check::after { content: '✓'; color: #fff; font-size: .72rem; font-weight: 700; }

    .phone-input-wrap { padding: 0 16px 16px; display: none; }
    .phone-input-wrap.show { display: block; }
    .phone-prefix {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        font-size: .85rem; font-weight: 700; color: var(--text-main);
    }

    #payBtn { margin: 0 16px 16px; width: calc(100% - 32px); }
    .pay-loader { display: none; }
    .secure-bar {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        font-size: .72rem; color: var(--text-muted); padding: 8px 16px 20px;
    }
    .secure-bar i { color: #16A34A; }

    /* Mock fallback */
    .mock-notice {
        margin: 0 16px 12px; padding: 12px 14px; border-radius: 10px;
        background: #FEF3C7; border: 1px solid #FDE68A; color: #92400E;
        font-size: .75rem; display: flex; gap: 8px; align-items: flex-start;
    }
</style>
@endpush

@section('content')

@php $devise = \App\Models\Parametre::get('paiement_devise','XOF'); @endphp

{{-- Montant --}}
<div class="pay-summary">
    <div class="pay-amount">{{ number_format($reservation->montant_total, 0, ',', ' ') }}</div>
    <div class="pay-devise">{{ $devise }}</div>
    <div class="pay-ref">Réf. #{{ strtoupper(substr($reservation->token, 0, 8)) }}</div>
</div>

{{-- Détail --}}
<div class="pay-detail">
    <div class="pay-detail-row"><span>{{ $reservation->annonce->titre }}</span></div>
    <div class="pay-detail-row">
        <span>{{ \Carbon\Carbon::parse($reservation->date_debut)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($reservation->date_fin)->format('d/m/Y') }}</span>
        <strong>{{ $reservation->nb_nuits }} nuit{{ $reservation->nb_nuits > 1 ? 's' : '' }}</strong>
    </div>
    <div class="pay-detail-row">
        <span>{{ number_format($reservation->prix_nuit,0,',',' ') }} × {{ $reservation->nb_nuits }}</span>
        <strong>{{ number_format($reservation->prix_nuit * $reservation->nb_nuits, 0, ',', ' ') }} {{ $devise }}</strong>
    </div>
    <div class="pay-detail-row">
        <span>Frais de service</span>
        <strong>{{ number_format($reservation->frais_service, 0, ',', ' ') }} {{ $devise }}</strong>
    </div>
</div>

@if(!\App\Models\Parametre::get('paiement_api_key'))
<div class="mock-notice">
    <i class="bi bi-info-circle-fill"></i>
    <span>Les clés de paiement ne sont pas encore configurées. Une simulation sera effectuée. En production, activez CinetPay ou Stripe dans Administration > Config. APIs.</span>
</div>
@endif

{{-- Canaux --}}
<div class="canal-section">
    <h5>💳 Choisissez votre mode de paiement</h5>

    <div class="canal-card" onclick="selectCanal('orange_money', this)" id="canal-orange_money">
        <input type="radio" name="canal" value="orange_money">
        <div class="canal-icon" style="background:#FF6600">🍊</div>
        <div class="canal-info">
            <div class="canal-name">Orange Money</div>
            <div class="canal-desc">Paiement via votre compte Orange Money</div>
        </div>
        <div class="canal-check"></div>
    </div>

    <div class="canal-card" onclick="selectCanal('mtn_money', this)" id="canal-mtn_money">
        <input type="radio" name="canal" value="mtn_money">
        <div class="canal-icon" style="background:#FFCC02">💛</div>
        <div class="canal-info">
            <div class="canal-name">MTN Mobile Money</div>
            <div class="canal-desc">Paiement via votre compte MTN MoMo</div>
        </div>
        <div class="canal-check"></div>
    </div>

    <div class="canal-card" onclick="selectCanal('wave', this)" id="canal-wave">
        <input type="radio" name="canal" value="wave">
        <div class="canal-icon" style="background:#1BA0E2">🌊</div>
        <div class="canal-info">
            <div class="canal-name">Wave</div>
            <div class="canal-desc">Paiement rapide via Wave</div>
        </div>
        <div class="canal-check"></div>
    </div>

    <div class="canal-card" onclick="selectCanal('carte', this)" id="canal-carte">
        <input type="radio" name="canal" value="carte">
        <div class="canal-icon" style="background:#1E40AF">💳</div>
        <div class="canal-info">
            <div class="canal-name">Carte bancaire</div>
            <div class="canal-desc">Visa, Mastercard — Paiement sécurisé</div>
        </div>
        <div class="canal-check"></div>
    </div>
</div>

{{-- Numéro de téléphone (mobile money) --}}
<div class="phone-input-wrap" id="phoneInputWrap">
    <label class="input-mob-label">📱 Numéro de téléphone</label>
    <div style="position:relative">
        <span class="phone-prefix">+225</span>
        <input type="tel" id="phoneInput" class="input-mob" style="padding-left:52px"
               placeholder="07 00 00 00 00" maxlength="12">
    </div>
    <div style="font-size:.7rem;color:var(--text-muted);margin-top:4px">
        Vous recevrez une demande de confirmation sur ce numéro
    </div>
</div>

{{-- Bouton payer --}}
<button id="payBtn" class="btn-mob-primary" onclick="initierPaiement()" disabled>
    <span id="payBtnText"><i class="bi bi-lock-fill"></i> Sélectionnez un mode de paiement</span>
    <span class="pay-loader" id="payLoader">
        <i class="bi bi-arrow-repeat" style="animation:spin 1s linear infinite"></i> Traitement en cours…
    </span>
</button>

<div class="secure-bar">
    <i class="bi bi-lock-fill"></i> Paiement sécurisé SSL · Données chiffrées
</div>

<div id="payError" class="alert-mob alert-mob-danger mx-3" style="display:none"></div>

<style>
@keyframes spin { from { transform: rotate(0) } to { transform: rotate(360deg) } }
</style>
@endsection

@push('scripts')
<script>
const TOKEN = '{{ $reservation->token }}';
const INITIER_URL = '{{ route("mobile.paiement.initier", $reservation->token) }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const DEVISE = '{{ $devise }}';
const MONTANT = '{{ number_format($reservation->montant_total, 0, ",", " ") }}';
let selectedCanal = '';

function selectCanal(canal, el) {
    document.querySelectorAll('.canal-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedCanal = canal;

    const mobileCanals = ['orange_money', 'mtn_money', 'wave'];
    const phoneWrap = document.getElementById('phoneInputWrap');
    phoneWrap.classList.toggle('show', mobileCanals.includes(canal));

    const btn = document.getElementById('payBtn');
    btn.disabled = false;
    const labels = {
        orange_money: '🍊 Payer via Orange Money',
        mtn_money:    '💛 Payer via MTN MoMo',
        wave:         '🌊 Payer via Wave',
        carte:        '💳 Payer par carte bancaire',
    };
    document.getElementById('payBtnText').innerHTML = '<i class="bi bi-lock-fill"></i> ' + labels[canal] + ' — ' + MONTANT + ' ' + DEVISE;
}

async function initierPaiement() {
    if (!selectedCanal) return;

    const btn = document.getElementById('payBtn');
    btn.disabled = true;
    document.getElementById('payBtnText').style.display = 'none';
    document.getElementById('payLoader').style.display = 'inline-flex';
    document.getElementById('payError').style.display = 'none';

    const body = { canal: selectedCanal };
    if (['orange_money','mtn_money','wave'].includes(selectedCanal)) {
        body.telephone = document.getElementById('phoneInput').value;
    }

    try {
        const res = await fetch(INITIER_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body),
        });
        const data = await res.json();

        if (data.ok && data.url) {
            window.location.href = data.url;
        } else if (data.ok) {
            // Simulation (pas de clé configurée) — aller direct à la confirmation
            window.location.href = '{{ route("mobile.confirmation", $reservation->token) }}';
        } else {
            throw new Error(data.message || 'Erreur lors de l\'initiation du paiement.');
        }
    } catch (e) {
        document.getElementById('payError').textContent = e.message;
        document.getElementById('payError').style.display = 'flex';
        document.getElementById('payBtnText').style.display = 'inline-flex';
        document.getElementById('payLoader').style.display = 'none';
        btn.disabled = false;
    }
}
</script>
@endpush
