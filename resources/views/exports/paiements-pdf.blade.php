<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1F2937; }

    .header { background: #EA580C; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .header-brand { font-size: 16px; font-weight: 700; }
    .header-sub { font-size: 10px; opacity: .85; margin-top: 2px; }
    .header-meta { text-align: right; font-size: 9px; opacity: .9; }

    .periode-band {
        background: #FFF7ED; border: 1px solid #FDBA74;
        border-radius: 6px; padding: 8px 14px; margin-bottom: 14px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .periode-label { font-size: 11px; font-weight: 700; color: #C2410C; }
    .periode-dates { font-size: 9px; color: #92400E; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    thead tr { background: #374151; color: #fff; }
    thead th { padding: 6px 8px; text-align: left; font-size: 8.5px; font-weight: 700; }
    tbody tr:nth-child(even) { background: #F9FAFB; }
    tbody td { padding: 5px 8px; border-bottom: 1px solid #E5E7EB; font-size: 8.5px; }
    .badge-paye    { background: #DCFCE7; color: #15803D; padding: 2px 6px; border-radius: 10px; font-weight: 700; }
    .badge-retard  { background: #FEE2E2; color: #DC2626; padding: 2px 6px; border-radius: 10px; font-weight: 700; }
    .badge-attente { background: #FEF3C7; color: #D97706; padding: 2px 6px; border-radius: 10px; font-weight: 700; }

    .summary-box {
        border: 1.5px solid #E5E7EB; border-radius: 8px;
        padding: 14px 18px; margin-bottom: 20px; width: 320px; float: right;
    }
    .summary-title { font-size: 10px; font-weight: 700; color: #374151; margin-bottom: 10px; border-bottom: 1px solid #E5E7EB; padding-bottom: 6px; }
    .summary-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 9px; color: #4B5563; }
    .summary-row.minus { color: #DC2626; }
    .summary-row.total { border-top: 1.5px solid #374151; margin-top: 6px; padding-top: 8px; font-weight: 700; font-size: 10px; color: #15803D; }
    .summary-label { }
    .summary-val { font-weight: 700; }

    .clearfix::after { content: ''; display: table; clear: both; }

    .signatures { margin-top: 36px; }
    .signatures-title { font-size: 9px; color: #9CA3AF; text-align: center; margin-bottom: 14px; border-bottom: 1px dashed #D1D5DB; padding-bottom: 6px; }
    .sig-grid { display: flex; gap: 40px; }
    .sig-box { flex: 1; border: 1px solid #E5E7EB; border-radius: 8px; padding: 14px 16px; }
    .sig-role { font-size: 10px; font-weight: 700; color: #374151; margin-bottom: 4px; }
    .sig-name { font-size: 9px; color: #6B7280; margin-bottom: 16px; }
    .sig-line { border-bottom: 1px solid #9CA3AF; margin-bottom: 8px; height: 28px; }
    .sig-hint { font-size: 8px; color: #9CA3AF; }

    .footer { margin-top: 14px; text-align: center; font-size: 7.5px; color: #9CA3AF; border-top: 1px solid #F3F4F6; padding-top: 8px; }
    .no-data { text-align: center; padding: 30px; color: #9CA3AF; font-style: italic; }
    .right { text-align: right; }
    .bold { font-weight: 700; }
    .green { color: #15803D; }
    .red { color: #DC2626; }
</style>
</head>
<body>

{{-- ── En-tête ─────────────────────────────────────────────────────────── --}}
<div class="header">
    <div class="header-top">
        <div>
            <div class="header-brand">ImmoGest</div>
            <div class="header-sub">Relevé de paiements — {{ $periodeLabel }}</div>
        </div>
        <div class="header-meta">
            Propriétaire : {{ $user->name }}<br>
            Généré le {{ now()->isoFormat('D MMMM YYYY') }}<br>
            {{ $dateDebut->isoFormat('D MMM Y') }} → {{ $dateFin->isoFormat('D MMM Y') }}
        </div>
    </div>
</div>

{{-- ── Bandeau période ─────────────────────────────────────────────────── --}}
<div class="periode-band">
    <div>
        <span class="periode-label">{{ $periodeLabel }}</span>
        <span class="periode-dates"> &nbsp;|&nbsp; {{ $paiements->count() }} paiement(s) sur la période</span>
    </div>
    <div class="periode-dates">
        Payés : {{ $paiements->where('statut','paye')->count() }}
        &nbsp;·&nbsp; En attente : {{ $paiements->where('statut','en_attente')->count() }}
    </div>
</div>

{{-- ── Tableau des paiements ───────────────────────────────────────────── --}}
@if($paiements->count())
<table>
    <thead>
        <tr>
            <th>Bien</th>
            <th>Locataire</th>
            <th>Échéance</th>
            <th class="right">Loyer</th>
            <th class="right">Charges</th>
            <th class="right">Frais ag.</th>
            <th class="right">Total</th>
            <th>Statut</th>
            <th>Date paiement</th>
        </tr>
    </thead>
    <tbody>
    @foreach($paiements as $p)
    @php
        $enRetard = $p->statut === 'en_attente' && $p->date_echeance->isPast();
        $fraisAg  = round((float)$p->location->loyer_mensuel * (float)$p->location->frais_agence / 100, 0);
    @endphp
    <tr>
        <td class="bold">{{ Str::limit(optional($p->location->bien)->titre ?? '—', 22) }}</td>
        <td>{{ $p->location->locataire->name ?? '—' }}</td>
        <td>{{ $p->date_echeance->format('d/m/Y') }}</td>
        <td class="right">{{ number_format($p->location->loyer_mensuel, 0, ',', ' ') }}</td>
        <td class="right">{{ number_format($p->location->charges, 0, ',', ' ') }}</td>
        <td class="right">
            @if($p->location->frais_agence > 0)
            {{ $p->location->frais_agence }}% ({{ number_format($fraisAg, 0, ',', ' ') }})
            @else —
            @endif
        </td>
        <td class="right bold">{{ number_format($p->montant, 0, ',', ' ') }} {{ $devSymbole }}</td>
        <td>
            @if($p->statut === 'paye')
            <span class="badge-paye">Payé</span>
            @elseif($enRetard)
            <span class="badge-retard">En retard</span>
            @else
            <span class="badge-attente">En attente</span>
            @endif
        </td>
        <td>{{ $p->date_paiement ? $p->date_paiement->format('d/m/Y') : '—' }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@else
<div class="no-data">Aucun paiement enregistré sur cette période.</div>
@endif

{{-- ── Récapitulatif financier ─────────────────────────────────────────── --}}
<div class="clearfix">
<div class="summary-box">
    <div class="summary-title">Récapitulatif financier — {{ $periodeLabel }}</div>

    <div class="summary-row">
        <span class="summary-label">Total recouvrement</span>
        <span class="summary-val green">{{ number_format($totalRecouvrement, 0, ',', ' ') }} {{ $devSymbole }}</span>
    </div>
    <div class="summary-row minus">
        <span class="summary-label">(–) Montant interventions</span>
        <span class="summary-val">{{ number_format($totalInterventions, 0, ',', ' ') }} {{ $devSymbole }}</span>
    </div>
    <div class="summary-row minus">
        <span class="summary-label">(–) Frais d'agence</span>
        <span class="summary-val">{{ number_format($totalFraisAgence, 0, ',', ' ') }} {{ $devSymbole }}</span>
    </div>
    <div class="summary-row total">
        <span class="summary-label">= Total net propriétaire</span>
        <span class="summary-val">{{ number_format($totalNet, 0, ',', ' ') }} {{ $devSymbole }}</span>
    </div>
</div>
</div>

{{-- ── Zones de signature ──────────────────────────────────────────────── --}}
<div class="signatures">
    <div class="signatures-title">Signatures</div>
    <div class="sig-grid">
        <div class="sig-box">
            <div class="sig-role">L'Agence immobilière</div>
            <div class="sig-name">Nom : ___________________________________</div>
            <div class="sig-line"></div>
            <div class="sig-hint">Lu et approuvé — Date : _____ / _____ / _______</div>
        </div>
        <div class="sig-box">
            <div class="sig-role">Le Propriétaire</div>
            <div class="sig-name">{{ $user->name }}</div>
            <div class="sig-line"></div>
            <div class="sig-hint">Lu et approuvé — Date : _____ / _____ / _______</div>
        </div>
    </div>
</div>

<div class="footer">
    Document généré par ImmoGest · {{ now()->isoFormat('D MMMM YYYY [à] HH[h]mm') }} · Confidentiel
</div>

</body>
</html>
