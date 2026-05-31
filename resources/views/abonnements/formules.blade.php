@extends('layouts.app')
@section('title', 'Nos formules d\'abonnement')
@section('page-title', 'Choisissez votre formule')

@push('styles')
<style>
.formule-card {
    border-radius: 20px;
    border: 2px solid #E5E7EB;
    padding: 32px 28px;
    background: #fff;
    transition: .25s;
    position: relative;
    display: flex;
    flex-direction: column;
}
.formule-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,.1); }
.formule-card.populaire { border-color: #EA580C; box-shadow: 0 8px 32px rgba(234,88,12,.15); }
.formule-card.courante  { border-color: #16A34A; box-shadow: 0 8px 32px rgba(22,163,74,.15); }
.badge-pop {
    position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
    background: linear-gradient(90deg,#EA580C,#F97316); color: #fff;
    font-size: .72rem; font-weight: 800; padding: 4px 18px; border-radius: 20px;
    white-space: nowrap; letter-spacing: .04em;
}
.feature-item {
    display: flex; align-items: center; gap: 10px;
    font-size: .82rem; color: #374151; padding: 5px 0;
}
.feature-item .check { color: #16A34A; font-size: 1rem; flex-shrink: 0; }
.feature-item .cross { color: #D1D5DB; font-size: 1rem; flex-shrink: 0; }
.feature-item.dim { color: #9CA3AF; }
.prix-big { font-size: 2.6rem; font-weight: 800; line-height: 1; }
.grid-formules {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
    gap: 24px;
    max-width: 1100px;
    margin: 0 auto;
}
.upgrade-banner {
    background: linear-gradient(135deg, #FFF7ED, #FFEDD5);
    border: 1.5px solid #FED7AA;
    border-radius: 14px;
    padding: 16px 24px;
    margin-bottom: 32px;
    display: flex; align-items: center; gap: 14px;
}
</style>
@endpush

@section('content')
@php $user = auth()->user(); @endphp

{{-- Alerte upgrade requis --}}
@if(session('upgrade_requis'))
<div class="upgrade-banner">
    <i class="bi bi-arrow-up-circle-fill" style="color:#EA580C;font-size:1.5rem;flex-shrink:0"></i>
    <div>
        <div style="font-weight:700;color:#9A3412">Mise à niveau requise</div>
        <div style="font-size:.83rem;color:#C2410C">{{ session('upgrade_requis') }}</div>
    </div>
</div>
@endif

{{-- Intro --}}
<div style="text-align:center;margin-bottom:40px">
    <h2 style="font-size:1.6rem;font-weight:800;color:#111827;margin-bottom:8px">
        Des formules adaptées à chaque besoin
    </h2>
    <p style="color:#6B7280;font-size:.92rem;max-width:540px;margin:0 auto">
        Gérez vos biens, locataires et finances en toute simplicité.
        Changez de formule à tout moment, sans engagement.
    </p>
</div>

<div class="grid-formules">
@foreach($formules as $f)
@php
    $estCourante = $formuleCourante?->id === $f->id;
    $cardClass   = $estCourante ? 'courante' : ($f->populaire ? 'populaire' : '');
@endphp
<div class="formule-card {{ $cardClass }}">

    {{-- Badge populaire --}}
    @if($f->populaire && !$estCourante)
    <div class="badge-pop"><i class="bi bi-star-fill me-1"></i>Le plus populaire</div>
    @endif
    @if($estCourante)
    <div class="badge-pop" style="background:linear-gradient(90deg,#16A34A,#22C55E)">
        <i class="bi bi-check-circle-fill me-1"></i>Votre formule actuelle
    </div>
    @endif

    {{-- Icône + Nom --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;margin-top:{{ ($f->populaire||$estCourante)?'10px':'0' }}">
        <div style="width:48px;height:48px;border-radius:12px;background:{{ $f->couleur }}22;
                    display:flex;align-items:center;justify-content:center;color:{{ $f->couleur }};font-size:1.4rem">
            <i class="bi {{ $f->icone }}"></i>
        </div>
        <div>
            <div style="font-size:1.1rem;font-weight:800;color:#111827">{{ $f->nom }}</div>
            <div style="font-size:.75rem;color:#9CA3AF">{{ $f->description }}</div>
        </div>
    </div>

    {{-- Prix --}}
    <div style="margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid #F3F4F6">
        <div style="display:flex;align-items:flex-end;gap:6px">
            @if($f->prix_mensuel === 0)
            <span class="prix-big" style="color:{{ $f->couleur }}">Gratuit</span>
            @else
            <span class="prix-big" style="color:{{ $f->couleur }}">{{ number_format($f->prix_mensuel, 0, ',', ' ') }}</span>
            <span style="font-size:.95rem;font-weight:600;color:#6B7280;padding-bottom:6px">
                {{ \App\Models\User::DEVISES[$f->devise]['symbole'] ?? $f->devise }}<span style="font-weight:400">/mois</span>
            </span>
            @endif
        </div>
        @if($f->prix_annuel > 0)
        @php $economie = round((1 - $f->prix_annuel / ($f->prix_mensuel * 12)) * 100); @endphp
        <div style="font-size:.74rem;color:#16A34A;margin-top:4px;font-weight:600">
            <i class="bi bi-tag me-1"></i>
            {{ number_format($f->prix_annuel, 0, ',', ' ') }} {{ \App\Models\User::DEVISES[$f->devise]['symbole'] ?? $f->devise }}/an
            @if($economie > 0)(−{{ $economie }}%)@endif
        </div>
        @endif
    </div>

    {{-- Limites --}}
    <div style="margin-bottom:18px">
        <div style="font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">
            Limites
        </div>
        <div class="feature-item">
            <i class="bi bi-buildings check"></i>
            <span>
                <strong>{{ $f->limiteLabel('max_biens') }}</strong> bien(s) immobilier(s)
            </span>
        </div>
        <div class="feature-item">
            <i class="bi bi-people check"></i>
            <span>
                <strong>{{ $f->limiteLabel('max_locataires') }}</strong> locataire(s)
            </span>
        </div>
        @if($f->max_annonces > 0 || $f->max_annonces === -1)
        <div class="feature-item">
            <i class="bi bi-megaphone check"></i>
            <span>
                <strong>{{ $f->limiteLabel('max_annonces') }}</strong> annonce(s)
            </span>
        </div>
        @endif
    </div>

    {{-- Fonctionnalités --}}
    <div style="flex:1;margin-bottom:24px">
        <div style="font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">
            Fonctionnalités
        </div>
        @php
        $features = [
            ['has_documents',         'bi-file-earmark-text', 'Gestion des documents'],
            ['has_export_pdf',         'bi-filetype-pdf',      'Export quittances PDF'],
            ['has_interventions',      'bi-tools',             'Gestion des interventions'],
            ['has_annonces',           'bi-megaphone',         'Publication d\'annonces'],
            ['has_depenses',           'bi-wallet2',           'Suivi des dépenses'],
            ['has_notifications_sms',  'bi-bell',              'Notifications SMS/WhatsApp'],
            ['has_ia',                 'bi-robot',             'Agent IA'],
            ['has_agents',             'bi-person-badge',      'Gestion des agents'],
            ['support_prioritaire',    'bi-headset',           'Support prioritaire'],
        ];
        @endphp
        @foreach($features as [$champ, $icon, $label])
        <div class="feature-item {{ $f->$champ ? '' : 'dim' }}">
            @if($f->$champ)
            <i class="bi {{ $icon }} check"></i>
            @else
            <i class="bi bi-dash cross"></i>
            @endif
            <span>{{ $label }}</span>
        </div>
        @endforeach
    </div>

    {{-- CTA --}}
    @if($estCourante)
    <div style="text-align:center;padding:12px;border-radius:10px;background:#F0FDF4;color:#15803D;font-size:.84rem;font-weight:700">
        <i class="bi bi-check-circle-fill me-2"></i>Formule active
    </div>
    @elseif(auth()->check() && auth()->user()->role === 'proprietaire')
    <a href="{{ route('abonnements.index', ['formule' => $f->slug]) }}"
       style="display:block;text-align:center;padding:13px;border-radius:10px;font-weight:700;font-size:.88rem;
              background:{{ $f->couleur }};color:#fff;text-decoration:none;transition:.2s"
       onmouseover="this.style.opacity='.85'"
       onmouseout="this.style.opacity='1'">
        <i class="bi bi-send-fill me-2"></i>
        {{ $formuleCourante ? 'Passer au ' . $f->nom : 'Choisir ' . $f->nom }}
    </a>
    @else
    <a href="{{ route('register') }}"
       style="display:block;text-align:center;padding:13px;border-radius:10px;font-weight:700;font-size:.88rem;
              background:{{ $f->couleur }};color:#fff;text-decoration:none;transition:.2s"
       onmouseover="this.style.opacity='.85'"
       onmouseout="this.style.opacity='1'">
        <i class="bi bi-person-plus me-2"></i>Commencer avec {{ $f->nom }}
    </a>
    @endif

</div>
@endforeach
</div>

{{-- FAQ rapide --}}
<div style="max-width:700px;margin:56px auto 0;padding:0 8px">
    <h3 style="font-size:1.1rem;font-weight:800;color:#111827;text-align:center;margin-bottom:24px">
        Questions fréquentes
    </h3>
    @php
    $faq = [
        ['Puis-je changer de formule ?', 'Oui, à tout moment. Le changement prend effet lors de votre prochain renouvellement.'],
        ['Y a-t-il un engagement ?', 'Non, tous les abonnements sont mensuels sans engagement.'],
        ['Quels moyens de paiement sont acceptés ?', 'Orange Money, MTN Mobile Money, Wave, et carte bancaire.'],
        ['Que se passe-t-il à l\'expiration ?', 'Votre compte passe en lecture seule. Vos données sont conservées 90 jours.'],
    ];
    @endphp
    @foreach($faq as [$q, $r])
    <details style="border-bottom:1px solid #F3F4F6;padding:14px 0">
        <summary style="font-weight:600;font-size:.88rem;color:#1F2937;cursor:pointer;list-style:none;
                         display:flex;justify-content:space-between;align-items:center">
            {{ $q }}
            <i class="bi bi-chevron-down" style="color:#9CA3AF;font-size:.8rem"></i>
        </summary>
        <p style="margin:10px 0 0;font-size:.82rem;color:#6B7280;line-height:1.6">{{ $r }}</p>
    </details>
    @endforeach
</div>

@endsection
