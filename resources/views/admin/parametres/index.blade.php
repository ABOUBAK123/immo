@extends('layouts.app')
@section('title', 'Configuration APIs')
@section('page-title', 'Configuration des APIs')

@section('content')
@php
    $canalColors = ['sms'=>'#D97706','whatsapp'=>'#059669','email'=>'#2563EB','abonnement'=>'#059669','ia'=>'#7C3AED','paiement'=>'#0891B2','orange_money'=>'#FF6B00','mtn_momo'=>'#B8860B','wave'=>'#009EE3'];
    $canalBg     = ['sms'=>'#FFFBEB','whatsapp'=>'#F0FDF4','email'=>'#EFF6FF','abonnement'=>'#F0FDF4','ia'=>'#F5F3FF','paiement'=>'#ECFEFF','orange_money'=>'#FFF3E8','mtn_momo'=>'#FFFDE7','wave'=>'#E3F4FD'];
@endphp

{{-- ── En-tête ──────────────────────────────────────────────────────────────── --}}
<div class="card-immo" style="padding:20px 24px;margin-bottom:24px;background:linear-gradient(135deg,#1E40AF,#2563EB);color:#fff;border:none">
    <div style="display:flex;align-items:center;gap:14px">
        <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem">
            <i class="bi bi-gear-wide-connected"></i>
        </div>
        <div>
            <h2 style="font-size:1rem;font-weight:800;margin:0">Intégrations & APIs</h2>
            <p style="font-size:.78rem;opacity:.8;margin:4px 0 0">
                Configurez les canaux de communication — SMS, WhatsApp et Email — pour envoyer des notifications à vos locataires.
            </p>
        </div>
        <div style="margin-left:auto;display:flex;gap:10px">
            @foreach($schema as $grp => $cfg)
            <div style="text-align:center;padding:8px 14px;border-radius:10px;background:rgba(255,255,255,.1)">
                <i class="bi bi-{{ $cfg['icone'] }}" style="font-size:1.1rem"></i>
                <div style="font-size:.65rem;margin-top:4px;font-weight:600">{{ $cfg['titre'] }}</div>
                <div style="width:8px;height:8px;border-radius:50%;margin:4px auto 0;
                            background:{{ $statuts[$grp] ? '#4ADE80' : '#FCA5A5' }}"></div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Layout : sidebar onglets + panneau ─────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:220px 1fr;gap:20px;align-items:start">

    {{-- Sidebar onglets ─────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:6px">
        @foreach($schema as $grp => $cfg)
        <a href="{{ route('admin.parametres', ['tab' => $grp]) }}"
           style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;
                  text-decoration:none;transition:all .15s;
                  {{ $onglet === $grp
                      ? 'background:'.$canalBg[$grp].';border:1.5px solid '.$canalColors[$grp].'40;color:'.$canalColors[$grp]
                      : 'background:#fff;border:1.5px solid #E5E7EB;color:#6B7280' }}">
            <div style="width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;
                        background:{{ $onglet === $grp ? $canalColors[$grp] : '#F3F4F6' }};
                        color:{{ $onglet === $grp ? '#fff' : '#9CA3AF' }}">
                <i class="bi bi-{{ $cfg['icone'] }}"></i>
            </div>
            <div style="flex:1">
                <div style="font-size:.85rem;font-weight:700">{{ $cfg['titre'] }}</div>
                <div style="display:flex;align-items:center;gap:5px;margin-top:2px">
                    <div style="width:7px;height:7px;border-radius:50%;
                                background:{{ $statuts[$grp] ? '#16A34A' : '#DC2626' }}"></div>
                    <span style="font-size:.68rem;color:{{ $statuts[$grp] ? '#16A34A' : '#DC2626' }};font-weight:600">
                        {{ $statuts[$grp] ? 'Configuré' : 'Non configuré' }}
                    </span>
                </div>
            </div>
            @if($onglet === $grp)
            <i class="bi bi-chevron-right" style="font-size:.7rem;opacity:.6"></i>
            @endif
        </a>
        @endforeach

        {{-- Guide rapide --}}
        <div class="card-immo" style="padding:14px 16px;margin-top:10px;background:#F9FAFB;border:1px dashed #D1D5DB">
            <div style="font-size:.72rem;font-weight:700;color:#6B7280;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">
                <i class="bi bi-info-circle me-1"></i>Guide
            </div>
            <div style="font-size:.72rem;color:#9CA3AF;line-height:1.6">
                Les clés API sont stockées en base de données chiffrées.<br><br>
                Pour SMS/WhatsApp, un compte <strong>Twilio</strong> gratuit suffit pour tester.
            </div>
            <a href="https://www.twilio.com/try-twilio" target="_blank"
               style="display:inline-flex;align-items:center;gap:5px;margin-top:10px;font-size:.73rem;
                      color:#2563EB;text-decoration:none;font-weight:600">
                <i class="bi bi-box-arrow-up-right"></i> Créer un compte Twilio
            </a>
        </div>
    </div>

    {{-- Panneau principal ───────────────────────────────────────────────── --}}
    <div>
        @php $cfg = $schema[$onglet]; $color = $canalColors[$onglet]; $bg = $canalBg[$onglet]; @endphp

        @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;
                    background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D;font-size:.83rem;margin-bottom:16px">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        {{-- En-tête du panneau --}}
        <div class="card-immo" style="padding:20px 24px;margin-bottom:16px;border-left:4px solid {{ $color }}">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
                <div style="width:42px;height:42px;border-radius:10px;background:{{ $bg }};color:{{ $color }};
                            display:flex;align-items:center;justify-content:center;font-size:1.3rem">
                    <i class="bi bi-{{ $cfg['icone'] }}"></i>
                </div>
                <div>
                    <h3 style="font-size:.95rem;font-weight:800;margin:0">{{ $cfg['titre'] }}</h3>
                    <p style="font-size:.77rem;color:#9CA3AF;margin:3px 0 0">{{ $cfg['description'] }}</p>
                </div>
                <div style="margin-left:auto;display:flex;flex-direction:column;align-items:flex-end;gap:6px">
                    <span style="padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:700;
                                 background:{{ $statuts[$onglet] ? '#DCFCE7' : '#FEE2E2' }};
                                 color:{{ $statuts[$onglet] ? '#15803D' : '#B91C1C' }}">
                        <i class="bi bi-{{ $statuts[$onglet] ? 'check-circle' : 'x-circle' }} me-1"></i>
                        {{ $statuts[$onglet] ? 'Configuré & actif' : 'Non configuré' }}
                    </span>
                    <button type="button" id="btnTest"
                            onclick="testerConnexion('{{ $onglet }}')"
                            style="padding:5px 14px;border-radius:8px;border:1.5px solid {{ $color }}40;
                                   background:{{ $bg }};color:{{ $color }};font-size:.75rem;font-weight:700;cursor:pointer">
                        <i class="bi bi-lightning-charge me-1"></i>Tester la connexion
                    </button>
                </div>
            </div>

            {{-- Résultat test --}}
            <div id="testResult" style="display:none"></div>
        </div>

        {{-- Documentation intégration ─────────────────────────────── --}}
        @if($onglet === 'sms')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            @foreach([
                ['Twilio', 'bi-1-circle', '#E01F3D', 'console.twilio.com', 'Créez un compte → achetez un numéro → copiez Account SID et Auth Token.'],
                ['Vonage', 'bi-2-circle', '#9C27B0', 'dashboard.nexmo.com', 'Créez un compte → API Settings → copiez API Key et API Secret.'],
            ] as [$nom, $ico, $col, $url, $aide])
            <div style="border:1px solid #E5E7EB;border-radius:10px;padding:14px 16px;background:#FAFAFA">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                    <i class="bi {{ $ico }}" style="color:{{ $col }};font-size:1rem"></i>
                    <strong style="font-size:.83rem">{{ $nom }}</strong>
                </div>
                <p style="font-size:.73rem;color:#6B7280;margin:0 0 8px;line-height:1.5">{{ $aide }}</p>
                <a href="https://{{ $url }}" target="_blank"
                   style="font-size:.72rem;color:#2563EB;text-decoration:none;font-weight:600">
                    <i class="bi bi-box-arrow-up-right me-1"></i>{{ $url }}
                </a>
            </div>
            @endforeach
        </div>
        @elseif($onglet === 'whatsapp')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            @foreach([
                ['Twilio WhatsApp', 'bi-1-circle', '#E01F3D', 'Plus simple — Sandbox gratuit pour les tests, puis numéro WhatsApp Business approuvé.', 'console.twilio.com/develop/sms/try-it-out/whatsapp-learn-more'],
                ['Meta Cloud API', 'bi-2-circle', '#0866FF', 'API officielle Meta — nécessite un compte Meta Business + numéro vérifié WhatsApp.', 'developers.facebook.com/docs/whatsapp/cloud-api/get-started'],
            ] as [$nom, $ico, $col, $aide, $url])
            <div style="border:1px solid #E5E7EB;border-radius:10px;padding:14px 16px;background:#FAFAFA">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                    <i class="bi {{ $ico }}" style="color:{{ $col }};font-size:1rem"></i>
                    <strong style="font-size:.83rem">{{ $nom }}</strong>
                </div>
                <p style="font-size:.73rem;color:#6B7280;margin:0 0 8px;line-height:1.5">{{ $aide }}</p>
                <a href="https://{{ $url }}" target="_blank"
                   style="font-size:.72rem;color:#2563EB;text-decoration:none;font-weight:600;word-break:break-all">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Documentation
                </a>
            </div>
            @endforeach
        </div>
        @elseif($onglet === 'orange_money')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            <div style="border:1px solid #FDBA74;border-radius:10px;padding:14px 16px;background:#FFF7ED">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                    <span style="font-size:1.2rem">🍊</span>
                    <strong style="font-size:.83rem;color:#7C2D12">Étapes d'onboarding Orange Money</strong>
                </div>
                <ol style="font-size:.72rem;color:#92400E;padding-left:16px;line-height:1.7;margin:0">
                    <li>Créer un compte sur <strong>developer.orange.com</strong></li>
                    <li>Créer une application → obtenir Client ID et Client Secret</li>
                    <li>Souscrire au produit <strong>Orange Money Webpay</strong></li>
                    <li>Passer par le processus KYB (vérification marchande)</li>
                    <li>Obtenir la Merchant Key après validation</li>
                </ol>
            </div>
            <div style="border:1px solid #E5E7EB;border-radius:10px;padding:14px 16px;background:#FAFAFA">
                <div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:8px">Webhook à configurer</div>
                <div style="font-family:monospace;font-size:.7rem;background:#1E293B;color:#A3E635;padding:10px;border-radius:7px;word-break:break-all">
                    {{ route('webhooks.orange-money') }}
                </div>
                <div style="font-size:.68rem;color:#9CA3AF;margin-top:6px">Copiez cette URL dans votre portail Orange Developer → Webhook URL</div>
                <div style="margin-top:8px;font-size:.72rem;color:#374151">
                    <i class="bi bi-info-circle me-1" style="color:#FF6B00"></i>
                    Pays supportés : Côte d'Ivoire, Sénégal, Mali, Burkina Faso
                </div>
            </div>
        </div>
        @elseif($onglet === 'mtn_momo')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            <div style="border:1px solid #FDE68A;border-radius:10px;padding:14px 16px;background:#FEFCE8">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                    <span style="font-size:1.2rem">💛</span>
                    <strong style="font-size:.83rem;color:#713F12">Étapes d'onboarding MTN MoMo</strong>
                </div>
                <ol style="font-size:.72rem;color:#92400E;padding-left:16px;line-height:1.7;margin:0">
                    <li>Créer un compte sur <strong>momodeveloper.mtn.com</strong></li>
                    <li>Souscrire au produit <strong>Collection</strong></li>
                    <li>Générer l'API User via <code>POST /v1_0/apiuser</code></li>
                    <li>Générer l'API Key via <code>POST /v1_0/apiuser/{id}/apikey</code></li>
                    <li>Tester en Sandbox → demander accès Production</li>
                </ol>
            </div>
            <div style="border:1px solid #E5E7EB;border-radius:10px;padding:14px 16px;background:#FAFAFA">
                <div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:8px">Webhook MTN (optionnel)</div>
                <div style="font-family:monospace;font-size:.7rem;background:#1E293B;color:#A3E635;padding:10px;border-radius:7px;word-break:break-all">
                    {{ route('webhooks.mtn-momo') }}
                </div>
                <div style="font-size:.68rem;color:#9CA3AF;margin-top:6px">MTN MoMo peut notifier cette URL à chaque transaction</div>
                <div style="margin-top:8px;font-size:.72rem;color:#374151">
                    <i class="bi bi-phone-vibrate me-1" style="color:#FFCC00"></i>
                    Flux USSD Push : l'utilisateur reçoit une notification sur son téléphone
                </div>
            </div>
        </div>
        @elseif($onglet === 'wave')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            <div style="border:1px solid #BAE6FD;border-radius:10px;padding:14px 16px;background:#E3F4FD">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                    <span style="font-size:1.2rem">🌊</span>
                    <strong style="font-size:.83rem;color:#0C4A6E">Étapes d'onboarding Wave</strong>
                </div>
                <ol style="font-size:.72rem;color:#075985;padding-left:16px;line-height:1.7;margin:0">
                    <li>Créer un compte Wave Business sur <strong>wave.com/business</strong></li>
                    <li>Vérifier votre identité et votre entreprise</li>
                    <li>Accéder à Paramètres → Développeurs</li>
                    <li>Générer une clé API de production</li>
                    <li>Configurer l'URL de webhook pour les notifications</li>
                </ol>
            </div>
            <div style="border:1px solid #E5E7EB;border-radius:10px;padding:14px 16px;background:#FAFAFA">
                <div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:8px">Webhook Wave (checkout_status_url)</div>
                <div style="font-family:monospace;font-size:.7rem;background:#1E293B;color:#A3E635;padding:10px;border-radius:7px;word-break:break-all">
                    {{ route('webhooks.wave') }}
                </div>
                <div style="font-size:.68rem;color:#9CA3AF;margin-top:6px">Wave envoie un POST à cette URL quand le paiement est complété</div>
                <div style="margin-top:8px;font-size:.72rem;color:#374151">
                    <i class="bi bi-water me-1" style="color:#009EE3"></i>
                    Pays supportés : Sénégal, Côte d'Ivoire, Mali, Burkina Faso, Gambie
                </div>
            </div>
        </div>
        @elseif($onglet === 'email')
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px">
            @foreach([
                ['Gmail',      '#EA4335', 'smtp.gmail.com',      '587', 'Mot de passe d\'application requis (2FA activé)'],
                ['SendGrid',   '#1A82E2', 'smtp.sendgrid.net',   '587', 'Utilisateur : apikey — MDP : votre API Key SendGrid'],
                ['OVH',        '#123F8D', 'ssl.smtp.ovh.net',    '587', 'Vos identifiants email OVH standard'],
            ] as [$nom, $col, $host, $port, $aide])
            <div style="border:1px solid #E5E7EB;border-radius:10px;padding:12px 14px;background:#FAFAFA;cursor:pointer"
                 onclick="prefillSmtp('{{ $host }}','{{ $port }}')"
                 title="Cliquer pour préremplir">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                    <div style="width:28px;height:28px;border-radius:6px;background:{{ $col }}18;color:{{ $col }};
                                display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <strong style="font-size:.82rem">{{ $nom }}</strong>
                    <span style="margin-left:auto;font-size:.65rem;color:#9CA3AF;background:#F3F4F6;padding:1px 6px;border-radius:4px">cliquer</span>
                </div>
                <div style="font-size:.7rem;color:#6B7280;font-family:monospace">{{ $host }}:{{ $port }}</div>
                <div style="font-size:.68rem;color:#9CA3AF;margin-top:3px">{{ $aide }}</div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Formulaire de configuration ───────────────────────────── --}}
        <div class="card-immo">
            <div style="padding:16px 22px;border-bottom:1px solid #F3F4F6;font-size:.85rem;font-weight:700">
                <i class="bi bi-sliders me-2" style="color:{{ $color }}"></i>Paramètres de connexion
            </div>
            <form method="POST" action="{{ route('admin.parametres.update', $onglet) }}" style="padding:22px 24px">
                @csrf @method('PUT')

                <div style="display:flex;flex-direction:column;gap:18px">
                @foreach($cfg['champs'] as $champ)
                @php $valeur = $valeurs[$champ['cle']] ?? ''; @endphp
                <div>
                    <label class="form-label-immo" for="{{ $champ['cle'] }}">
                        {{ $champ['label'] }}
                        @if(in_array($champ['cle'], $cfg['cles_requises']))
                        <span style="color:#DC2626;margin-left:2px">*</span>
                        @endif
                    </label>

                    @if($champ['type'] === 'select')
                    <select name="{{ $champ['cle'] }}" id="{{ $champ['cle'] }}" class="form-select-immo">
                        @foreach($champ['options'] as $optVal => $optLbl)
                        <option value="{{ $optVal }}" {{ $valeur == $optVal ? 'selected' : '' }}>{{ $optLbl }}</option>
                        @endforeach
                    </select>

                    @elseif($champ['type'] === 'password')
                    <div style="position:relative">
                        <input type="password" name="{{ $champ['cle'] }}" id="{{ $champ['cle'] }}"
                               class="form-control-immo" placeholder="{{ $champ['placeholder'] ?? '' }}"
                               autocomplete="new-password"
                               style="padding-right:44px">
                        @if($valeur)
                        <div style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                    font-size:.7rem;color:#16A34A;font-weight:600;pointer-events:none">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        @endif
                        <button type="button" onclick="togglePw('{{ $champ['cle'] }}')"
                                style="position:absolute;right:{{ $valeur ? '40px' : '10px' }};top:50%;transform:translateY(-50%);
                                       background:none;border:none;color:#9CA3AF;cursor:pointer;padding:4px">
                            <i class="bi bi-eye" id="eye_{{ $champ['cle'] }}"></i>
                        </button>
                    </div>
                    @if($valeur)
                    <div style="font-size:.72rem;color:#16A34A;margin-top:4px">
                        <i class="bi bi-shield-check me-1"></i>Clé enregistrée — laisser vide pour conserver l'actuelle
                    </div>
                    @endif

                    @else
                    <input type="text" name="{{ $champ['cle'] }}" id="{{ $champ['cle'] }}"
                           class="form-control-immo"
                           value="{{ $valeur }}"
                           placeholder="{{ $champ['placeholder'] ?? '' }}">
                    @endif

                    @if(!empty($champ['aide']))
                    <div style="font-size:.72rem;color:#9CA3AF;margin-top:4px;display:flex;align-items:flex-start;gap:5px">
                        <i class="bi bi-info-circle" style="margin-top:1px;flex-shrink:0"></i>
                        {{ $champ['aide'] }}
                    </div>
                    @endif
                </div>
                @endforeach
                </div>

                <div style="display:flex;gap:10px;margin-top:24px;padding-top:20px;border-top:1px solid #F3F4F6">
                    <button type="submit" class="btn-primary-immo" style="flex:1;justify-content:center;background:{{ $color }}">
                        <i class="bi bi-floppy2"></i> Enregistrer la configuration {{ $cfg['titre'] }}
                    </button>
                    <button type="reset" class="btn-ghost" style="padding:8px 18px">
                        <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                    </button>
                </div>
            </form>
        </div>

        {{-- Snippet d'intégration code ────────────────────────────── --}}
        <div class="card-immo" style="margin-top:16px">
            <div style="padding:14px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:.83rem;font-weight:700">
                    <i class="bi bi-code-slash me-2" style="color:{{ $color }}"></i>Extrait d'intégration PHP
                </span>
                <button onclick="copyCode()" class="btn-ghost" style="padding:4px 10px;font-size:.72rem">
                    <i class="bi bi-clipboard"></i> Copier
                </button>
            </div>
            <div style="padding:16px 20px">
                <pre id="codeSnippet" style="background:#0F172A;color:#E2E8F0;padding:16px;border-radius:10px;
                     font-size:.75rem;overflow-x:auto;margin:0;line-height:1.7"><code>{{ $onglet === 'sms' ? "// SMS via Twilio\n\$twilio = new Twilio\\Rest\\Client(\n    config('services.twilio.sid'),\n    config('services.twilio.token')\n);\n\$twilio->messages->create(\n    \$destinataire,\n    ['from' => config('services.twilio.from'), 'body' => \$message]\n);" : ($onglet === 'whatsapp' ? "// WhatsApp via Twilio\n\$twilio = new Twilio\\Rest\\Client(\n    config('services.twilio.sid'),\n    config('services.twilio.token')\n);\n\$twilio->messages->create(\n    'whatsapp:' . \$destinataire,\n    ['from' => 'whatsapp:' . config('services.twilio.wa_from'),\n     'body' => \$message]\n);" : "// Email via Laravel Mail\nMail::raw(\$message, function(\$mail) use (\$destinataire) {\n    \$mail->to(\$destinataire)\n         ->from(config('mail.from.address'), config('mail.from.name'))\n         ->subject(\$sujet);\n});") }}</code></pre>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Afficher/masquer mot de passe ────────────────────────────────────────
function togglePw(id) {
    const inp = document.getElementById(id);
    const eye = document.getElementById('eye_' + id);
    if (inp.type === 'password') {
        inp.type = 'text';
        eye.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        eye.className = 'bi bi-eye';
    }
}

// ── Préremplir SMTP ────────────────────────────────────────────────────
function prefillSmtp(host, port) {
    const h = document.getElementById('mail_host');
    const p = document.getElementById('mail_port');
    if (h) { h.value = host; h.style.borderColor = '#2563EB'; }
    if (p) { p.value = port; p.style.borderColor = '#2563EB'; }
    setTimeout(() => {
        if (h) h.style.borderColor = '';
        if (p) p.style.borderColor = '';
    }, 1500);
}

// ── Tester la connexion ────────────────────────────────────────────────
function testerConnexion(groupe) {
    const btn    = document.getElementById('btnTest');
    const result = document.getElementById('testResult');

    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Test en cours…';
    btn.disabled  = true;
    result.style.display = 'none';

    fetch(`{{ url('/admin/parametres') }}/${groupe}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        result.style.display = 'flex';
        result.style.alignItems = 'center';
        result.style.gap = '10px';
        result.style.padding = '10px 14px';
        result.style.borderRadius = '8px';
        result.style.fontSize = '.8rem';
        result.style.marginTop = '10px';

        if (data.ok) {
            result.style.background = '#F0FDF4';
            result.style.border = '1px solid #BBF7D0';
            result.style.color = '#15803D';
            result.innerHTML = '<i class="bi bi-check-circle-fill fs-5"></i><span>' + data.message + '</span>';
        } else {
            result.style.background = '#FFF1F2';
            result.style.border = '1px solid #FECDD3';
            result.style.color = '#9F1239';
            result.innerHTML = '<i class="bi bi-x-circle-fill fs-5"></i><span>' + data.message + '</span>';
        }
    })
    .catch(() => {
        result.style.display = 'flex';
        result.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i><span>Erreur réseau. Vérifiez que le serveur est démarré.</span>';
    })
    .finally(() => {
        btn.innerHTML = '<i class="bi bi-lightning-charge me-1"></i>Tester la connexion';
        btn.disabled = false;
    });
}

// ── Copier le snippet ──────────────────────────────────────────────────
function copyCode() {
    navigator.clipboard.writeText(document.getElementById('codeSnippet').textContent)
        .then(() => {
            const btn = event.currentTarget;
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Copié !';
            btn.style.color = '#16A34A';
            setTimeout(() => { btn.innerHTML = orig; btn.style.color = ''; }, 2000);
        });
}
</script>
@endpush
