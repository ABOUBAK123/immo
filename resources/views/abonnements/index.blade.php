@extends('layouts.app')
@section('title', 'Mon abonnement')
@section('page-title', 'Mon abonnement')

@push('styles')
<style>
.canal-card {
    display:flex;flex-direction:column;align-items:center;gap:8px;
    padding:18px 14px;border:2px solid #E5E7EB;border-radius:12px;
    cursor:pointer;transition:.2s;background:#fff;text-align:center;
    position:relative;
}
.canal-card:hover  { transform:translateY(-2px);box-shadow:0 4px 14px rgba(0,0,0,.1); }
.canal-card.active { border-width:2.5px; }
.canal-emoji { font-size:2rem;line-height:1; }
.canal-label { font-size:.78rem;font-weight:700;color:#374151; }
.canal-badge {
    position:absolute;top:-7px;right:-7px;
    background:#16A34A;color:#fff;font-size:.6rem;font-weight:700;
    padding:2px 6px;border-radius:10px;border:2px solid #fff;
}
.canal-badge.qr { background:#EA580C; }
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.pulse{animation:pulse 1.2s ease-in-out infinite}
@keyframes spin { to { transform:rotate(360deg); } }
.spin { animation:spin 1s linear infinite; display:inline-block; }
.formule-tab {
    padding:8px 18px;border-radius:10px;border:2px solid #E5E7EB;
    font-size:.8rem;font-weight:700;cursor:pointer;transition:.2s;background:#fff;
    white-space:nowrap;
}
.formule-tab:hover { border-color:var(--couleur); color:var(--couleur); }
.formule-tab.active { background:var(--couleur); color:#fff; border-color:var(--couleur); }
</style>
@endpush

@section('content')
@php $user = auth()->user(); @endphp

{{-- ── Alerte abonnement requis ─────────────────────────────────────────── --}}
@if(session('abonnement_requis'))
<div style="background:linear-gradient(135deg,#FFF1F2,#FFE4E6);border:1px solid #FECDD3;border-radius:14px;
            padding:18px 22px;margin-bottom:24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="width:44px;height:44px;border-radius:12px;background:#DC2626;display:flex;align-items:center;
                justify-content:center;color:#fff;font-size:1.3rem;flex-shrink:0">
        <i class="bi bi-shield-exclamation"></i>
    </div>
    <div style="flex:1">
        <div style="font-weight:700;color:#9F1239">Accès bloqué — Abonnement requis</div>
        <div style="font-size:.82rem;color:#BE123C;margin-top:2px">
            Souscrivez à une formule pour accéder à toutes les fonctionnalités ImmoGest.
        </div>
    </div>
</div>
@endif

@if(session('abonnement_expire_bientot') !== null)
<div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:12px;padding:14px 20px;margin-bottom:20px;
            display:flex;align-items:center;gap:12px">
    <i class="bi bi-clock-history" style="color:#D97706;font-size:1.2rem"></i>
    <span style="font-size:.85rem;color:#92400E;font-weight:600">
        Votre abonnement expire dans <strong>{{ session('abonnement_expire_bientot') }} jour(s)</strong>. Renouvelez maintenant pour éviter toute interruption.
    </span>
</div>
@endif

@if(session('success'))
<div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:14px 20px;margin-bottom:20px;
            display:flex;align-items:center;gap:10px">
    <i class="bi bi-check-circle-fill" style="color:#16A34A;font-size:1.1rem"></i>
    <span style="font-size:.85rem;color:#15803D;font-weight:600">{{ session('success') }}</span>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 400px;gap:24px;align-items:start">

{{-- ── Colonne gauche : statut + features + historique ───────────────── --}}
<div>

    {{-- Statut actuel --}}
    @if($abonnementActif)
    <div class="card-immo" style="padding:28px;margin-bottom:20px;
         background:linear-gradient(135deg,#F0FDF4,#DCFCE7);border:1.5px solid #86EFAC">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:16px">
                <div style="width:56px;height:56px;border-radius:14px;background:#16A34A;display:flex;align-items:center;
                            justify-content:center;color:#fff;font-size:1.5rem;flex-shrink:0">
                    <i class="bi bi-shield-check-fill"></i>
                </div>
                <div>
                    <div style="font-size:1rem;font-weight:800;color:#15803D">Abonnement actif</div>
                    @if($abonnementActif->formule)
                    <div style="margin-top:4px">
                        <span style="display:inline-flex;align-items:center;gap:5px;
                                     background:#fff;border-radius:8px;padding:3px 10px;
                                     font-size:.78rem;font-weight:700;color:{{ $abonnementActif->formule->couleur }}">
                            <i class="bi {{ $abonnementActif->formule->icone }}"></i>
                            Formule {{ $abonnementActif->formule->nom }}
                        </span>
                    </div>
                    @endif
                    <div style="font-size:.82rem;color:#166534;margin-top:4px">
                        Valable jusqu'au <strong>{{ $abonnementActif->date_fin->format('d/m/Y') }}</strong>
                    </div>
                    @if($abonnementActif->essai)
                    <span style="display:inline-block;margin-top:6px;background:#DBEAFE;color:#1D4ED8;
                                 font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:10px">
                        Période d'essai
                    </span>
                    @endif
                </div>
            </div>
            <div style="text-align:right">
                @php $jours = $abonnementActif->joursRestants(); @endphp
                <div style="font-size:2rem;font-weight:800;color:#15803D;line-height:1">{{ $jours }}</div>
                <div style="font-size:.72rem;color:#166534;font-weight:600">jour(s) restant(s)</div>
                <div style="margin-top:8px;background:#fff;border-radius:8px;height:8px;width:140px;overflow:hidden">
                    <div style="height:100%;background:#16A34A;width:{{ min(100, round($jours/30*100)) }}%;transition:width .5s"></div>
                </div>
            </div>
        </div>
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid #BBF7D0;
                    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <span style="font-size:.78rem;color:#166534">
                <i class="bi bi-arrow-repeat me-1"></i>
                Renouvellement : {{ $abonnementActif->date_fin->format('d/m/Y') }}
            </span>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="{{ route('abonnements.formules') }}"
                   style="background:#fff;color:#166534;border:1px solid #86EFAC;border-radius:8px;
                          padding:6px 14px;font-size:.78rem;font-weight:600;text-decoration:none">
                    <i class="bi bi-arrow-up-circle me-1"></i>Changer de formule
                </a>
                @if($jours <= 10)
                <button onclick="document.getElementById('paySection').scrollIntoView({behavior:'smooth'})"
                        style="background:#16A34A;color:#fff;border:none;border-radius:8px;padding:7px 16px;
                               font-size:.8rem;font-weight:700;cursor:pointer">
                    <i class="bi bi-arrow-repeat me-1"></i> Renouveler maintenant
                </button>
                @endif
            </div>
        </div>
    </div>
    @else
    <div class="card-immo" style="padding:28px;margin-bottom:20px;
         background:linear-gradient(135deg,#FFF1F2,#FFE4E6);border:1.5px solid #FECDD3">
        <div style="display:flex;align-items:center;gap:16px">
            <div style="width:56px;height:56px;border-radius:14px;background:#DC2626;display:flex;align-items:center;
                        justify-content:center;color:#fff;font-size:1.5rem;flex-shrink:0">
                <i class="bi bi-shield-x-fill"></i>
            </div>
            <div>
                <div style="font-size:1rem;font-weight:800;color:#9F1239">Aucun abonnement actif</div>
                <div style="font-size:.82rem;color:#BE123C;margin-top:2px">
                    Souscrivez ci-contre pour accéder à toutes les fonctionnalités.
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Ce qui est inclus dans la formule sélectionnée --}}
    @if($formuleSelectionnee)
    <div class="card-immo" style="padding:24px;margin-bottom:20px">
        <div style="font-size:.85rem;font-weight:800;color:#374151;margin-bottom:16px;
                    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <span>
                <i class="bi {{ $formuleSelectionnee->icone }} me-2" style="color:{{ $formuleSelectionnee->couleur }}"></i>
                Formule <span style="color:{{ $formuleSelectionnee->couleur }}">{{ $formuleSelectionnee->nom }}</span> — Ce qui est inclus
            </span>
            <a href="{{ route('abonnements.formules') }}" style="font-size:.75rem;color:#6B7280;text-decoration:none">
                Voir toutes les formules →
            </a>
        </div>
        @php
        $features = [
            ['bi bi-buildings',  'Biens immobiliers',        $formuleSelectionnee->limiteLabel('max_biens') . ' bien(s) maximum',             true],
            ['bi bi-people',     'Locataires',               $formuleSelectionnee->limiteLabel('max_locataires') . ' locataire(s) maximum',     true],
            ['bi bi-wallet2',    'Suivi des paiements',      'Loyers, quittances automatiques, relances',                                       true],
            ['bi bi-filetype-pdf','Export PDF',              'Quittances et reçus en PDF',                                                      $formuleSelectionnee->has_export_pdf],
            ['bi bi-tools',      'Interventions',            'Suivi des travaux et maintenances',                                               $formuleSelectionnee->has_interventions],
            ['bi bi-megaphone',  'Annonces',                 'Publication d\'annonces courte/longue durée',                                     $formuleSelectionnee->has_annonces],
            ['bi bi-wallet',     'Dépenses',                 'Suivi comptable des dépenses agence',                                             $formuleSelectionnee->has_depenses],
            ['bi bi-bell',       'Notifications SMS',        'Alertes SMS et WhatsApp aux locataires',                                          $formuleSelectionnee->has_notifications_sms],
            ['bi bi-robot',      'Agent IA',                 'Rédaction intelligente et analyses',                                              $formuleSelectionnee->has_ia],
        ];
        @endphp
        <div style="display:grid;gap:8px">
            @foreach($features as [$icon, $titre, $desc, $actif])
            <div style="display:flex;align-items:flex-start;gap:12px;{{ $actif?'':'opacity:.45' }}">
                <div style="width:34px;height:34px;border-radius:8px;
                            background:{{ $actif ? $formuleSelectionnee->couleur.'22' : '#F3F4F6' }};
                            color:{{ $actif ? $formuleSelectionnee->couleur : '#9CA3AF' }};
                            display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="{{ $icon }}"></i>
                </div>
                <div>
                    <div style="font-size:.84rem;font-weight:600;color:#1F2937">{{ $titre }}</div>
                    <div style="font-size:.75rem;color:#9CA3AF">{{ $desc }}</div>
                </div>
                @if(!$actif)
                <span style="margin-left:auto;font-size:.68rem;background:#FFF7ED;color:#EA580C;
                              padding:2px 8px;border-radius:8px;font-weight:700;white-space:nowrap;flex-shrink:0">
                    <i class="bi bi-lock me-1"></i>Plan supérieur
                </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Historique --}}
    @if($historique->count())
    <div class="card-immo">
        <div style="padding:18px 20px;border-bottom:1px solid #F3F4F6;font-size:.85rem;font-weight:700;color:#374151">
            <i class="bi bi-clock-history me-2" style="color:#9CA3AF"></i>Historique des abonnements
        </div>
        <table class="table-immo">
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Formule</th>
                    <th>Période</th>
                    <th>Montant</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            @foreach($historique as $a)
            <tr>
                <td style="font-size:.78rem;font-weight:600;color:#374151">{{ $a->invoice_number }}</td>
                <td>
                    @if($a->formule)
                    <span style="font-size:.75rem;font-weight:700;color:{{ $a->formule->couleur }}">
                        <i class="bi {{ $a->formule->icone }} me-1"></i>{{ $a->formule->nom }}
                    </span>
                    @else
                    <span style="font-size:.75rem;color:#9CA3AF">Standard</span>
                    @endif
                </td>
                <td style="font-size:.78rem;color:#6B7280">
                    {{ $a->date_debut->format('d/m/Y') }} → {{ $a->date_fin->format('d/m/Y') }}
                    @if($a->essai)<span style="font-size:.68rem;background:#DBEAFE;color:#1D4ED8;padding:1px 6px;border-radius:8px;margin-left:4px">Essai</span>@endif
                </td>
                <td style="font-weight:700;font-size:.82rem">
                    @if($a->montant == 0) <span style="color:#16A34A">Gratuit</span>
                    @else {{ number_format($a->montant, 0, ',', ' ') }} {{ $a->deviseSymbole() }}
                    @endif
                </td>
                <td>
                    @if($a->statut === 'actif' && $a->date_fin->isFuture())
                    <span class="badge-pill badge-success" style="font-size:.7rem">Actif</span>
                    @elseif($a->statut === 'actif')
                    <span class="badge-pill" style="font-size:.7rem;background:#F3F4F6;color:#6B7280">Expiré</span>
                    @elseif($a->statut === 'en_attente')
                    <span class="badge-pill badge-warning" style="font-size:.7rem">En attente</span>
                    @else
                    <span class="badge-pill" style="font-size:.7rem;background:#FFF1F2;color:#DC2626">Annulé</span>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>

{{-- ── Colonne droite : sélecteur formule + paiement ─────────────────── --}}
<div id="paySection">
    <div class="card-immo" style="padding:0;overflow:hidden;position:sticky;top:76px">

        {{-- Sélecteur de formule --}}
        @if($formules->count() > 1)
        <div style="padding:18px 20px;border-bottom:1px solid #F3F4F6">
            <div style="font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;
                         letter-spacing:.06em;margin-bottom:10px">Choisir une formule</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                @foreach($formules as $f)
                <button class="formule-tab {{ $formuleSelectionnee?->id === $f->id ? 'active' : '' }}"
                        style="--couleur:{{ $f->couleur }};border-color:{{ $formuleSelectionnee?->id === $f->id ? $f->couleur : '#E5E7EB' }};
                               color:{{ $formuleSelectionnee?->id === $f->id ? '#fff' : '#374151' }};
                               background:{{ $formuleSelectionnee?->id === $f->id ? $f->couleur : '#fff' }}"
                        onclick="changerFormule('{{ $f->slug }}', '{{ $f->couleur }}', {{ $f->prix_mensuel }}, '{{ \App\Models\User::DEVISES[$f->devise]['symbole'] ?? $f->devise }}', {{ $f->id }})">
                    <i class="bi {{ $f->icone }} me-1"></i>{{ $f->nom }}
                    @if($f->populaire)<span style="font-size:.6rem;opacity:.75"> ★</span>@endif
                </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- En-tête tarif --}}
        <div id="tarifHeader" style="background:linear-gradient(135deg,{{ $formuleSelectionnee?->couleur ?? '#EA580C' }},{{ $formuleSelectionnee?->couleur ?? '#F97316' }}dd);color:#fff;padding:24px 24px 20px">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;opacity:.85;margin-bottom:8px">
                Formule <span id="tarifNom">{{ $formuleSelectionnee?->nom ?? 'Standard' }}</span> — mensuel
            </div>
            <div style="display:flex;align-items:flex-end;gap:6px">
                <span id="tarifPrix" style="font-size:2.4rem;font-weight:800;line-height:1">{{ number_format($prix, 0, ',', ' ') }}</span>
                <span id="tarifDevise" style="font-size:1rem;font-weight:600;opacity:.9;padding-bottom:4px">{{ $devSymbole }}</span>
                <span style="font-size:.78rem;opacity:.75;padding-bottom:6px">/mois</span>
            </div>
            <div style="font-size:.75rem;opacity:.8;margin-top:6px">
                <i class="bi bi-shield-check me-1"></i>Paiement sécurisé · Quittance automatique
            </div>
        </div>

        <div style="padding:22px">

            {{-- Champ caché formule_id --}}
            <input type="hidden" id="formuleIdInput" value="{{ $formuleSelectionnee?->id }}">

            {{-- Choix canal --}}
            <div style="font-size:.78rem;font-weight:700;color:#374151;margin-bottom:10px">
                Choisissez votre moyen de paiement
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px" id="canalGrid">
                @foreach(\App\Models\Paiement::CANAUX_MOBILE as $code => $info)
                @php
                    $hasApi = $operateursDirects[$code] ?? false;
                    $hasQr  = !empty($qrCodes[$code]);
                @endphp
                <div class="canal-card" data-canal="{{ $code }}"
                     data-has-api="{{ $hasApi ? '1' : '0' }}"
                     data-has-qr="{{ $hasQr ? '1' : '0' }}"
                     data-qr-url="{{ $qrCodes[$code] ?? '' }}"
                     onclick="selectCanal('{{ $code }}', '{{ $info['color'] }}', '{{ $info['bg'] }}')">
                    @if($code !== 'carte')
                        @if($hasApi)
                            <span class="canal-badge">API ✓</span>
                        @elseif($hasQr)
                            <span class="canal-badge qr">QR</span>
                        @endif
                    @endif
                    <div class="canal-emoji">{{ $info['emoji'] }}</div>
                    <div class="canal-label">{{ $info['label'] }}</div>
                </div>
                @endforeach
            </div>

            {{-- Champ téléphone MTN MoMo --}}
            <div id="telephoneSection" style="display:none;margin-bottom:14px">
                <label style="font-size:.78rem;font-weight:700;color:#374151;display:block;margin-bottom:6px">
                    <i class="bi bi-phone me-1" style="color:#FFCC00"></i>Numéro MTN Mobile Money
                </label>
                <input type="tel" id="telephoneInput"
                       value="{{ auth()->user()->phone ?? '' }}"
                       placeholder="+225 07 XX XX XX XX"
                       style="width:100%;padding:10px 12px;border:1.5px solid #E5E7EB;border-radius:8px;
                              font-size:.875rem;outline:none;transition:.2s"
                       onfocus="this.style.borderColor='#FFCC00'"
                       onblur="this.style.borderColor='#E5E7EB'">
                <div style="font-size:.7rem;color:#9CA3AF;margin-top:4px">
                    <i class="bi bi-info-circle me-1"></i>
                    Vous recevrez une notification USSD sur ce numéro pour confirmer le paiement.
                </div>
            </div>

            {{-- Zone QR code --}}
            <div id="qrSection" style="display:none;margin-bottom:14px">
                <div style="text-align:center;padding:16px;background:#F9FAFB;border-radius:10px;border:1px dashed #D1D5DB">
                    <img id="qrImage" src="" alt="QR Code"
                         style="width:180px;height:180px;object-fit:contain;border-radius:8px;margin-bottom:10px">
                    <div style="font-size:.78rem;color:#374151;font-weight:600;margin-bottom:4px">
                        Scannez ce QR code avec votre application mobile
                    </div>
                    <div style="font-size:.72rem;color:#6B7280;margin-bottom:12px">
                        Envoyez exactement <strong id="prixQrLabel">{{ number_format($prix, 0, ',', ' ') }} {{ $devSymbole }}</strong>
                    </div>
                    <div style="text-align:left">
                        <label style="font-size:.75rem;font-weight:600;color:#374151;display:block;margin-bottom:5px">
                            Référence de transaction (obligatoire)
                        </label>
                        <input type="text" id="referenceQr"
                               placeholder="Ex: CI240515XXXXX"
                               style="width:100%;padding:8px 10px;border:1.5px solid #E5E7EB;border-radius:7px;
                                      font-size:.82rem;outline:none;font-family:monospace"
                               onfocus="this.style.borderColor='#EA580C'"
                               onblur="this.style.borderColor='#E5E7EB'">
                    </div>
                </div>
            </div>

            {{-- Zone attente USSD MTN --}}
            <div id="ussdWaiting" style="display:none;margin-bottom:14px">
                <div style="background:#FFFDE7;border:1.5px solid #FFCC00;border-radius:10px;padding:16px;text-align:center">
                    <div style="font-size:1.5rem;margin-bottom:8px">
                        <i class="bi bi-phone-vibrate spin" style="color:#FFCC00"></i>
                    </div>
                    <div style="font-size:.85rem;font-weight:700;color:#92400E;margin-bottom:4px">
                        Notification envoyée sur votre téléphone
                    </div>
                    <div style="font-size:.75rem;color:#78350F;margin-bottom:10px">
                        Ouvrez votre application MTN MoMo ou composez <strong>*133#</strong> pour confirmer.
                    </div>
                    <div id="pollTimer" style="font-size:.72rem;color:#9CA3AF">
                        Vérification automatique dans <span id="countdownVal">5</span>s…
                    </div>
                </div>
            </div>

            {{-- Message d'état --}}
            <div id="aboMessage" style="display:none;margin-bottom:14px;font-size:.8rem;border-radius:8px;padding:10px 12px"></div>

            {{-- Bouton principal --}}
            <button id="aboBtn" onclick="payerAbonnement()" disabled
                    style="width:100%;padding:13px;border:none;border-radius:10px;font-size:.92rem;font-weight:700;
                           background:#E5E7EB;color:#9CA3AF;cursor:not-allowed;transition:.2s;
                           display:flex;align-items:center;justify-content:center;gap:8px">
                <i class="bi bi-lock-fill"></i> Choisissez un moyen de paiement
            </button>

            <div style="margin-top:12px;font-size:.7rem;color:#9CA3AF;text-align:center;line-height:1.6">
                <i class="bi bi-info-circle me-1"></i>
                Renouvellement mensuel manuel. Aucun prélèvement automatique.<br>
                Accès immédiat après confirmation du paiement.
            </div>
        </div>
    </div>
</div>

</div>{{-- /grid --}}
@endsection

@push('scripts')
<script>
let canalSelectionne  = null;
let canalColor        = '#EA580C';
let prixCourant       = {{ $prix }};
let deviseCourante    = '{{ $devSymbole }}';
let pollInterval      = null;
let countdownInterval = null;

const INITIER_URL  = '{{ route("abonnements.initier") }}';
const POLL_BASE    = '{{ url("/abonnements/statut") }}';
const CSRF_TOKEN   = '{{ csrf_token() }}';

// ── Changement de formule ────────────────────────────────────────────────
function changerFormule(slug, couleur, prix, devise, formuleId) {
    prixCourant    = prix;
    deviseCourante = devise;
    document.getElementById('formuleIdInput').value = formuleId;

    // Mettre à jour l'en-tête
    document.getElementById('tarifPrix').textContent  = prix.toLocaleString('fr-FR');
    document.getElementById('tarifDevise').textContent = devise;
    document.getElementById('prixQrLabel').textContent = prix.toLocaleString('fr-FR') + ' ' + devise;

    // Recharger la page avec la formule sélectionnée (pour mettre à jour la colonne gauche)
    window.location.href = '{{ route("abonnements.index") }}?formule=' + slug;
}

// ── Sélection canal ──────────────────────────────────────────────────────
function selectCanal(code, color, bg) {
    canalSelectionne = code;
    canalColor       = color;
    resetZones();

    document.querySelectorAll('.canal-card').forEach(c => {
        const sel = c.dataset.canal === code;
        c.classList.toggle('active', sel);
        c.style.borderColor = sel ? color : '#E5E7EB';
        c.style.background  = sel ? bg    : '#fff';
    });

    const card   = document.querySelector(`.canal-card[data-canal="${code}"]`);
    const hasApi = card?.dataset.hasApi === '1';
    const hasQr  = card?.dataset.hasQr  === '1';
    const qrUrl  = card?.dataset.qrUrl  ?? '';

    if (code === 'mtn_money' && hasApi) {
        document.getElementById('telephoneSection').style.display = 'block';
    } else if (hasQr && !hasApi && code !== 'carte') {
        document.getElementById('qrSection').style.display = 'block';
        document.getElementById('qrImage').src = qrUrl;
    }

    const btn = document.getElementById('aboBtn');
    btn.disabled = false;
    btn.style.background = color;
    btn.style.color      = '#fff';
    btn.style.cursor     = 'pointer';
    btn.innerHTML = `<i class="bi bi-send-fill"></i> Confirmer — ${prixCourant.toLocaleString('fr-FR')} ${deviseCourante}`;
}

function resetZones() {
    document.getElementById('telephoneSection').style.display = 'none';
    document.getElementById('qrSection').style.display        = 'none';
    document.getElementById('ussdWaiting').style.display      = 'none';
    document.getElementById('aboMessage').style.display       = 'none';
    if (pollInterval)      { clearInterval(pollInterval);      pollInterval      = null; }
    if (countdownInterval) { clearInterval(countdownInterval); countdownInterval = null; }
}

// ── Payer ────────────────────────────────────────────────────────────────
async function payerAbonnement() {
    if (!canalSelectionne) return;

    const btn    = document.getElementById('aboBtn');
    const msg    = document.getElementById('aboMessage');
    const card   = document.querySelector(`.canal-card[data-canal="${canalSelectionne}"]`);
    const hasQr  = card?.dataset.hasQr === '1' && card?.dataset.hasApi !== '1' && canalSelectionne !== 'carte';

    if (hasQr) {
        const ref = document.getElementById('referenceQr').value.trim();
        if (!ref) {
            msg.style.cssText = 'display:block;background:#FFF1F2;border:1px solid #FECDD3;color:#9F1239;padding:10px 12px';
            msg.innerHTML = '<i class="bi bi-exclamation-circle-fill me-2"></i>Veuillez saisir votre référence de transaction.';
            return;
        }
        afficherSucces(btn, msg, 'Paiement en attente de validation. Référence : <strong>' + ref + '</strong>. Un administrateur activera votre compte sous 24h.');
        return;
    }

    btn.disabled  = true;
    btn.innerHTML = '<span class="pulse"><i class="bi bi-hourglass-split"></i> Connexion à l\'opérateur…</span>';
    msg.style.display = 'none';

    const formData = new FormData();
    formData.append('_token',     CSRF_TOKEN);
    formData.append('canal',      canalSelectionne);
    formData.append('formule_id', document.getElementById('formuleIdInput').value);

    if (canalSelectionne === 'mtn_money' && card?.dataset.hasApi === '1') {
        const tel = document.getElementById('telephoneInput').value.trim();
        if (!tel) {
            msg.style.cssText = 'display:block;background:#FFF1F2;border:1px solid #FECDD3;color:#9F1239;padding:10px 12px';
            msg.innerHTML = '<i class="bi bi-exclamation-circle-fill me-2"></i>Veuillez saisir votre numéro MTN Mobile Money.';
            btn.disabled  = false;
            btn.innerHTML = `<i class="bi bi-send-fill"></i> Confirmer — ${prixCourant.toLocaleString('fr-FR')} ${deviseCourante}`;
            return;
        }
        formData.append('telephone', tel);
    }

    try {
        const res = await fetch(INITIER_URL, {
            method: 'POST', body: formData,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        let data;
        try { data = await res.json(); } catch(_) { data = {}; }

        if (!res.ok) throw new Error(data.message || `Erreur ${res.status}`);
        if (!data.ok) throw new Error(data.message || 'Erreur lors du paiement.');

        if (data.url) {
            btn.innerHTML = '<span class="pulse"><i class="bi bi-arrow-right-circle"></i> Redirection…</span>';
            window.location.href = data.url;
        } else if (data.ussd) {
            btn.style.background = '#FFCC00';
            btn.style.color      = '#78350F';
            btn.innerHTML        = '<i class="bi bi-phone-vibrate spin"></i> En attente de confirmation…';
            document.getElementById('telephoneSection').style.display = 'none';
            document.getElementById('ussdWaiting').style.display      = 'block';
            demarrerPoll(data.reference);
        } else {
            afficherSucces(btn, msg, '<strong>Abonnement activé !</strong> Accès complet débloqué. Redirection…');
            setTimeout(() => window.location.reload(), 2000);
        }
    } catch(err) {
        msg.style.cssText = 'display:block;background:#FFF1F2;border:1px solid #FECDD3;color:#9F1239;padding:10px 12px';
        msg.innerHTML = '<i class="bi bi-exclamation-circle-fill me-2"></i>' + err.message;
        btn.disabled  = false;
        btn.style.background = canalColor;
        btn.style.color      = '#fff';
        btn.innerHTML = `<i class="bi bi-send-fill"></i> Réessayer`;
    }
}

// ── Polling MTN MoMo ──────────────────────────────────────────────────────
function demarrerPoll(reference) {
    let attempts  = 0;
    const MAX     = 24;
    let countdown = 5;

    countdownInterval = setInterval(() => {
        countdown--;
        const el = document.getElementById('countdownVal');
        if (el) el.textContent = countdown;
        if (countdown <= 0) countdown = 5;
    }, 1000);

    pollInterval = setInterval(async () => {
        attempts++;
        if (attempts > MAX) {
            clearInterval(pollInterval); clearInterval(countdownInterval);
            const msg = document.getElementById('aboMessage');
            msg.style.cssText = 'display:block;background:#FFF1F2;border:1px solid #FECDD3;color:#9F1239;padding:10px 12px';
            msg.innerHTML = '<i class="bi bi-clock me-2"></i>Délai dépassé. Veuillez réessayer ou contacter le support.';
            document.getElementById('ussdWaiting').style.display = 'none';
            const btn = document.getElementById('aboBtn');
            btn.disabled = false;
            btn.style.background = '#FFCC00';
            btn.style.color = '#78350F';
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Réessayer';
            return;
        }
        try {
            const res  = await fetch(`${POLL_BASE}/${reference}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.statut === 'SUCCESSFUL') {
                clearInterval(pollInterval); clearInterval(countdownInterval);
                document.getElementById('ussdWaiting').style.display = 'none';
                const btn = document.getElementById('aboBtn');
                const msg = document.getElementById('aboMessage');
                afficherSucces(btn, msg, '<strong>Paiement confirmé !</strong> Abonnement activé. Redirection…');
                setTimeout(() => window.location.reload(), 2500);
            } else if (data.statut === 'FAILED') {
                clearInterval(pollInterval); clearInterval(countdownInterval);
                document.getElementById('ussdWaiting').style.display = 'none';
                const msg = document.getElementById('aboMessage');
                msg.style.cssText = 'display:block;background:#FFF1F2;border:1px solid #FECDD3;color:#9F1239;padding:10px 12px';
                msg.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i>Paiement refusé ou annulé. Veuillez réessayer.';
                const btn = document.getElementById('aboBtn');
                btn.disabled = false;
                btn.style.background = '#FFCC00'; btn.style.color = '#78350F';
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Réessayer';
            }
        } catch(_) {}
    }, 5000);
}

function afficherSucces(btn, msg, texte) {
    btn.style.background = '#16A34A';
    btn.style.color      = '#fff';
    btn.innerHTML        = '<i class="bi bi-check-circle-fill"></i> Abonnement activé !';
    msg.style.cssText    = 'display:block;background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D;padding:12px';
    msg.innerHTML        = '<i class="bi bi-check-circle-fill me-2"></i>' + texte;
}
</script>
@endpush
