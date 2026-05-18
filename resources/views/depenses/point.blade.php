@extends('layouts.app')
@section('title', 'Faire le point')
@section('page-title', 'Point financier agence')

@php $cats = \App\Models\Depense::CATEGORIES; @endphp

@section('topbar-actions')
<a href="{{ route('depenses.index') }}" class="btn-ghost" style="padding:7px 14px;font-size:.82rem">
    <i class="bi bi-arrow-left"></i> Dépenses
</a>
@endsection

@section('content')

{{-- ── Sélecteur de période ─────────────────────────────────────────────── --}}
<div class="card-immo" style="padding:18px 22px;margin-bottom:24px">
    <form method="GET" action="{{ route('depenses.point') }}"
          style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <span style="font-size:.82rem;font-weight:700;color:#374151">
            <i class="bi bi-calendar3 me-1" style="color:#EA580C"></i>Période :
        </span>
        @foreach([
            'mois'      => 'Mois courant',
            '2mois'     => '2 mois',
            'trimestre' => 'Trimestre',
            'semestre'  => 'Semestre',
            'annuel'    => 'Année entière',
        ] as $val => $lbl)
        <a href="{{ route('depenses.point', ['periode' => $val]) }}"
           style="padding:6px 16px;border-radius:20px;font-size:.78rem;font-weight:600;
                  text-decoration:none;border:1.5px solid;transition:.15s;
                  {{ $periode === $val
                      ? 'background:#EA580C;color:#fff;border-color:#EA580C'
                      : 'background:#fff;color:#6B7280;border-color:#E5E7EB' }}">
            {{ $lbl }}
        </a>
        @endforeach
        <span style="font-size:.78rem;color:#9CA3AF">
            {{ $dateDebut->isoFormat('D MMM Y') }} → {{ $dateFin->isoFormat('D MMM Y') }}
        </span>
    </form>
</div>

