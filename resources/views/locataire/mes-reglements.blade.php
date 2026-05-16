@extends('layouts.app')
@section('title', 'Mes règlements')
@section('page-title', 'Mon espace')

@push('styles')
<style>
.loc-tab {
    display:inline-flex;align-items:center;gap:6px;padding:9px 18px;
    font-size:.82rem;font-weight:600;border-bottom:2.5px solid transparent;
    text-decoration:none;color:#6B7280;white-space:nowrap;transition:.15s;
}
.loc-tab:hover  { color:#111827; border-bottom-color:#D1D5DB; }
.loc-tab.active { color:#2563EB; border-bottom-color:#2563EB; }
.loc-tab .badge-tab {
    background:#DC2626;color:#fff;border-radius:99px;
    padding:1px 6px;font-size:.6rem;font-weight:700;
}
.canal-card {
    display:flex;flex-direction:column;align-items:center;gap:8px;
    padding:16px 12px;border:2px solid #E5E7EB;border-radius:12px;
    cursor:pointer;transition:.2s;background:#fff;text-align:center;
}
.canal-card:hover  { transform:translateY(-2px);box-shadow:0 4px 14px rgba(0,0,0,.1); }
.canal-card.active { border-width:2.5px; }
.canal-emoji { font-size:1.9rem;line-height:1; }
.canal-label { font-size:.76rem;font-weight:700;color:#374151; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
.pulse { animation:pulse 1.2s ease-in-out infinite; }
</style>
@endpush

@section('topbar-actions')
<span style="font-size:.8rem;color:#6B7280">
    {{ now()->isoFormat('dddd D MMMM YYYY') }}
</span>
@endsection

@section('content')
@php
    $user       = auth()->user();
    $devSymbole = \App\Models\User::DEVISES[$user->devise ?? 'XOF']['symbole'] ?? ($user->devise ?? 'XOF');
    $moisY      = now()->year;
    $moisM      = now()->month;
    $nbRetard   = $paiements->filter(fn($p) => $p->statut === 'en_attente' && $p->date_echeance->isPast())->count();
@endphp

{{-- ── Onglets de navigation Mon espace ────────────────────────────── --}}
<div style="display:flex;border-bottom:2px solid #E5E7EB;margin-bottom:24px;overflow-x:auto;gap:0">
    <a href="{{ route('dashboard') }}" class="loc-tab">
        <i class="bi bi-grid-1x2"></i> Tableau de bord
    </a>
    @if(\App\Models\ProfilConfig::isActif('locataire','location'))
    <a href="{{ route('locations.index') }}" class="loc-tab">
        <i class="bi bi-house-check"></i> Mon bail
    </a>
    @endif
    <a href="{{ route('locataire.reglements') }}" class="loc-tab active">
        <i class="bi bi-receipt"></i> Mes règlements
        @if($nbRetard > 0)
        <span class="badge-tab">{{ $nbRetard }}</span>
        @endif
    </a>
    @if(\App\Models\ProfilConfig::isActif('locataire','interventions'))
    <a href="{{ route('interventions.index') }}" class="loc-tab">
        <i class="bi bi-tools"></i> Mes demandes
    </a>
    @endif
</div>

@if(!$location)
{{-- Aucun bail actif --}}
<div class="card-immo" style="padding:60px;text-align:center">
    <i class="bi bi-house-door" style="font-size:3rem;color:#E5E7EB"></i>
    <p style="color:#9CA3AF;margin:16px 0 0;font-size:.88rem">Vous n'avez pas encore de bail actif.</p>
</div>
@else

{{-- ── Cartes statut ─────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

    {{-- Dernier paiement confirmé --}}
    @if($dernierPaye)
    <div style="background:linear-gradient(135deg,#F0FDF4,#DCFCE7);border:1px solid #BBF7D0;
                border-radius:14px;padding:18px 20px;display:flex;align-items:flex-start;gap:14px">
        <div style="width:44px;height:44px;border-radius:12px;background:#16A34A;display:flex;
                    align-items:center;justify-content:center;color:#fff;font-size:1.3rem;flex-shrink:0">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-size:.72rem;font-weight:700;color:#15803D;text-transform:uppercase;letter-spacing:.04em">
                Dernier règlement
            </div>
            <div style="font-size:1rem;font-weight:800;color:#15803D;margin-top:3px">
                {{ ucfirst($dernierPaye->date_echeance->isoFormat('MMMM YYYY')) }} — Payé ✓
            </div>
            <div style="font-size:.8rem;color:#166534;margin-top:3px">
                {{ number_format($dernierPaye->montant, 0, ',', ' ') }} {{ $devSymbole }}
                @if($dernierPaye->date_paiement)
                · le {{ $dernierPaye->date_paiement->isoFormat('D MMM YYYY') }}
                @endif
            </div>
            @if($dernierPaye->quittance)
            <a href="{{ route('quittances.download', $dernierPaye->quittance) }}"
               style="display:inline-flex;align-items:center;gap:5px;margin-top:10px;
                      background:#16A34A;color:#fff;padding:6px 13px;border-radius:7px;
                      font-size:.73rem;font-weight:700;text-decoration:none">
                <i class="bi bi-file-earmark-pdf"></i> Quittance PDF
            </a>
            @endif
        </div>
    </div>
    @else
    <div style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:12px">
        <div style="width:44px;height:44px;border-radius:12px;background:#E5E7EB;display:flex;align-items:center;justify-content:center;color:#9CA3AF;font-size:1.3rem;flex-shrink:0">
            <i class="bi bi-receipt"></i>
        </div>
        <div style="color:#9CA3AF;font-size:.83rem">Aucun règlement enregistré pour l'instant.</div>
    </div>
    @endif

    {{-- Prochaine échéance --}}
    @if($prochaine)
    @php
        $estEnRetard = $prochaine->date_echeance->isPast();
        $bgCard  = $estEnRetard ? 'linear-gradient(135deg,#FFF1F2,#FFE4E6)' : 'linear-gradient(135deg,#FFFBEB,#FEF3C7)';
        $bdCard  = $estEnRetard ? '#FECDD3' : '#FDE68A';
        $icColor = $estEnRetard ? '#DC2626' : '#D97706';
        $icon    = $estEnRetard ? 'bi-exclamation-triangle-fill' : 'bi-calendar-event';
        $label   = $estEnRetard ? 'Loyer en retard' : 'Prochaine échéance';
    @endphp
    <div style="background:{{ $bgCard }};border:1px solid {{ $bdCard }};border-radius:14px;padding:18px 20px;
                display:flex;align-items:flex-start;gap:14px">
        <div style="width:44px;height:44px;border-radius:12px;background:{{ $icColor }};display:flex;
                    align-items:center;justify-content:center;color:#fff;font-size:1.3rem;flex-shrink:0">
            <i class="bi {{ $icon }}"></i>
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-size:.72rem;font-weight:700;color:{{ $icColor }};text-transform:uppercase;letter-spacing:.04em">
                {{ $label }}
            </div>
            <div style="font-size:1rem;font-weight:800;color:{{ $icColor }};margin-top:3px">
                {{ ucfirst($prochaine->date_echeance->isoFormat('MMMM YYYY')) }}
            </div>
            <div style="font-size:.8rem;color:{{ $icColor }};margin-top:3px">
                {{ number_format($prochaine->montant, 0, ',', ' ') }} {{ $devSymbole }}
                @if($estEnRetard)
                · {{ $prochaine->date_echeance->diffForHumans() }}
                @else
                · le {{ $prochaine->date_echeance->format('d/m/Y') }}
                @endif
            </div>
            <button onclick="ouvrirPaiement({{ $prochaine->id }}, '{{ addslashes($location->bien->titre) }}', {{ $prochaine->montant }}, '{{ $devSymbole }}')"
                    style="display:inline-flex;align-items:center;gap:5px;margin-top:10px;
                           background:{{ $icColor }};color:#fff;padding:7px 14px;border-radius:8px;
                           font-size:.76rem;font-weight:700;border:none;cursor:pointer">
                <i class="bi bi-phone-fill"></i> Payer maintenant
            </button>
        </div>
    </div>
    @else
    <div style="background:linear-gradient(135deg,#F0FDF4,#DCFCE7);border:1px solid #BBF7D0;
                border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px">
        <div style="width:44px;height:44px;border-radius:12px;background:#16A34A;display:flex;align-items:center;
                    justify-content:center;color:#fff;font-size:1.3rem;flex-shrink:0">
            <i class="bi bi-check2-all"></i>
        </div>
        <div>
            <div style="font-weight:700;color:#15803D;font-size:.9rem">Tout est à jour</div>
            <div style="font-size:.78rem;color:#166534;margin-top:2px">Aucune échéance en attente.</div>
        </div>
    </div>
    @endif

</div>

{{-- ── Récapitulatif KPIs ────────────────────────────────────────────── --}}
@php
    $totalPaye    = $paiements->where('statut','paye')->sum('montant');
    $totalAttente = $paiements->where('statut','en_attente')->sum('montant');
    $nbPaye       = $paiements->where('statut','paye')->count();
    $nbAttente    = $paiements->where('statut','en_attente')->count();
@endphp
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
    <div class="card-immo stat-card" style="padding:14px 16px">
        <div class="stat-icon" style="background:#F0FDF4;color:#16A34A;width:34px;height:34px;font-size:.9rem">
            <i class="bi bi-check-circle"></i>
        </div>
        <div>
            <div class="stat-val" style="color:#16A34A;font-size:1.1rem">{{ number_format($totalPaye, 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">Réglé ({{ $nbPaye }} mois)</div>
        </div>
    </div>
    <div class="card-immo stat-card" style="padding:14px 16px">
        <div class="stat-icon" style="background:#FFFBEB;color:#D97706;width:34px;height:34px;font-size:.9rem">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
            <div class="stat-val" style="color:#D97706;font-size:1.1rem">{{ number_format($totalAttente, 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">Restant ({{ $nbAttente }} mois)</div>
        </div>
    </div>
    <div class="card-immo stat-card" style="padding:14px 16px">
        <div class="stat-icon" style="background:#FFF1F2;color:#DC2626;width:34px;height:34px;font-size:.9rem">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div>
            <div class="stat-val" style="color:{{ $nbRetard > 0 ? '#DC2626' : '#9CA3AF' }};font-size:1.1rem">{{ $nbRetard }}</div>
            <div class="stat-label">En retard</div>
        </div>
    </div>
</div>

{{-- ── Tableau historique complet ────────────────────────────────────── --}}
<div class="card-immo">
    <div style="padding:14px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:.9rem;font-weight:700">
            <i class="bi bi-clock-history me-2" style="color:#6B7280"></i>Loyers payés
        </span>
        <span style="font-size:.75rem;color:#9CA3AF" id="tabCount">{{ $paiements->where('statut','paye')->count() }} payé(s)</span>
    </div>

    @php $paiementsPayes = $paiements->where('statut','paye'); @endphp

    @if($paiementsPayes->isEmpty())
    <div style="padding:60px;text-align:center">
        <i class="bi bi-check2-circle" style="font-size:2.5rem;color:#E5E7EB"></i>
        <p style="color:#9CA3AF;margin:12px 0 0;font-size:.85rem">Aucun loyer payé pour l'instant.</p>
    </div>
    @else
    <table class="table-immo">
        <thead>
            <tr>
                <th>Période</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Réglé le</th>
                <th>Quittance</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($paiementsPayes as $p)
        @php
            $estEnRetard    = $p->statut === 'en_attente' && $p->date_echeance->isPast();
            $estMoisCourant = $p->date_echeance->year === $moisY && $p->date_echeance->month === $moisM;
            $rowBg = $estEnRetard
                ? 'background:#FFF1F2'
                : ($p->statut === 'paye' ? 'background:#F0FDF4' : ($estMoisCourant ? 'background:#FFFBEB' : ''));
        @endphp
        <tr style="{{ $rowBg }}">
            <td>
                <div style="display:flex;align-items:center;gap:7px">
                    <div style="font-weight:700;font-size:.85rem">
                        {{ ucfirst($p->date_echeance->isoFormat('MMMM YYYY')) }}
                    </div>
                    @if($estMoisCourant)
                    <span style="font-size:.62rem;font-weight:700;background:#DBEAFE;color:#1E40AF;
                                 border:1px solid #BFDBFE;border-radius:4px;padding:1px 5px">Mois en cours</span>
                    @endif
                    @if($estEnRetard)
                    <span style="font-size:.62rem;font-weight:700;background:#FEE2E2;color:#B91C1C;
                                 border:1px solid #FECACA;border-radius:4px;padding:1px 5px">Retard</span>
                    @endif
                </div>
                <div style="font-size:.7rem;color:#9CA3AF;margin-top:1px">
                    Échéance : {{ $p->date_echeance->format('d/m/Y') }}
                </div>
            </td>
            <td style="font-weight:700;font-size:.9rem">
                {{ number_format($p->montant, 0, ',', ' ') }} {{ $devSymbole }}
            </td>
            <td>
                @if($p->statut === 'paye')
                <span class="badge-pill badge-success" style="font-size:.72rem">
                    <span style="width:6px;height:6px;border-radius:50%;background:#16A34A;display:inline-block;margin-right:4px"></span>
                    Payé
                    @if($p->canal_paiement && $p->methode_paiement === 'mobile_money')
                    · {{ \App\Models\Paiement::CANAUX_MOBILE[$p->canal_paiement]['emoji'] ?? '' }}
                    @endif
                </span>
                @elseif($estEnRetard)
                <span class="badge-pill badge-danger" style="font-size:.72rem">
                    <span style="width:6px;height:6px;border-radius:50%;background:#DC2626;display:inline-block;margin-right:4px"></span>
                    En retard
                </span>
                @else
                <span class="badge-pill badge-warning" style="font-size:.72rem">
                    <span style="width:6px;height:6px;border-radius:50%;background:#D97706;display:inline-block;margin-right:4px"></span>
                    En attente
                </span>
                @endif
            </td>
            <td style="font-size:.8rem;color:#6B7280">
                @if($p->date_paiement)
                {{ $p->date_paiement->isoFormat('D MMM YYYY') }}
                @if($p->methode_paiement)
                <div style="font-size:.7rem;color:#9CA3AF">
                    {{ $p->methode_paiement === 'mobile_money'
                        ? ucfirst(str_replace('_',' ', $p->canal_paiement ?? 'Mobile'))
                        : ucfirst($p->methode_paiement) }}
                </div>
                @endif
                @else
                <span style="color:#D1D5DB">—</span>
                @endif
            </td>
            <td>
                @if($p->quittance)
                <div style="display:flex;flex-direction:column;gap:4px">
                    <span style="font-size:.68rem;font-weight:700;color:#6B7280;font-family:monospace">{{ $p->quittance->numero }}</span>
                    <div style="display:flex;gap:4px">
                        <a href="{{ route('quittances.pdf', $p->quittance) }}" target="_blank"
                           style="display:inline-flex;align-items:center;gap:3px;font-size:.7rem;
                                  color:#2563EB;font-weight:600;text-decoration:none;
                                  background:#EFF6FF;border:1px solid #BFDBFE;border-radius:5px;padding:3px 7px">
                            <i class="bi bi-eye"></i> Voir
                        </a>
                        <a href="{{ route('quittances.download', $p->quittance) }}"
                           style="display:inline-flex;align-items:center;gap:3px;font-size:.7rem;
                                  color:#16A34A;font-weight:600;text-decoration:none;
                                  background:#F0FDF4;border:1px solid #BBF7D0;border-radius:5px;padding:3px 7px">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                    </div>
                </div>
                @else
                <span style="color:#D1D5DB;font-size:.8rem">—</span>
                @endif
            </td>
            <td style="white-space:nowrap">
                @if($p->statut !== 'paye')
                <button onclick="ouvrirPaiement({{ $p->id }}, '{{ addslashes($location->bien->titre) }}', {{ $p->montant }}, '{{ $devSymbole }}')"
                        style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;
                               background:linear-gradient(135deg,#EA580C,#F97316);color:#fff;
                               border:none;border-radius:8px;font-size:.75rem;font-weight:700;
                               cursor:pointer;box-shadow:0 2px 8px rgba(234,88,12,.3)">
                    <i class="bi bi-phone-fill"></i> Payer
                </button>
                @else
                <span style="font-size:.72rem;color:#9CA3AF">—</span>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ── Loyers en attente ─────────────────────────────────────────────── --}}
@php $paiementsEnAttente = $paiements->where('statut','en_attente'); @endphp
@if($paiementsEnAttente->isNotEmpty())
<div class="card-immo" style="margin-top:16px">
    <button onclick="toggleAttente()" type="button"
            style="width:100%;padding:14px 20px;border:none;background:none;text-align:left;cursor:pointer;
                   display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #F3F4F6">
        <span style="font-size:.9rem;font-weight:700;display:flex;align-items:center;gap:8px">
            <i class="bi bi-hourglass-split me-2" style="color:#D97706"></i>Loyers en attente
            <span style="font-size:.75rem;color:#9CA3AF">{{ $paiementsEnAttente->count() }} en attente</span>
        </span>
        <i class="bi bi-chevron-down" id="attenteChevron" style="color:#6B7280;transition:.2s;transform:rotate(0deg)"></i>
    </button>

    <div id="attenteContent" style="display:none">
        <table class="table-immo">
            <thead>
                <tr>
                    <th>Période</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Échéance</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($paiementsEnAttente as $p)
            @php
                $estEnRetard = $p->date_echeance->isPast();
                $rowBg = $estEnRetard ? 'background:#FFF1F2' : 'background:#FFFBEB';
            @endphp
            <tr style="{{ $rowBg }}">
                <td>
                    <div style="display:flex;align-items:center;gap:7px">
                        <div style="font-weight:700;font-size:.85rem">
                            {{ ucfirst($p->date_echeance->isoFormat('MMMM YYYY')) }}
                        </div>
                        @if($estEnRetard)
                        <span style="font-size:.62rem;font-weight:700;background:#FEE2E2;color:#B91C1C;
                                     border:1px solid #FECACA;border-radius:4px;padding:1px 5px">Retard</span>
                        @endif
                    </div>
                    <div style="font-size:.7rem;color:#9CA3AF;margin-top:1px">
                        Échéance : {{ $p->date_echeance->format('d/m/Y') }}
                    </div>
                </td>
                <td style="font-weight:700;font-size:.9rem">
                    {{ number_format($p->montant, 0, ',', ' ') }} {{ $devSymbole }}
                </td>
                <td>
                    @if($estEnRetard)
                    <span class="badge-pill badge-danger" style="font-size:.72rem">
                        <span style="width:6px;height:6px;border-radius:50%;background:#DC2626;display:inline-block;margin-right:4px"></span>
                        En retard
                    </span>
                    @else
                    <span class="badge-pill badge-warning" style="font-size:.72rem">
                        <span style="width:6px;height:6px;border-radius:50%;background:#D97706;display:inline-block;margin-right:4px"></span>
                        En attente
                    </span>
                    @endif
                </td>
                <td style="font-size:.8rem;color:#6B7280">
                    {{ $p->date_echeance->diffForHumans() }}
                </td>
                <td style="white-space:nowrap">
                    <button onclick="ouvrirPaiement({{ $p->id }}, '{{ addslashes($location->bien->titre) }}', {{ $p->montant }}, '{{ $devSymbole }}')"
                            style="background:#D97706;color:#fff;border:none;border-radius:6px;padding:5px 12px;
                                   font-size:.75rem;font-weight:600;cursor:pointer;text-decoration:none">
                        <i class="bi bi-phone-fill"></i> Payer
                    </button>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endif {{-- end $location --}}

{{-- ════ MODAL PAIEMENT MOBILE ════════════════════════════════════════ --}}
<div class="modal fade" id="modalPaiement" tabindex="-1" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content" style="border-radius:18px;border:none;box-shadow:0 24px 60px rgba(0,0,0,.18)">
            <div style="padding:20px 22px 0;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h5 style="font-size:.95rem;font-weight:800;margin:0">Payer mon loyer</h5>
                    <div id="mpBienInfo" style="font-size:.78rem;color:#9CA3AF;margin-top:2px"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:.75rem"></button>
            </div>
            <div style="padding:20px 22px">
                <div style="background:linear-gradient(135deg,#FFEDD5,#FEF3C7);border-radius:12px;padding:14px 16px;
                            margin-bottom:20px;display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:.82rem;color:#92400E;font-weight:600">Montant à payer</span>
                    <span id="mpMontant" style="font-size:1.4rem;font-weight:800;color:#C2410C"></span>
                </div>
                <div style="font-size:.78rem;font-weight:700;color:#374151;margin-bottom:10px">Choisissez votre moyen de paiement</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px" id="mpCanalGrid">
                    @foreach(\App\Models\Paiement::CANAUX_MOBILE as $code => $info)
                    <div class="canal-card" data-canal="{{ $code }}" style="border-color:#E5E7EB"
                         onclick="selectCanal('{{ $code }}','{{ $info['color'] }}','{{ $info['bg'] }}')">
                        <div class="canal-emoji">{{ $info['emoji'] }}</div>
                        <div class="canal-label">{{ $info['label'] }}</div>
                    </div>
                    @endforeach
                </div>
                <div id="mpPhoneField" style="display:none;margin-bottom:16px">
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:6px">
                        <i class="bi bi-phone me-1"></i>Numéro de téléphone
                    </label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <span style="background:#F3F4F6;border:1px solid #E5E7EB;border-radius:8px;padding:9px 12px;font-size:.82rem;color:#374151;font-weight:600;white-space:nowrap">+225</span>
                        <input type="tel" id="mpTelephone" placeholder="07 XX XX XX XX"
                               style="flex:1;border:1px solid #E5E7EB;border-radius:8px;padding:9px 12px;font-size:.83rem;outline:none;font-family:inherit"
                               onfocus="this.style.borderColor='#EA580C'" onblur="this.style.borderColor='#E5E7EB'">
                    </div>
                </div>
                <div id="mpMessage" style="display:none;margin-bottom:14px;font-size:.8rem;border-radius:8px;padding:10px 12px"></div>
                <button id="mpBtn" onclick="lancerPaiement()" disabled
                        style="width:100%;padding:12px;border:none;border-radius:10px;font-size:.9rem;font-weight:700;
                               background:#E5E7EB;color:#9CA3AF;cursor:not-allowed;transition:.2s;
                               display:flex;align-items:center;justify-content:center;gap:8px">
                    <i class="bi bi-lock-fill"></i> Choisissez un moyen de paiement
                </button>
                <div style="margin-top:12px;text-align:center;font-size:.72rem;color:#9CA3AF">
                    <i class="bi bi-shield-check me-1" style="color:#16A34A"></i>
                    Paiement sécurisé — Quittance générée automatiquement
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle affichage des loyers en attente
function toggleAttente() {
    const content = document.getElementById('attenteContent');
    const chevron = document.getElementById('attenteChevron');
    const isHidden = content.style.display === 'none';

    content.style.display = isHidden ? 'block' : 'none';
    chevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
}

let _paiementId   = null;
let _canalChoisi  = null;
const _canalsMob  = ['orange_money','mtn_money','wave'];

function ouvrirPaiement(id, titre, montant, sym) {
    _paiementId  = id;
    _canalChoisi = null;
    document.getElementById('mpBienInfo').textContent  = titre;
    document.getElementById('mpMontant').textContent   = parseInt(montant).toLocaleString('fr-FR') + ' ' + sym;
    document.getElementById('mpMessage').style.display = 'none';
    document.getElementById('mpPhoneField').style.display = 'none';
    document.getElementById('mpTelephone').value = '';
    document.querySelectorAll('.canal-card').forEach(c => {
        c.classList.remove('active');
        c.style.borderColor = '#E5E7EB';
        c.style.background  = '#fff';
    });
    const btn = document.getElementById('mpBtn');
    btn.disabled = true;
    btn.style.background = '#E5E7EB';
    btn.style.color      = '#9CA3AF';
    btn.style.cursor     = 'not-allowed';
    btn.innerHTML = '<i class="bi bi-lock-fill"></i> Choisissez un moyen de paiement';
    new bootstrap.Modal(document.getElementById('modalPaiement')).show();
}

function selectCanal(code, color, bg) {
    _canalChoisi = code;
    document.querySelectorAll('.canal-card').forEach(c => {
        const sel = c.dataset.canal === code;
        c.classList.toggle('active', sel);
        c.style.borderColor = sel ? color : '#E5E7EB';
        c.style.background  = sel ? bg    : '#fff';
    });
    document.getElementById('mpPhoneField').style.display = _canalsMob.includes(code) ? '' : 'none';
    const btn = document.getElementById('mpBtn');
    btn.disabled = false;
    btn.style.background = color;
    btn.style.color      = '#fff';
    btn.style.cursor     = 'pointer';
    btn.innerHTML = '<i class="bi bi-send-fill"></i> Confirmer le paiement';
}

async function lancerPaiement() {
    if (!_canalChoisi || !_paiementId) return;
    const btn = document.getElementById('mpBtn');
    const msg = document.getElementById('mpMessage');
    btn.disabled  = true;
    btn.innerHTML = '<span class="pulse"><i class="bi bi-hourglass-split"></i> Traitement en cours…</span>';
    msg.style.display = 'none';
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('canal', _canalChoisi);
    const tel = document.getElementById('mpTelephone').value;
    if (tel) fd.append('telephone', tel);
    try {
        const res  = await fetch(`{{ url('/paiements') }}/${_paiementId}/mobile`, {
            method: 'POST', body: fd,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        let data = {};
        try { data = await res.json(); } catch(_) {}
        if (!res.ok) throw new Error(data.message || `Erreur ${res.status}`);
        if (data.ok) {
            if (data.url) {
                window.location.href = data.url;
            } else {
                btn.style.background = '#16A34A';
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Paiement validé !';
                msg.style.cssText = 'display:block;background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D';
                let ql = data.quittance_id
                    ? `<div style="margin-top:8px"><a href="/quittances/${data.quittance_id}/telecharger"
                         style="display:inline-flex;align-items:center;gap:5px;background:#16A34A;color:#fff;
                                padding:6px 13px;border-radius:7px;font-size:.78rem;font-weight:700;text-decoration:none">
                         <i class="bi bi-file-earmark-pdf"></i> Télécharger la quittance</a></div>` : '';
                msg.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i><strong>Paiement confirmé !</strong> Votre quittance est disponible.' + ql;
                setTimeout(() => window.location.reload(), 4500);
            }
        } else {
            throw new Error(data.message || 'Erreur lors du paiement.');
        }
    } catch (err) {
        msg.style.cssText = 'display:block;background:#FFF1F2;border:1px solid #FECDD3;color:#9F1239';
        msg.innerHTML     = '<i class="bi bi-exclamation-circle-fill me-2"></i>' + err.message;
        btn.disabled      = false;
        btn.innerHTML     = '<i class="bi bi-send-fill"></i> Réessayer';
    }
}
</script>
@endpush

@endsection
