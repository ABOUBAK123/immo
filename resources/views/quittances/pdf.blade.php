<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quittance {{ $quittance->numero }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, 'Helvetica Neue', sans-serif;
            font-size: 11.5px; line-height: 1.55; color: #111; background: #e8e8e8;
        }
        .actions-bar {
            background: #EA580C; color: #fff; padding: 12px 24px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; position: sticky; top: 0; z-index: 100;
        }
        .btn-print {
            background: #fff; color: #EA580C; border: none; padding: 8px 20px;
            border-radius: 8px; font-size: .875rem; font-weight: 700; cursor: pointer;
        }
        .btn-back { color: rgba(255,255,255,.85); font-size: .82rem; text-decoration: none; }
        .page-wrap { max-width: 210mm; margin: 24px auto; padding: 0 12px 32px; }
        .page { background:#fff; padding:18mm 16mm 16mm; box-shadow:0 2px 24px rgba(0,0,0,.12); }
        .border-box { border: 1.5px solid #111; margin-bottom: 10px; }
        .section-title {
            background: #111; color: #fff; padding: 4px 10px;
            font-weight: 700; font-size: 11px; letter-spacing: .04em; text-transform: uppercase;
        }
        .section-body { padding: 8px 12px; }
        .row-info { display: flex; gap: 6px; margin-bottom: 3px; }
        .row-info .lbl { font-weight: 700; min-width: 200px; flex-shrink: 0; color: #444; }
        .header-outer { border: 2px solid #111; margin-bottom: 14px; }
        .header-top { background: #111; color: #fff; text-align: center; padding: 10px 12px 8px; }
        .header-top h1 { font-size: 18px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
        .header-top .subtitle { font-size: 11px; opacity: .8; margin-top: 3px; }
        .header-meta { display: grid; grid-template-columns: 1fr 1fr; border-top: 1.5px solid #111; }
        .header-meta-item { padding: 7px 12px; }
        .header-meta-item:first-child { border-right: 1px solid #111; }
        .meta-lbl { font-size: 10px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: .04em; }
        .meta-val { font-weight: 800; font-size: 12.5px; }
        .payment-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .payment-table th { background: #333; color: #fff; padding: 5px 10px; font-size: 10px; text-transform: uppercase; }
        .payment-table td { padding: 5px 10px; border-bottom: 1px solid #ddd; }
        .payment-table td:last-child { text-align: right; font-weight: 600; }
        .payment-table .indent { padding-left: 24px; color: #555; }
        .subtotal-row td { background: #f5f5f5; font-weight: 700; border-top: 1px solid #bbb; }
        .total-row td { background: #111; color: #fff; font-weight: 900; font-size: 13px; border: none; }
        .modes-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5px 20px; padding: 8px 12px; }
        .mode-item { display: flex; align-items: center; gap: 7px; font-size: 11px; }
        .checkbox {
            width: 13px; height: 13px; border: 1.5px solid #555; border-radius: 2px;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 9px; font-weight: 900;
        }
        .checkbox.checked { background: #111; color: #fff; border-color: #111; }
        .attestation-text { padding: 10px 12px; text-align: justify; line-height: 1.65; }
        .montant-lettres {
            background: #f9f9f9; border: 1.5px solid #bbb; border-radius: 4px;
            padding: 7px 12px; font-weight: 800; font-size: 12.5px;
            text-align: center; margin: 8px 0; letter-spacing: .02em;
        }
        .signature-section { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 8px 12px 10px; }
        .signature-box { border: 1px dashed #aaa; border-radius: 6px; padding: 8px 12px; min-height: 80px; }
        .sig-lbl { font-size: 10px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
        .sig-name { font-size: 11px; font-weight: 700; margin-top: 32px; }
        .legal-section { border: 1px solid #ccc; border-radius: 4px; padding: 8px 12px; margin-top: 10px; }
        .legal-title { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #555; margin-bottom: 5px; }
        .legal-list { font-size: 9.5px; color: #555; line-height: 1.5; padding-left: 14px; }
        .legal-list li { margin-bottom: 3px; }
        .doc-footer { border: 1px solid #ddd; border-radius: 4px; text-align: center; padding: 6px 12px; margin-top: 8px; font-size: 9.5px; color: #888; }
        @media print {
            body { background: #fff; }
            .actions-bar { display: none !important; }
            .page-wrap { max-width: none; margin: 0; padding: 0; }
            .page { box-shadow: none; padding: 10mm 12mm; }
        }
    </style>
</head>
<body>

@php
    $location     = $paiement->location;
    $bien         = $location->bien;
    $locataire    = $location->locataire;
    $proprietaire = $bien->proprietaire;
    $sym          = $proprietaire ? $proprietaire->deviseSymbole() : 'FCFA';
    $devise       = $proprietaire ? ($proprietaire->devise ?? 'XOF') : 'XOF';
    $deviseLong = match($devise) {
        'EUR' => 'EUROS', 'USD' => 'DOLLARS US',
        'MAD' => 'DIRHAMS MAROCAINS', 'DZD' => 'DINARS ALGÉRIENS',
        default => 'FRANCS CFA',
    };
    $periodeDebut  = $paiement->date_echeance->startOfMonth()->copy();
    $periodeFin    = $paiement->date_echeance->endOfMonth()->copy();
    $periodeLabel  = ucfirst($paiement->date_echeance->isoFormat('MMMM YYYY'));
    $villeCode     = strtoupper(substr($bien->ville ?? 'VIL', 0, 3));
    $refBail       = 'BAIL-' . $location->date_debut->format('Y') . '-' . $villeCode . '-' . str_pad($location->id, 3, '0', STR_PAD_LEFT);
    $loyer         = (float) $location->loyer_mensuel;
    $charges       = (float) $location->charges;
    $total         = (float) $paiement->montant;
    $modePaye = match($paiement->methode_paiement ?? '') {
        'virement','prelevement' => 'virement',
        'cheque'                 => 'cheque',
        'cb','carte'             => 'carte',
        'mobile_money'           => 'mobile',
        default                  => 'especes',
    };
    $operateurMobile = match($paiement->canal_paiement ?? '') {
        'orange_money' => 'Orange Money',
        'mtn_money'    => 'MTN MoMo',
        'wave'         => 'Wave',
        default        => 'Orange Money / Wave / MTN MoMo',
    };
    $modePaiementLabel = match($modePaye) {
        'virement' => 'virement bancaire',
        'cheque'   => 'chèque',
        'carte'    => 'carte bancaire',
        'mobile'   => 'Mobile Money (' . $operateurMobile . ')',
        default    => 'espèces',
    };
    $montantLettres = \App\Models\Quittance::montantEnLettres((int) $total, $deviseLong);
    $dateEmission   = $quittance->date_emission->isoFormat('D MMMM YYYY');
    $datePaiement   = $paiement->date_paiement ? $paiement->date_paiement->isoFormat('D MMMM YYYY') : $dateEmission;
    $refTransaction = $paiement->reference ?? $paiement->provider_reference ?? null;
    if ($refTransaction && str_starts_with((string)$refTransaction, 'SIMU_')) $refTransaction = null;
@endphp

<div class="actions-bar">
    <div style="display:flex;align-items:center;gap:16px">
        <a href="{{ url()->previous() }}" class="btn-back">← Retour</a>
        <span style="font-weight:800;font-size:1rem">🏠 ImmoGest</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <span style="font-size:.8rem;opacity:.85">{{ $quittance->numero }}</span>
        <a href="{{ route('quittances.download', $quittance) }}"
           style="background:#fff;color:#EA580C;border:none;padding:8px 18px;border-radius:8px;
                  font-size:.875rem;font-weight:700;cursor:pointer;text-decoration:none;
                  display:inline-flex;align-items:center;gap:6px">
            ⬇ Télécharger PDF
        </a>
        <button class="btn-print" onclick="window.print()">🖨️ Imprimer</button>
    </div>
</div>

<div class="page-wrap"><div class="page">

{{-- ── En-tête ────────────────────────────────────────────────────────────── --}}
<div class="header-outer">
    <div class="header-top">
        <h1>Quittance de Loyer</h1>
        <div class="subtitle">République de Côte d'Ivoire &nbsp;·&nbsp; Union — Discipline — Travail</div>
    </div>
    <div class="header-meta">
        <div class="header-meta-item">
            <div class="meta-lbl">Numéro de quittance</div>
            <div class="meta-val">{{ $quittance->numero }}</div>
        </div>
        <div class="header-meta-item">
            <div class="meta-lbl">Date d'émission</div>
            <div class="meta-val">{{ $dateEmission }}</div>
        </div>
    </div>
</div>

{{-- ── Bailleur ────────────────────────────────────────────────────────────── --}}
<div class="border-box">
    <div class="section-title">Informations du Bailleur (Propriétaire)</div>
    <div class="section-body">
        <div class="row-info"><span class="lbl">Nom et Prénom / Raison sociale :</span><span>{{ strtoupper($proprietaire?->name ?? '—') }}</span></div>
        <div class="row-info"><span class="lbl">Adresse :</span><span>{{ $bien->adresse ?? '—' }}, {{ $bien->ville ?? '' }}{{ $bien->code_postal ? ' '.$bien->code_postal : '' }}, {{ $bien->pays ?? 'Côte d\'Ivoire' }}</span></div>
        <div class="row-info"><span class="lbl">Téléphone :</span><span>{{ $proprietaire?->phone ?? '—' }}</span></div>
        <div class="row-info"><span class="lbl">Email :</span><span>{{ $proprietaire?->email ?? '—' }}</span></div>
        <div class="row-info"><span class="lbl">N° Contribuable / RCCM :</span><span style="color:#999">—</span></div>
    </div>
</div>

{{-- ── Locataire ───────────────────────────────────────────────────────────── --}}
<div class="border-box">
    <div class="section-title">Informations du Locataire</div>
    <div class="section-body">
        <div class="row-info"><span class="lbl">Nom et Prénom :</span><span>{{ strtoupper($locataire?->name ?? '—') }}</span></div>
        <div class="row-info"><span class="lbl">Adresse de correspondance :</span><span>{{ $bien->adresse ?? '' }}, {{ $bien->ville ?? '' }} (même adresse que le bien loué)</span></div>
        <div class="row-info"><span class="lbl">Téléphone :</span><span>{{ $locataire?->phone ?? '—' }}</span></div>
        <div class="row-info"><span class="lbl">Email :</span><span>{{ $locataire?->email ?? '—' }}</span></div>
    </div>
</div>

{{-- ── Bien loué ───────────────────────────────────────────────────────────── --}}
<div class="border-box">
    <div class="section-title">Bien Loué</div>
    <div class="section-body">
        <div class="row-info">
            <span class="lbl">Adresse complète :</span>
            <span>
                {{ $bien->titre }}
                @if($bien->etage !== null), {{ $bien->etage === 0 ? 'Rez-de-chaussée' : $bien->etage.'ème étage' }}@endif
                — {{ $bien->adresse }}, {{ $bien->ville }}
            </span>
        </div>
        <div class="row-info">
            <span class="lbl">Type de bien :</span>
            <span>
                {{ $bien->type }}
                @if($bien->nb_pieces) — {{ $bien->nb_pieces }} pièce(s)@endif
                @if($bien->nb_chambres) ({{ $bien->nb_chambres }} ch.)@endif
                @if($bien->meuble) — Meublé@endif
            </span>
        </div>
        @if($bien->surface)
        <div class="row-info"><span class="lbl">Superficie :</span><span>{{ $bien->surface }} m²</span></div>
        @endif
        <div class="row-info"><span class="lbl">Référence bail :</span><span>{{ $refBail }} du {{ $location->date_debut->format('d/m/Y') }}</span></div>
        @if($location->type_bail)
        <div class="row-info"><span class="lbl">Type de bail :</span><span>{{ ucfirst($location->type_bail) }}</span></div>
        @endif
    </div>
</div>

{{-- ── Attestation (avec détail paiement et mode de paiement intégrés) ──────── --}}
<div class="border-box">
    <div class="section-title">Attestation</div>
    <div class="attestation-text">
        <p>
            Je soussigné(e), <strong>{{ strtoupper($proprietaire?->name ?? '—') }}</strong>,
            bailleur du bien ci-dessus désigné, reconnais avoir reçu de
            <strong>{{ strtoupper($locataire?->name ?? '—') }}</strong>, locataire, la somme de :
        </p>
        <div class="montant-lettres">{{ $montantLettres }}</div>
        <p>
            en <strong>{{ $modePaiementLabel }}</strong>
            au titre du loyer et des charges pour la période de <strong>{{ $periodeLabel }}</strong>
            (du {{ $periodeDebut->format('d/m/Y') }} au {{ $periodeFin->format('d/m/Y') }}).
        </p>
        @if($refTransaction || $paiement->date_paiement)
        <p style="margin-top:4px;font-size:10.5px;color:#444">
            @if($refTransaction)
                Réf. transaction : <strong style="font-family:monospace">{{ $refTransaction }}</strong>
                @if($paiement->date_paiement) &nbsp;·&nbsp; @endif
            @endif
            @if($paiement->date_paiement)
                Date de réception : <strong>{{ $datePaiement }}</strong>
            @endif
        </p>
        @endif
    </div>

    {{-- Mention décharge --}}
    <div class="attestation-text" style="padding-top:8px;border-top:1px solid #e5e7eb">
        <p>
            La présente quittance vaut décharge pour la période concernée et ne préjuge pas des
            éventuelles régularisations de charges en fin d'année conformément au contrat de bail.
        </p>
    </div>

    <div class="signature-section">
        <div>
            <div style="font-size:11px;padding:0 0 6px"><strong>Fait à {{ $bien->ville ?? 'Abidjan' }}, le {{ $dateEmission }}</strong></div>
            <div class="signature-box">
                <div class="sig-lbl">Signature du bailleur ou mandataire</div>
                <div style="height:36px"></div>
                <div class="sig-name">{{ $proprietaire?->name ?? '—' }}</div>
                <div style="font-size:9px;color:#aaa;margin-top:2px">(Cachet si professionnel)</div>
            </div>
        </div>
        <div>
            <div style="font-size:11px;padding:0 0 6px"><strong>Reçu par le locataire</strong></div>
            <div class="signature-box">
                <div class="sig-lbl">Signature du locataire (optionnel)</div>
                <div style="height:36px"></div>
                <div class="sig-name">{{ $locataire?->name ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Mentions légales ────────────────────────────────────────────────────── --}}
<div class="legal-section">
    <div class="legal-title">Mentions légales obligatoires</div>
    <ul class="legal-list">
        <li>Conformément à l'article 44 de la Loi n° 2019-576 du 26 juin 2019 instituant le Code de la Construction et de l'Habitat, cette quittance fait foi du paiement du loyer.</li>
        <li>Conformément à la Loi n° 2018-575 du 13 juin 2018 relative au bail à usage d'habitation, le locataire a droit à la délivrance d'une quittance à chaque paiement.</li>
        <li><strong>Conservation :</strong> Cette quittance doit être conservée pendant toute la durée du bail + 10 ans (archivage légal).</li>
        <li>En cas de litige, cette quittance constitue une preuve de paiement opposable devant les juridictions compétentes.</li>
    </ul>
</div>

<div class="doc-footer">
    Document généré électroniquement par <strong>ImmoGest</strong> — Version numérique valide &nbsp;·&nbsp;
    Réf. {{ $quittance->numero }} &nbsp;·&nbsp; Émis le {{ $dateEmission }}
</div>

</div></div>{{-- /page /page-wrap --}}
</body>
</html>
