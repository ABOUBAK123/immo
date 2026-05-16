<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relance loyer impayé</title>
    <style>
        body  { margin:0; padding:0; background:#F3F4F6; font-family:Arial,sans-serif; font-size:14px; color:#111; }
        .wrap { max-width:580px; margin:30px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
        .top  { padding:24px 32px; }
        .body { padding:4px 32px 28px; }
        .alert-box { border-radius:10px; padding:16px 20px; margin:0 0 20px; display:flex; align-items:flex-start; gap:14px; }
        .info-grid { border:1px solid #E5E7EB; border-radius:8px; overflow:hidden; margin:20px 0; }
        .info-row  { display:flex; border-bottom:1px solid #F3F4F6; }
        .info-row:last-child { border-bottom:none; }
        .info-lbl  { background:#F9FAFB; padding:9px 14px; font-size:11.5px; font-weight:700; color:#6B7280; width:42%; text-transform:uppercase; letter-spacing:.03em; }
        .info-val  { padding:9px 14px; font-size:13px; font-weight:600; width:58%; }
        .msg-body  { background:#FAFAFA; border-left:4px solid #6B7280; padding:14px 18px; border-radius:0 6px 6px 0; font-size:13.5px; line-height:1.7; color:#374151; white-space:pre-line; }
        .footer    { background:#F9FAFB; border-top:1px solid #E5E7EB; padding:16px 32px; text-align:center; font-size:11.5px; color:#9CA3AF; }
    </style>
</head>
<body>
@php
    $p         = $paiement;
    $locataire = $p->location->locataire;
    $bien      = $p->location->bien;
    $proprio   = $bien->proprietaire;
    $sym       = $proprio?->deviseSymbole() ?? 'FCFA';
    $montant   = number_format((float) $p->montant, 0, ',', ' ');
    $echeance  = $p->date_echeance->isoFormat('D MMMM YYYY');
    $retard    = today()->diffInDays($p->date_echeance);
    $nb        = $p->nb_relances + 1;

    [$topBg, $topColor, $icon, $label] = match(true) {
        $nb === 1 => ['#FFF7ED', '#92400E', '📋', 'Rappel de paiement'],
        $nb === 2 => ['#FFF1F2', '#9F1239', '⚠️', 'Relance urgente'],
        default   => ['#1F2937', '#fff',    '🔔', 'Mise en demeure'],
    };

    $alertBg     = $nb <= 2 ? ($nb === 1 ? '#FFFBEB' : '#FFF1F2') : '#FEE2E2';
    $alertBorder = $nb <= 2 ? ($nb === 1 ? '#FDE68A' : '#FECDD3') : '#FCA5A5';
    $alertColor  = $nb <= 2 ? ($nb === 1 ? '#78350F' : '#881337') : '#7F1D1D';
@endphp

<div class="wrap">

    {{-- En-tête coloré selon l'urgence --}}
    <div class="top" style="background:{{ $topBg }};color:{{ $topColor }}">
        <div style="font-size:1.5rem;margin-bottom:6px">{{ $icon }}</div>
        <div style="font-weight:900;font-size:1.1rem;letter-spacing:.02em">{{ $label }}</div>
        <div style="font-size:.82rem;opacity:.75;margin-top:3px">ImmoGest — Gestion immobilière</div>
    </div>

    <div class="body">
        <p style="margin:20px 0 16px">Bonjour <strong>{{ $locataire?->name ?? 'Monsieur/Madame' }}</strong>,</p>

        {{-- Alerte montant en retard --}}
        <div class="alert-box" style="background:{{ $alertBg }};border:1px solid {{ $alertBorder }}">
            <div style="font-size:1.8rem;line-height:1">💸</div>
            <div>
                <div style="font-weight:800;font-size:.82rem;text-transform:uppercase;letter-spacing:.04em;color:{{ $alertColor }}">
                    Loyer impayé — {{ $retard }} jour{{ $retard > 1 ? 's' : '' }} de retard
                </div>
                <div style="font-size:1.5rem;font-weight:900;color:{{ $alertColor }};margin-top:2px">
                    {{ $montant }} {{ $sym }}
                </div>
                <div style="font-size:.8rem;color:{{ $alertColor }};opacity:.8;margin-top:2px">
                    Échéance initiale : {{ $echeance }}
                </div>
            </div>
        </div>

        {{-- Détails du bien --}}
        <div class="info-grid">
            <div class="info-row">
                <div class="info-lbl">Bien</div>
                <div class="info-val">{{ $bien->titre }}</div>
            </div>
            <div class="info-row">
                <div class="info-lbl">Adresse</div>
                <div class="info-val">{{ $bien->adresse }}, {{ $bien->ville }}</div>
            </div>
            <div class="info-row">
                <div class="info-lbl">Montant dû</div>
                <div class="info-val" style="color:#DC2626;font-size:1rem">{{ $montant }} {{ $sym }}</div>
            </div>
            <div class="info-row">
                <div class="info-lbl">Relance n°</div>
                <div class="info-val">{{ $nb }}</div>
            </div>
        </div>

        {{-- Message généré par l'IA --}}
        <div class="msg-body">{{ $messageIA }}</div>

        {{-- CTA --}}
        <div style="text-align:center;margin:24px 0 8px">
            <a href="{{ config('app.url') }}/paiements"
               style="display:inline-block;background:#DC2626;color:#fff;padding:12px 28px;border-radius:9px;
                      font-size:.88rem;font-weight:700;text-decoration:none;letter-spacing:.02em">
                Régulariser mon paiement
            </a>
        </div>
    </div>

    <div class="footer">
        Cet e-mail a été généré automatiquement par <strong>ImmoGest</strong>.<br>
        @if($proprio?->email)
        Pour toute question, contactez votre bailleur : <a href="mailto:{{ $proprio->email }}" style="color:#6B7280">{{ $proprio->email }}</a>
        @endif
    </div>

</div>
</body>
</html>
