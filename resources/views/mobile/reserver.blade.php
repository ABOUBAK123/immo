@extends('layouts.mobile')
@section('title', 'Réservation')

@section('header-left')
<a href="{{ route('mobile.detail', $annonce) }}" class="btn-mob-ghost" style="font-size:1.2rem">
    <i class="bi bi-arrow-left"></i>
</a>
@endsection
@section('page-title', 'Réserver')

@push('styles')
<style>
    .step-progress {
        display: flex; align-items: center; padding: 14px 20px;
        background: #fff; border-bottom: 1px solid var(--border); gap: 8px;
    }
    .step { display: flex; align-items: center; gap: 6px; flex: 1; }
    .step-num {
        width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .78rem; font-weight: 800;
    }
    .step-num.done  { background: #16A34A; color: #fff; }
    .step-num.active{ background: var(--primary); color: #fff; }
    .step-num.todo  { background: var(--bg); color: var(--text-muted); border: 2px solid var(--border); }
    .step-lbl { font-size: .72rem; font-weight: 600; color: var(--text-muted); }
    .step.active .step-lbl { color: var(--primary); }
    .step-sep { width: 24px; height: 2px; background: var(--border); flex-shrink: 0; }

    .summary-card {
        background: var(--primary-lt); border: 1.5px solid #FED7AA;
        border-radius: 14px; padding: 14px 16px; margin-bottom: 16px;
    }
    .summary-card .s-title { font-size: .82rem; font-weight: 800; margin-bottom: 8px; }
    .summary-row { display: flex; justify-content: space-between; font-size: .8rem; padding: 3px 0; }
    .summary-row.total { font-weight: 800; font-size: .88rem; border-top: 1px solid #FED7AA; padding-top: 6px; margin-top: 4px; color: var(--primary); }

    .form-section { background: #fff; border-radius: 14px; padding: 16px; margin-bottom: 12px; }
    .form-section h5 { font-size: .85rem; font-weight: 800; margin: 0 0 14px; }
    .form-group { margin-bottom: 14px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

    .terms-check { display: flex; gap: 10px; align-items: flex-start; }
    .terms-check input[type=checkbox] { width: 20px; height: 20px; flex-shrink: 0; accent-color: var(--primary); margin-top: 1px; }
    .terms-check label { font-size: .78rem; color: var(--text-muted); line-height: 1.5; }

    .secure-badges { display: flex; justify-content: center; gap: 16px; padding: 12px 0; flex-wrap: wrap; }
    .secure-badge { display: flex; align-items: center; gap: 5px; font-size: .7rem; color: var(--text-muted); }
    .secure-badge i { color: #16A34A; }
</style>
@endpush

@section('content')

{{-- Étapes --}}
<div class="step-progress">
    <div class="step active">
        <div class="step-num active">1</div>
        <span class="step-lbl">Dates</span>
    </div>
    <div class="step-sep"></div>
    <div class="step active">
        <div class="step-num active">2</div>
        <span class="step-lbl">Infos</span>
    </div>
    <div class="step-sep"></div>
    <div class="step">
        <div class="step-num todo">3</div>
        <span class="step-lbl">Paiement</span>
    </div>
</div>

<div class="mob-page">

    {{-- Résumé --}}
    <div class="summary-card">
        <div class="s-title">{{ $annonce->titre }}</div>
        <div class="summary-row"><span>📅 {{ \Carbon\Carbon::parse($debut)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($fin)->format('d/m/Y') }}</span><span>{{ $nbNuits }} nuit{{ $nbNuits > 1 ? 's' : '' }}</span></div>
        <div class="summary-row"><span>👥 {{ $voyageurs }} voyageur{{ $voyageurs > 1 ? 's' : '' }}</span><span>{{ number_format($annonce->prix_nuit, 0, ',', ' ') }} {{ \App\Models\Parametre::get('paiement_devise','XOF') }}/nuit</span></div>
        <div class="summary-row"><span>Frais de service</span><span>{{ number_format($frais, 0, ',', ' ') }} {{ \App\Models\Parametre::get('paiement_devise','XOF') }}</span></div>
        <div class="summary-row total"><span>Total à payer</span><span>{{ number_format($total, 0, ',', ' ') }} {{ \App\Models\Parametre::get('paiement_devise','XOF') }}</span></div>
    </div>

    {{-- Formulaire --}}
    <form method="POST" action="{{ route('mobile.reserver.store') }}">
        @csrf
        <input type="hidden" name="annonce_id"   value="{{ $annonce->id }}">
        <input type="hidden" name="date_debut"   value="{{ $debut }}">
        <input type="hidden" name="date_fin"     value="{{ $fin }}">
        <input type="hidden" name="nb_voyageurs" value="{{ $voyageurs }}">

        <div class="form-section">
            <h5>👤 Vos informations</h5>
            <div class="form-row">
                <div class="form-group">
                    <label class="input-mob-label">Prénom *</label>
                    <input type="text" name="prenom" class="input-mob" required
                           placeholder="Jean" value="{{ old('prenom') }}" autocomplete="given-name">
                </div>
                <div class="form-group">
                    <label class="input-mob-label">Nom *</label>
                    <input type="text" name="nom" class="input-mob" required
                           placeholder="Dupont" value="{{ old('nom') }}" autocomplete="family-name">
                </div>
            </div>
            <div class="form-group">
                <label class="input-mob-label">Email *</label>
                <input type="email" name="email" class="input-mob" required
                       placeholder="votre@email.com" value="{{ old('email') }}" autocomplete="email">
                <div style="font-size:.7rem;color:var(--text-muted);margin-top:4px">
                    Votre confirmation de réservation sera envoyée à cette adresse
                </div>
            </div>
            <div class="form-group">
                <label class="input-mob-label">Téléphone *</label>
                <input type="tel" name="telephone" class="input-mob" required
                       placeholder="+225 07 00 00 00 00" value="{{ old('telephone') }}" autocomplete="tel">
                <div style="font-size:.7rem;color:var(--text-muted);margin-top:4px">
                    Numéro utilisé pour le paiement mobile (Orange, MTN, Wave)
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="terms-check">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">J'accepte les <a href="#" style="color:var(--primary)">conditions générales</a> et la <a href="#" style="color:var(--primary)">politique d'annulation</a>. Le montant sera débité lors de la confirmation du paiement.</label>
            </div>
        </div>

        <button type="submit" class="btn-mob-primary">
            <i class="bi bi-arrow-right-circle"></i> Continuer vers le paiement
        </button>

        <div class="secure-badges">
            <span class="secure-badge"><i class="bi bi-lock-fill"></i> Paiement sécurisé</span>
            <span class="secure-badge"><i class="bi bi-shield-check-fill"></i> Données protégées</span>
            <span class="secure-badge"><i class="bi bi-award-fill"></i> Réservation garantie</span>
        </div>
    </form>
</div>
@endsection