{{-- ── Résultat principal ───────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

    {{-- Récapitulatif calcul --}}
    <div class="card-immo" style="padding:24px">
        <div style="font-size:1rem;font-weight:800;color:#1F2937;margin-bottom:20px;
                    display:flex;align-items:center;gap:8px">
            <i class="bi bi-calculator-fill" style="color:#7C3AED"></i>
            Calcul du bénéfice — {{ $periodeLabel }}
        </div>

        <div style="space-y:12px">

            {{-- Commissions agence --}}
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:12px 16px;background:#F0FDF4;border:1px solid #BBF7D0;
                        border-radius:10px;margin-bottom:10px">
                <div>
                    <div style="font-size:.82rem;font-weight:700;color:#15803D">
                        <i class="bi bi-percent me-1"></i>Commissions agence
                    </div>
                    <div style="font-size:.72rem;color:#166534">
                        Frais agence sur loyers perçus ({{ $commissionsParBien->count() }} bien(s))
                    </div>
                </div>
                <div style="font-size:1.2rem;font-weight:800;color:#15803D">
                    + {{ number_format($totalCommissions, 0, ',', ' ') }} {{ $devSymbole }}
                </div>
            </div>

            {{-- Interventions --}}
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:12px 16px;background:#FFF7ED;border:1px solid #FDBA74;
                        border-radius:10px;margin-bottom:10px">
                <div>
                    <div style="font-size:.82rem;font-weight:700;color:#C2410C">
                        <i class="bi bi-tools me-1"></i>Coût interventions
                    </div>
                    <div style="font-size:.72rem;color:#92400E">Travaux et maintenance sur la période</div>
                </div>
                <div style="font-size:1.2rem;font-weight:800;color:#C2410C">
                    − {{ number_format($totalInterventions, 0, ',', ' ') }} {{ $devSymbole }}
                </div>
            </div>

            {{-- Dépenses agence --}}
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:12px 16px;background:#FFF1F2;border:1px solid #FECDD3;
                        border-radius:10px;margin-bottom:16px">
                <div>
                    <div style="font-size:.82rem;font-weight:700;color:#DC2626">
                        <i class="bi bi-receipt-cutoff me-1"></i>Dépenses agence
                    </div>
                    <div style="font-size:.72rem;color:#9F1239">
                        {{ $depenses->count() }} opération(s) sur la période
                    </div>
                </div>
                <div style="font-size:1.2rem;font-weight:800;color:#DC2626">
                    − {{ number_format($totalDepenses, 0, ',', ' ') }} {{ $devSymbole }}
                </div>
            </div>

            {{-- Séparateur --}}
            <div style="border-top:2px solid #374151;margin-bottom:14px"></div>

            {{-- Bénéfice net --}}
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:16px 20px;
                        background:{{ $benefice >= 0 ? 'linear-gradient(135deg,#F0FDF4,#DCFCE7)' : 'linear-gradient(135deg,#FFF1F2,#FEE2E2)' }};
                        border:2px solid {{ $benefice >= 0 ? '#16A34A' : '#DC2626' }};
                        border-radius:12px">
                <div>
                    <div style="font-size:.95rem;font-weight:800;color:{{ $benefice >= 0 ? '#15803D' : '#9F1239' }}">
                        = Bénéfice net agence
                    </div>
                    <div style="font-size:.72rem;color:#6B7280">
                        Commissions − Interventions − Dépenses
                    </div>
                </div>
                <div style="font-size:1.6rem;font-weight:800;color:{{ $benefice >= 0 ? '#15803D' : '#DC2626' }}">
                    {{ $benefice >= 0 ? '+' : '' }}{{ number_format($benefice, 0, ',', ' ') }} {{ $devSymbole }}
                </div>
            </div>
        </div>
    </div>

    {{-- Détail commissions par bien --}}
    <div class="card-immo" style="padding:24px">
        <div style="font-size:.95rem;font-weight:800;color:#1F2937;margin-bottom:16px;
                    display:flex;align-items:center;gap:8px">
            <i class="bi bi-buildings-fill" style="color:#EA580C"></i>
            Commissions par bien
        </div>

        @if($commissionsParBien->count())
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach($commissionsParBien as $b)
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:10px 14px;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:9px">
                <div>
                    <div style="font-size:.82rem;font-weight:600;color:#1F2937">{{ $b['bien'] }}</div>
                    <div style="font-size:.72rem;color:#9CA3AF">
                        {{ $b['pct'] }}% × {{ $b['count'] }} loyer(s) perçu(s)
                    </div>
                </div>
                <div style="font-size:.9rem;font-weight:700;color:#15803D">
                    {{ number_format($b['commission'], 0, ',', ' ') }} {{ $devSymbole }}
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:30px;color:#9CA3AF;font-size:.82rem">
            <i class="bi bi-info-circle" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
            Aucune commission agence sur cette période.<br>
            <span style="font-size:.76rem">Vérifiez que des frais d'agence sont configurés sur vos baux.</span>
        </div>
        @endif
    </div>
</div>

{{-- ── Répartition dépenses par catégorie + liste ──────────────────────── --}}
<div style="display:grid;grid-template-columns:280px 1fr;gap:20px">

    {{-- Répartition catégories --}}
    <div class="card-immo" style="padding:20px">
        <div style="font-size:.88rem;font-weight:700;color:#1F2937;margin-bottom:14px">
            Dépenses par catégorie
        </div>
        @if($depensesParCategorie->count())
        @foreach($depensesParCategorie as $catKey => $montant)
        @php
            $cat = $cats[$catKey] ?? $cats['autres'];
            $pct = $totalDepenses > 0 ? round($montant / $totalDepenses * 100) : 0;
        @endphp
        <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                <span style="font-size:.78rem;font-weight:600;color:#374151;display:flex;align-items:center;gap:5px">
                    <i class="bi {{ $cat['icon'] }}" style="color:{{ $cat['color'] }}"></i>
                    {{ $cat['label'] }}
                </span>
                <span style="font-size:.75rem;font-weight:700;color:{{ $cat['color'] }}">
                    {{ number_format($montant, 0, ',', ' ') }} {{ $devSymbole }}
                </span>
            </div>
            <div style="background:#E5E7EB;border-radius:4px;height:6px;overflow:hidden">
                <div style="height:6px;border-radius:4px;width:{{ $pct }}%;background:{{ $cat['color'] }};transition:.4s"></div>
            </div>
            <div style="font-size:.68rem;color:#9CA3AF;margin-top:2px;text-align:right">{{ $pct }}%</div>
        </div>
        @endforeach
        @else
        <div style="text-align:center;color:#9CA3AF;font-size:.82rem;padding:20px">
            Aucune dépense sur cette période.
        </div>
        @endif
    </div>

    {{-- Liste détaillée des dépenses --}}
    <div class="card-immo">
        <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;font-size:.88rem;font-weight:700;color:#1F2937">
            Détail des dépenses — {{ $periodeLabel }}
        </div>
        @if($depenses->count())
        <table class="table-immo">
            <thead>
                <tr>
                    <th>Intitulé</th>
                    <th>Catégorie</th>
                    <th>Date</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
            @foreach($depenses as $dep)
            @php $cat = $cats[$dep->categorie] ?? $cats['autres']; @endphp
            <tr>
                <td>
                    <div style="font-weight:600;font-size:.83rem">{{ $dep->titre }}</div>
                    @if($dep->notes)
                    <div style="font-size:.72rem;color:#9CA3AF">{{ Str::limit($dep->notes, 55) }}</div>
                    @endif
                </td>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:5px;
                                 padding:2px 8px;border-radius:20px;font-size:.73rem;font-weight:600;
                                 background:{{ $cat['color'] }}18;color:{{ $cat['color'] }}">
                        <i class="bi {{ $cat['icon'] }}" style="font-size:.65rem"></i>
                        {{ $cat['label'] }}
                    </span>
                </td>
                <td style="font-size:.8rem">{{ $dep->date_depense->format('d/m/Y') }}</td>
                <td style="font-weight:700;color:#DC2626;font-size:.9rem">
                    {{ number_format($dep->montant, 0, ',', ' ') }} {{ $devSymbole }}
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @else
        <div style="padding:40px;text-align:center;color:#9CA3AF;font-size:.85rem">
            Aucune dépense enregistrée sur cette période.
        </div>
        @endif
    </div>
</div>

@endsection
