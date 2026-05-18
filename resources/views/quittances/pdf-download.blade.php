<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Quittance {{ $quittance->numero }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10.5px; color: #111; background: #fff; }
        h1  { font-size: 17px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        .page { padding: 14mm 13mm 12mm; }

        /* ── En-tête ── */
        .hdr-outer { border: 2px solid #111; margin-bottom: 10px; }
        .hdr-top   { background: #111; color: #fff; text-align: center; padding: 8px 10px 7px; }
        .hdr-sub   { font-size: 10px; opacity: .78; margin-top: 2px; }
        .hdr-meta  { border-top: 1.5px solid #111; }
        .hdr-meta td { padding: 6px 10px; vertical-align: top; }
        .hdr-meta td:first-child { border-right: 1px solid #111; width: 50%; }
        .meta-lbl  { font-size: 9px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: .04em; }
        .meta-val  { font-weight: 800; font-size: 12px; margin-top: 2px; }

        /* ── Sections ── */
        .section   { border: 1.5px solid #111; margin-bottom: 8px; }
        .sec-title { background: #111; color: #fff; padding: 4px 9px; font-weight: 700; font-size: 10px; letter-spacing: .04em; text-transform: uppercase; }
        .sec-body  { padding: 7px 10px; }
        .row-info  { margin-bottom: 3px; }
        .lbl       { font-weight: 700; color: #444; display: inline; }

        /* ── Tableau détail paiement ── */
        .pay-table      { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .pay-table th   { background: #333; color: #fff; padding: 5px 8px; font-size: 9.5px; text-transform: uppercase; text-align: left; }
        .pay-table th.r { text-align: right; width: 130px; }
        .pay-table td   { padding: 5px 8px; border-bottom: 1px solid #ddd; vertical-align: top; }
        .pay-table td.r { text-align: right; font-weight: 600; }
        .indent         { padding-left: 20px; color: #555; }
        .subtotal td    { background: #f5f5f5; font-weight: 700; border-top: 1px solid #bbb; }
        .total-row td   { background: #111; color: #fff; font-weight: 900; font-size: 12px; border: none; }

        /* ── Mode paiement ── */
        .modes-table td { padding: 4px 10px; font-size: 10px; vertical-align: middle; }
        .cb  { display: inline-block; width: 12px; height: 12px; border: 1.5px solid #555; text-align: center; line-height: 11px; font-size: 9px; font-weight: 900; vertical-align: middle; margin-right: 5px; }
        .cb.on { background: #111; color: #fff; border-color: #111; }

        /* ── Attestation ── */
        .attestation   { padding: 9px 10px; text-align: justify; line-height: 1.6; }
        .montant-box   { background: #f6f6f6; border: 1.5px solid #aaa; padding: 7px 10px; font-weight: 800; font-size: 11.5px; text-align: center; margin: 7px 0; letter-spacing: .02em; }
        .sig-box       { border: 1px dashed #aaa; border-radius: 4px; padding: 7px 10px; min-height: 70px; }
        .sig-lbl       { font-size: 9px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 3px; }
        .sig-name      { font-size: 10px; font-weight: 700; margin-top: 28px; }

        /* ── Mentions légales ── */
        .legal { border: 1px solid #ccc; padding: 7px 10px; margin-top: 9px; font-size: 9px; color: #555; }
        .legal-title { font-weight: 800; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
        .legal li { margin-bottom: 2px; padding-left: 4px; }

        /* ── Pied de page ── */
        .footer { border: 1px solid #ddd; text-align: center; padding: 5px 10px; margin-top: 7px; font-size: 9px; color: #888; }
    </style>
</head>
<body>
@php
    $location     = $paiement->location;
    $bien         = $location->bien;
    $locataire    = $location->locataire;
    $proprietaire = $bien->proprietaire;
    $sym          = $proprietaire ? ($proprietaire->deviseSymbole()) : 'FCFA';
    $devise       = $proprietaire ? ($proprietaire->devise ?? 'XOF') : 'XOF';
    $deviseLong   = match($devise) {
        'EUR' => 'EUROS', 'USD' => 'DOLLARS US',
        'MAD' => 'DIRHAMS MAROCAINS', 'DZD' => 'DINARS ALGÉRIENS',
        default => 'FRANCS CFA',
    };
    $periodeDebut  = $paiement->date_echeance->copy()->startOfMonth();
    $periodeFin    = $paiement->date_echeance->copy()->endOfMonth();
    $periodeLabel  = ucfirst($paiement->date_echeance->isoFormat('MMMM YYYY'));
    $villeCode     = strtoupper(substr($bien->ville ?? 'VIL', 0, 3));
    $refBail       = 'BAIL-'.$location->date_debut->format('Y').'-'.$villeCode.'-'.str_pad($location->id, 3, '0', STR_PAD_LEFT);
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
        default        => 'Mobile Money',
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

    $modes = [
        'virement' => 'Virement bancaire',
        'especes'  => 'Espèces',
        'mobile'   => 'Mobile Money ('.$operateurMobile.')',
        'cheque'   => 'Chèque',
        'carte'    => 'Carte bancaire (CB / Visa / Mastercard)',
    ];
@endphp

<div class="page">

{{-- ── En-tête ── --}}
<div class="hdr-outer">
    <div class="hdr-top">
        <h1>Quittance de Loyer</h1>
        <div class="hdr-sub">République de Côte d'Ivoire &nbsp;·&nbsp; Union — Discipline — Travail</div>
    </div>
    <table class="hdr-meta">
        <tr>
            <td>
                <div class="meta-lbl">Numéro de quittance</div>
                <div class="meta-val">{{ $quittance->numero }}</div>
            </td>
            <td>
                <div class="meta-lbl">Date d'émission</div>
                <div class="meta-val">{{ $dateEmission }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ── Bailleur ── --}}
<div class="section">
    <div class="sec-title">Informations du Bailleur (Propriétaire)</div>
    <div class="sec-body">
        <div class="row-info"><span class="lbl">Nom et Prénom / Raison sociale : </span>{{ strtoupper($proprietaire?->name ?? '—') }}</div>
        <div class="row-info"><span class="lbl">Adresse : </span>{{ $bien->adresse ?? '—' }}, {{ $bien->ville ?? '' }}{{ $bien->code_postal ? ' '.$bien->code_postal : '' }}, {{ $bien->pays ?? "Côte d'Ivoire" }}</div>
        <div class="row-info"><span class="lbl">Téléphone : </span>{{ $proprietaire?->phone ?? '—' }}</div>
        <div class="row-info"><span class="lbl">Email : </span>{{ $proprietaire?->email ?? '—' }}</div>
        <div class="row-info"><span class="lbl">N° Contribuable / RCCM : </span><span style="color:#999">—</span></div>
    </div>
</div>

{{-- ── Locataire ── --}}
<div class="section">
    <div class="sec-title">Informations du Locataire</div>
    <div class="sec-body">
        <div class="row-info"><span class="lbl">Nom et Prénom : </span>{{ strtoupper($locataire?->name ?? '—') }}</div>
        <div class="row-info"><span class="lbl">Adresse de correspondance : </span>{{ $bien->adresse ?? '' }}, {{ $bien->ville ?? '' }} (même adresse que le bien loué)</div>
        <div class="row-info"><span class="lbl">Téléphone : </span>{{ $locataire?->phone ?? '—' }}</div>
        <div class="row-info"><span class="lbl">Email : </span>{{ $locataire?->email ?? '—' }}</div>
    </div>
</div>

{{-- ── Bien loué ── --}}
<div class="section">
    <div class="sec-title">Bien Loué</div>
    <div class="sec-body">
        <div class="row-info">
            <span class="lbl">Adresse complète : </span>
            {{ $bien->titre }}
            @if($bien->etage !== null), {{ $bien->etage === 0 ? 'Rez-de-chaussée' : $bien->etage.'ème étage' }}@endif
            — {{ $bien->adresse }}, {{ $bien->ville }}
        </div>
        <div class="row-info">
            <span class="lbl">Type de bien : </span>
            {{ $bien->type }}
            @if($bien->nb_pieces) — {{ $bien->nb_pieces }} pièce(s)@endif
            @if($bien->nb_chambres) ({{ $bien->nb_chambres }} ch.)@endif
            @if($bien->meuble) — Meublé@endif
        </div>
        @if($bien->surface)
        <div class="row-info"><span class="lbl">Superficie : </span>{{ $bien->surface }} m²</div>
        @endif
        <div class="row-info"><span class="lbl">Référence bail : </span>{{ $refBail }} du {{ $location->date_debut->format('d/m/Y') }}</div>
        @if($location->type_bail)
        <div class="row-info"><span class="lbl">Type de bail : </span>{{ ucfirst($location->type_bail) }}</div>
        @endif
    </div>
</div>

{{-- ── Détail paiement ── --}}
<div class="section">
    <div class="sec-title">Détail du Paiement</div>
    <div class="sec-body" style="padding-bottom:5px">
        <div style="margin-bottom:6px;font-weight:700">
            Période concernée : {{ $periodeLabel }}
            (du {{ $periodeDebut->format('d/m/Y') }} au {{ $periodeFin->format('d/m/Y') }})
        </div>
        <table class="pay-table">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class="r">Montant ({{ $sym }})</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Loyer hors charges</td>
                    <td class="r">{{ number_format($loyer, 0, ',', ' ') }}</td>
                </tr>
                @if($charges > 0)
                <tr>
                    <td>Charges récupérables :</td>
                    <td class="r"></td>
                </tr>
                <tr>
                    <td class="indent">Eau, électricité, entretien parties communes &amp; ordures ménagères</td>
                    <td class="r">{{ number_format($charges, 0, ',', ' ') }}</td>
                </tr>
                <tr class="subtotal">
                    <td>Sous-total charges</td>
                    <td class="r">{{ number_format($charges, 0, ',', ' ') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL GÉNÉRAL PAYÉ</td>
                    <td class="r">{{ number_format($total, 0, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ── Attestation ── --}}
<div class="section">
    <div class="sec-title">Attestation</div>
    <div class="attestation">
        <p>
            Je soussigné(e), <strong>{{ strtoupper($proprietaire?->name ?? '—') }}</strong>,
            bailleur du bien ci-dessus désigné, reconnais avoir reçu de
            <strong>{{ strtoupper($locataire?->name ?? '—') }}</strong>, locataire, la somme de :
        </p>
        <div class="montant-box">{{ $montantLettres }}</div>
        <p>
            en <strong>{{ $modePaiementLabel }}</strong>
            au titre du loyer et des charges pour la période de <strong>{{ $periodeLabel }}</strong>
            (du {{ $periodeDebut->format('d/m/Y') }} au {{ $periodeFin->format('d/m/Y') }}).
        </p>
        @if($refTransaction || $paiement->date_paiement)
        <p style="margin-top:4px;font-size:9.5px;color:#444">
            @if($refTransaction)
                Réf. transaction : <strong style="font-family:monospace">{{ $refTransaction }}</strong>
                @if($paiement->date_paiement) &nbsp;·&nbsp; @endif
            @endif
            @if($paiement->date_paiement)
                Date de réception : <strong>{{ $datePaiement }}</strong>
            @endif
        </p>
        @endif
        <p style="margin-top:7px">
            La présente quittance vaut décharge pour la période concernée et ne préjuge pas des
            éventuelles régularisations de charges en fin d'année conformément au contrat de bail.
        </p>
    </div>

    <table style="width:100%;border-top:1px solid #eee">
        <tr>
            <td style="width:50%;padding:8px 10px;vertical-align:top;border-right:1px solid #eee">
                <div style="font-size:10px;margin-bottom:6px"><strong>Fait à {{ $bien->ville ?? 'Abidjan' }}, le {{ $dateEmission }}</strong></div>
                <div class="sig-box">
                    <div class="sig-lbl">Signature du bailleur ou mandataire</div>
                    <div style="height:30px"></div>
                    <div class="sig-name">{{ $proprietaire?->name ?? '—' }}</div>
                    <div style="font-size:8.5px;color:#aaa;margin-top:2px">(Cachet si professionnel)</div>
                </div>
            </td>
            <td style="width:50%;padding:8px 10px;vertical-align:top">
                <div style="font-size:10px;margin-bottom:6px"><strong>Reçu par le locataire</strong></div>
                <div class="sig-box">
                    <div class="sig-lbl">Signature du locataire (optionnel)</div>
                    <div style="height:30px"></div>
                    <div class="sig-name">{{ $locataire?->name ?? '—' }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ── Mentions légales ── --}}
<div class="legal">
    <div class="legal-title">Mentions légales obligatoires</div>
    <ul style="padding-left:14px">
        <li class="legal li">Conformément à l'article 44 de la Loi n° 2019-576 du 26 juin 2019 instituant le Code de la Construction et de l'Habitat, cette quittance fait foi du paiement du loyer.</li>
        <li class="legal li">Conformément à la Loi n° 2018-575 du 13 juin 2018 relative au bail à usage d'habitation, le locataire a droit à la délivrance d'une quittance à chaque paiement.</li>
        <li class="legal li"><strong>Conservation :</strong> Cette quittance doit être conservée pendant toute la durée du bail + 10 ans.</li>
        <li class="legal li">En cas de litige, cette quittance constitue une preuve de paiement opposable devant les juridictions compétentes.</li>
    </ul>
</div>

<div class="footer">
    Document généré électroniquement par <strong>ImmoGest</strong> — Version numérique valide
    &nbsp;·&nbsp; Réf. {{ $quittance->numero }} &nbsp;·&nbsp; Émis le {{ $dateEmission }}
</div>

</div>
</body>
</html>
