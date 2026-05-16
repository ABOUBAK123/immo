<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quittance de loyer</title>
    <style>
        body  { margin:0; padding:0; background:#F3F4F6; font-family: Arial, sans-serif; font-size:14px; color:#111; }
        .wrap { max-width:580px; margin:30px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
        .top  { background:#111; color:#fff; padding:28px 32px 24px; }
        .top h1 { font-size:20px; font-weight:900; letter-spacing:.06em; margin:0 0 4px; }
        .top .sub { font-size:12px; opacity:.7; }
        .body { padding:28px 32px; }
        .highlight { background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:16px 20px; margin:20px 0; }
        .highlight .amount { font-size:22px; font-weight:800; color:#15803D; margin-top:4px; }
        .info-grid { border:1px solid #E5E7EB; border-radius:8px; overflow:hidden; margin:20px 0; }
        .info-row { display:flex; border-bottom:1px solid #F3F4F6; }
        .info-row:last-child { border-bottom:none; }
        .info-lbl { background:#F9FAFB; padding:9px 14px; font-size:12px; font-weight:700; color:#6B7280; width:45%; text-transform:uppercase; letter-spacing:.03em; }
        .info-val { padding:9px 14px; font-size:13px; font-weight:600; width:55%; }
        .pdf-note { background:#FFF7ED; border:1px solid #FED7AA; border-radius:8px; padding:14px 18px; font-size:12.5px; color:#92400E; margin:20px 0; }
        .footer { background:#F9FAFB; border-top:1px solid #E5E7EB; padding:18px 32px; text-align:center; font-size:11.5px; color:#9CA3AF; }
        .footer a { color:#6B7280; }
    </style>
</head>
<body>
@php
    $paiement  = $paiement;
    $quittance = $paiement->quittance;
    $location  = $paiement->location;
    $bien      = $location->bien;
    $proprio   = $bien->proprietaire;
    $sym       = $proprio ? ($proprio->deviseSymbole()) : 'FCFA';
    $periode   = ucfirst($paiement->date_echeance->isoFormat('MMMM YYYY'));
    $montant   = number_format($paiement->montant, 0, ',', ' ');
    $emetteur  = $proprio?->name ?? config('app.name');
@endphp

<div class="wrap">
    <div class="top">
        <h1>Quittance de Loyer</h1>
        <div class="sub">ImmoGest &nbsp;·&nbsp; Gestion immobilière</div>
    </div>

    <div class="body">
        <p style="margin:0 0 16px">Bonjour <strong>{{ $location->locataire?->name ?? 'Locataire' }}</strong>,</p>

        <p style="margin:0 0 4px;color:#374151">
            Nous vous confirmons la bonne réception de votre loyer du mois de
            <strong>{{ $periode }}</strong> pour le bien :
        </p>
        <p style="margin:4px 0 0;font-weight:700;font-size:15px;color:#111">{{ $bien->titre }}</p>
        @if($bien->adresse)
        <p style="margin:2px 0 0;font-size:12.5px;color:#6B7280">{{ $bien->adresse }}, {{ $bien->ville ?? '' }}</p>
        @endif

        <div class="highlight">
            <div style="font-size:12px;font-weight:700;color:#166534;text-transform:uppercase;letter-spacing:.04em">Montant reçu</div>
            <div class="amount">{{ $montant }} {{ $sym }}</div>
        </div>

        <div class="info-grid">
            <div class="info-row">
                <div class="info-lbl">N° Quittance</div>
                <div class="info-val" style="font-family:monospace">{{ $quittance->numero }}</div>
            </div>
            <div class="info-row">
                <div class="info-lbl">Période</div>
                <div class="info-val">{{ $periode }}</div>
            </div>
            <div class="info-row">
                <div class="info-lbl">Date de paiement</div>
                <div class="info-val">{{ $paiement->date_paiement?->isoFormat('D MMMM YYYY') ?? $quittance->date_emission->isoFormat('D MMMM YYYY') }}</div>
            </div>
            <div class="info-row">
                <div class="info-lbl">Méthode</div>
                <div class="info-val">
                    @php
                        $methode = match($paiement->methode_paiement ?? '') {
                            'virement','prelevement' => 'Virement bancaire',
                            'cheque'                 => 'Chèque',
                            'cb','carte'             => 'Carte bancaire',
                            'mobile_money'           => 'Mobile Money',
                            default                  => 'Espèces',
                        };
                    @endphp
                    {{ $methode }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-lbl">Émis par</div>
                <div class="info-val">{{ $emetteur }}</div>
            </div>
        </div>

        <div class="pdf-note">
            <strong>Quittance en pièce jointe</strong><br>
            Votre quittance officielle est jointe à cet e-mail au format PDF.
            Conservez-la précieusement pendant toute la durée de votre bail et au moins 10 ans après sa fin.
        </div>

        <p style="margin:0;font-size:13px;color:#374151">
            Pour toute question, contactez votre bailleur ou l'agence ImmoGest.
        </p>
    </div>

    <div class="footer">
        Cet e-mail a été généré automatiquement par <strong>ImmoGest</strong>.<br>
        Ne pas répondre directement à cet e-mail.
        @if($proprio?->email)
        — Contact : <a href="mailto:{{ $proprio->email }}">{{ $proprio->email }}</a>
        @endif
    </div>
</div>
</body>
</html>
