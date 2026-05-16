@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Centre de notifications')

@section('topbar-actions')
<button class="btn-primary-immo" data-bs-toggle="modal" data-bs-target="#modalCompose">
    <i class="bi bi-send"></i> Nouvelle notification
</button>
@endsection

@section('content')

{{-- ── KPIs ─────────────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px">
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#EFF6FF;color:#2563EB">
            <i class="bi bi-bell"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['total_mois'] }}</div>
            <div class="stat-label">Envoyées ce mois</div>
        </div>
    </div>
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#F0FDF4;color:#16A34A">
            <i class="bi bi-envelope-check"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['par_email'] }}</div>
            <div class="stat-label">Par Email</div>
        </div>
    </div>
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#FFFBEB;color:#D97706">
            <i class="bi bi-phone"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['par_sms'] }}</div>
            <div class="stat-label">Par SMS</div>
        </div>
    </div>
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#F0FDF4;color:#059669">
            <i class="bi bi-whatsapp"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['par_whatsapp'] }}</div>
            <div class="stat-label">Via WhatsApp</div>
        </div>
    </div>
    <div class="card-immo stat-card" style="border-color:{{ $stats['retardataires'] > 0 ? '#FECDD3' : '#E5E7EB' }}">
        <div class="stat-icon" style="background:#FFF1F2;color:#DC2626">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div>
            <div class="stat-val" style="color:{{ $stats['retardataires'] > 0 ? '#DC2626' : 'inherit' }}">
                {{ $stats['retardataires'] }}
            </div>
            <div class="stat-label">Locataires en retard</div>
        </div>
    </div>
</div>

{{-- ── Actions rapides ──────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">

    {{-- Relancer les retardataires --}}
    <div class="card-immo" style="padding:20px;border-left:4px solid #DC2626">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
            <div style="width:40px;height:40px;border-radius:10px;background:#FFF1F2;color:#DC2626;display:flex;align-items:center;justify-content:center;font-size:1.2rem">
                <i class="bi bi-alarm"></i>
            </div>
            <div>
                <div style="font-size:.88rem;font-weight:700">Relancer les retardataires</div>
                <div style="font-size:.72rem;color:#9CA3AF">{{ $stats['retardataires'] }} locataire(s) concerné(s)</div>
            </div>
        </div>
        <p style="font-size:.78rem;color:#6B7280;margin-bottom:14px">
            Envoie automatiquement une lettre de relance amiable à tous les locataires ayant un loyer impayé.
        </p>
        @if($stats['retardataires'] > 0)
        <form method="POST" action="{{ route('notifications.bulk-retards') }}">
            @csrf
            <div style="display:flex;gap:8px;margin-bottom:10px">
                @foreach(['email'=>['bi-envelope','Email'],'sms'=>['bi-phone','SMS'],'whatsapp'=>['bi-whatsapp','WhatsApp']] as $c=>[$ico,$lbl])
                <label style="flex:1;display:flex;align-items:center;justify-content:center;gap:5px;
                              padding:7px;border:1.5px solid #E5E7EB;border-radius:8px;cursor:pointer;
                              font-size:.75rem;font-weight:600" id="lbl_bulk_{{ $c }}">
                    <input type="radio" name="canal" value="{{ $c }}" style="display:none"
                           onchange="selectBulkCanal('{{ $c }}')">
                    <i class="bi {{ $ico }}"></i> {{ $lbl }}
                </label>
                @endforeach
            </div>
            <button type="submit" class="btn-primary-immo" style="width:100%;justify-content:center;background:#DC2626"
                    onclick="document.querySelector('[name=canal]:not([value])')">
                <i class="bi bi-send"></i> Envoyer les relances
            </button>
        </form>
        @else
        <div style="text-align:center;padding:10px;background:#F0FDF4;border-radius:8px;font-size:.78rem;color:#16A34A">
            <i class="bi bi-check-circle me-1"></i> Aucun retard à signaler
        </div>
        @endif
    </div>

    @php $moisLabel = now()->translatedFormat('F Y'); @endphp
    {{-- Alerte loyer à venir --}}
    <div class="card-immo" style="padding:20px;border-left:4px solid #D97706">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
            <div style="width:40px;height:40px;border-radius:10px;background:#FFFBEB;color:#D97706;display:flex;align-items:center;justify-content:center;font-size:1.2rem">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div>
                <div style="font-size:.88rem;font-weight:700">Rappel loyer du mois</div>
                <div style="font-size:.72rem;color:#9CA3AF">{{ $locataires->count() }} locataire(s)</div>
            </div>
        </div>
        <p style="font-size:.78rem;color:#6B7280;margin-bottom:14px">
            Envoyez un rappel préventif à tous vos locataires actifs en début de mois.
        </p>
        <button type="button" class="btn-primary-immo" style="width:100%;justify-content:center;background:#D97706"
                data-bs-toggle="modal" data-bs-target="#modalCompose"
                onclick="presetCompose('alerte_loyer', 'Rappel loyer — {{ $moisLabel }}')">
            <i class="bi bi-bell"></i> Envoyer les rappels
        </button>
    </div>

    {{-- Message personnalisé --}}
    <div class="card-immo" style="padding:20px;border-left:4px solid #2563EB">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
            <div style="width:40px;height:40px;border-radius:10px;background:#EFF6FF;color:#2563EB;display:flex;align-items:center;justify-content:center;font-size:1.2rem">
                <i class="bi bi-chat-text"></i>
            </div>
            <div>
                <div style="font-size:.88rem;font-weight:700">Message personnalisé</div>
                <div style="font-size:.72rem;color:#9CA3AF">Email, SMS ou WhatsApp</div>
            </div>
        </div>
        <p style="font-size:.78rem;color:#6B7280;margin-bottom:14px">
            Rédigez et envoyez un message libre à un ou plusieurs locataires.
        </p>
        <button type="button" class="btn-primary-immo" style="width:100%;justify-content:center"
                data-bs-toggle="modal" data-bs-target="#modalCompose"
                onclick="presetCompose('personnalise', '')">
            <i class="bi bi-pencil-square"></i> Rédiger un message
        </button>
    </div>

</div>

{{-- ── Historique ────────────────────────────────────────────────────────────── --}}
<div class="card-immo">
    <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <span style="font-size:.88rem;font-weight:700;flex:1">
            <i class="bi bi-clock-history me-2" style="color:#2563EB"></i>Historique des envois
        </span>
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap">
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Nom locataire…" class="form-control-immo" style="width:160px">
            <select name="canal" class="form-select-immo" style="width:auto" onchange="this.form.submit()">
                <option value="">Tous canaux</option>
                @foreach(['email'=>'Email','sms'=>'SMS','whatsapp'=>'WhatsApp'] as $v=>$l)
                <option value="{{ $v }}" {{ request('canal')===$v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select-immo" style="width:auto" onchange="this.form.submit()">
                <option value="">Tous types</option>
                @foreach(['alerte_loyer'=>'Alerte loyer','relance_retard'=>'Relance','quittance'=>'Quittance','personnalise'=>'Personnalisé'] as $v=>$l)
                <option value="{{ $v }}" {{ request('type')===$v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-ghost"><i class="bi bi-search"></i></button>
            @if(request()->hasAny(['q','canal','type']))
            <a href="{{ route('notifications.index') }}" class="btn-ghost">✕</a>
            @endif
        </form>
    </div>

    @if($notifications->count())
    <table class="table-immo">
        <thead>
            <tr>
                <th>Locataire</th>
                <th>Canal</th>
                <th>Type</th>
                <th>Message</th>
                <th>Statut</th>
                <th>Envoyé le</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($notifications as $n)
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:30px;height:30px;border-radius:50%;background:#DBEAFE;color:#1D4ED8;
                                display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($n->locataire->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:.82rem;font-weight:600">{{ $n->locataire->name }}</div>
                        <div style="font-size:.7rem;color:#9CA3AF">{{ $n->destinataire_contact }}</div>
                    </div>
                </div>
            </td>
            <td>
                @php $canalColor = ['email'=>'#16A34A','sms'=>'#D97706','whatsapp'=>'#059669'][$n->canal] ?? '#6B7280'; @endphp
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;
                             font-size:.72rem;font-weight:700;background:{{ $canalColor }}18;color:{{ $canalColor }}">
                    <i class="bi bi-{{ \App\Models\NotificationEnvoyee::canalIcon($n->canal) }}"></i>
                    {{ \App\Models\NotificationEnvoyee::canalLabel($n->canal) }}
                </span>
            </td>
            <td>
                <span class="badge-pill badge-gray" style="font-size:.7rem">
                    {{ \App\Models\NotificationEnvoyee::typeLabel($n->type) }}
                </span>
            </td>
            <td style="max-width:280px">
                <div style="font-size:.78rem;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:240px"
                     title="{{ $n->message }}">
                    {{ Str::limit($n->message, 60) }}
                </div>
                @if($n->sujet)
                <div style="font-size:.7rem;color:#9CA3AF">{{ $n->sujet }}</div>
                @endif
            </td>
            <td>
                @php
                    $sc = ['envoye'=>['badge-success','check-circle','Envoyé'],
                           'echec' =>['badge-danger','x-circle','Échec'],
                           'simule'=>['badge-gray','send','Simulé']][$n->statut] ?? ['badge-gray','question','?'];
                @endphp
                <span class="badge-pill {{ $sc[0] }}">
                    <i class="bi bi-{{ $sc[1] }}"></i> {{ $sc[2] }}
                </span>
            </td>
            <td style="font-size:.78rem;color:#6B7280;white-space:nowrap">
                {{ $n->sent_at?->format('d/m/Y H:i') ?? $n->created_at->format('d/m/Y H:i') }}
                <div style="font-size:.7rem;color:#9CA3AF">{{ $n->created_at->diffForHumans() }}</div>
            </td>
            <td>
                <button class="btn-ghost" style="padding:4px 8px;font-size:.75rem"
                        data-bs-toggle="modal" data-bs-target="#modalDetail{{ $n->id }}">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($notifications->hasPages())
    <div style="padding:14px 20px;border-top:1px solid #F3F4F6">{{ $notifications->links() }}</div>
    @endif

    @else
    <div style="padding:60px;text-align:center">
        <i class="bi bi-bell-slash" style="font-size:2.5rem;color:#E5E7EB"></i>
        <p style="color:#9CA3AF;margin:16px 0 20px;font-size:.88rem">Aucune notification envoyée.</p>
        <button class="btn-primary-immo" data-bs-toggle="modal" data-bs-target="#modalCompose">
            <i class="bi bi-send"></i> Envoyer votre première notification
        </button>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL COMPOSER                                                             --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCompose" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.18)">

            <div class="modal-header" style="border-bottom:1px solid #F3F4F6;padding:20px 24px">
                <div>
                    <h5 class="modal-title" style="font-weight:700;font-size:.95rem;margin:0">
                        <i class="bi bi-send me-2" style="color:#2563EB"></i>Composer une notification
                    </h5>
                    <div style="font-size:.75rem;color:#9CA3AF;margin-top:2px">Email · SMS · WhatsApp</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('notifications.send') }}" id="formCompose">
                @csrf
                <div class="modal-body" style="padding:24px">

                    {{-- Étape 1 : Destinataires ──────────────────────────── --}}
                    <div style="margin-bottom:20px">
                        <label class="form-label-immo" style="margin-bottom:8px">
                            <span style="display:inline-flex;align-items:center;justify-content:center;
                                         width:22px;height:22px;border-radius:50%;background:#2563EB;color:#fff;
                                         font-size:.7rem;font-weight:700;margin-right:8px">1</span>
                            Destinataires
                        </label>

                        {{-- Sélection rapide --}}
                        <div style="display:flex;gap:8px;margin-bottom:12px">
                            <button type="button" class="btn-ghost" style="font-size:.75rem;padding:5px 12px"
                                    onclick="selectAll(true)">
                                <i class="bi bi-check-all"></i> Tous sélectionner
                            </button>
                            <button type="button" class="btn-ghost" style="font-size:.75rem;padding:5px 12px"
                                    onclick="selectAll(false)">
                                <i class="bi bi-x"></i> Désélectionner
                            </button>
                            <button type="button" class="btn-ghost" style="font-size:.75rem;padding:5px 12px;color:#DC2626;border-color:#FECDD3"
                                    onclick="selectRetardataires()">
                                <i class="bi bi-exclamation-triangle"></i> En retard uniquement
                            </button>
                        </div>

                        {{-- Liste locataires --}}
                        <div style="border:1px solid #E5E7EB;border-radius:10px;max-height:200px;overflow-y:auto">
                            @forelse($locataires as $loc)
                            @php $locAct = $loc->locations->first(); @endphp
                            <label style="display:flex;align-items:center;gap:12px;padding:10px 14px;cursor:pointer;border-bottom:1px solid #F9FAFB;transition:background .1s"
                                   onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                                <input type="checkbox" name="locataire_ids[]" value="{{ $loc->id }}"
                                       class="loc-checkbox" data-retard="{{ $loc->id }}"
                                       style="width:16px;height:16px;accent-color:#2563EB;flex-shrink:0">
                                <div style="width:32px;height:32px;border-radius:50%;background:#DBEAFE;color:#1D4ED8;
                                            display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0">
                                    {{ strtoupper(substr($loc->name, 0, 1)) }}
                                </div>
                                <div style="flex:1">
                                    <div style="font-size:.83rem;font-weight:600">{{ $loc->name }}</div>
                                    <div style="font-size:.72rem;color:#9CA3AF">
                                        {{ $loc->email }}
                                        @if($loc->phone) · {{ $loc->phone }} @endif
                                    </div>
                                </div>
                                @if($locAct)
                                <div style="text-align:right">
                                    <div style="font-size:.72rem;font-weight:600;color:#2563EB">{{ number_format($locAct->loyer_mensuel,0,',',' ') }} €/mois</div>
                                    <div style="font-size:.68rem;color:#9CA3AF">{{ Str::limit($locAct->bien->titre, 20) }}</div>
                                </div>
                                @endif
                            </label>
                            @empty
                            <div style="padding:24px;text-align:center;color:#9CA3AF;font-size:.82rem">
                                Aucun locataire associé à vos biens.
                            </div>
                            @endforelse
                        </div>
                        <div id="selectionCount" style="font-size:.73rem;color:#9CA3AF;margin-top:6px">0 destinataire(s) sélectionné(s)</div>
                    </div>

                    {{-- Étape 2 : Canal ───────────────────────────────────── --}}
                    <div style="margin-bottom:20px">
                        <label class="form-label-immo" style="margin-bottom:8px">
                            <span style="display:inline-flex;align-items:center;justify-content:center;
                                         width:22px;height:22px;border-radius:50%;background:#2563EB;color:#fff;
                                         font-size:.7rem;font-weight:700;margin-right:8px">2</span>
                            Canal d'envoi
                        </label>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px" id="canalGrid">
                            @php
                                $canaux = [
                                    'email'    => ['bi-envelope-fill', 'Email', '#16A34A', 'Livraison garantie, historique consultable'],
                                    'sms'      => ['bi-phone-fill',    'SMS',   '#D97706', 'Reçu instantanément, sans internet'],
                                    'whatsapp' => ['bi-whatsapp',      'WhatsApp','#059669','Lecture rapide, accusé de réception'],
                                ];
                            @endphp
                            @foreach($canaux as $val => [$ico, $lbl, $color, $desc])
                            <label class="canal-card" id="cc_{{ $val }}"
                                   style="border:2px solid #E5E7EB;border-radius:10px;padding:14px 12px;cursor:pointer;
                                          display:flex;flex-direction:column;align-items:center;text-align:center;gap:6px;transition:all .15s">
                                <input type="radio" name="canal" value="{{ $val }}" style="display:none"
                                       onchange="selectCanal('{{ $val }}', '{{ $color }}')">
                                <i class="bi {{ $ico }}" style="font-size:1.4rem;color:{{ $color }}"></i>
                                <div style="font-size:.82rem;font-weight:700">{{ $lbl }}</div>
                                <div style="font-size:.68rem;color:#9CA3AF;line-height:1.3">{{ $desc }}</div>
                                @if($val === 'sms' || $val === 'whatsapp')
                                <span style="font-size:.62rem;background:#FEF3C7;color:#92400E;border-radius:4px;padding:2px 6px;font-weight:600">
                                    API requise
                                </span>
                                @endif
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Étape 3 : Template & Message ─────────────────────── --}}
                    <div style="margin-bottom:20px">
                        <label class="form-label-immo" style="margin-bottom:8px">
                            <span style="display:inline-flex;align-items:center;justify-content:center;
                                         width:22px;height:22px;border-radius:50%;background:#2563EB;color:#fff;
                                         font-size:.7rem;font-weight:700;margin-right:8px">3</span>
                            Message
                        </label>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
                            <div>
                                <label class="form-label-immo" style="font-size:.75rem">Type</label>
                                <select name="type" id="typeSelect" class="form-select-immo" onchange="applyTemplate(this.value)">
                                    <option value="alerte_loyer">Alerte loyer dû</option>
                                    <option value="relance_retard">Relance retard</option>
                                    <option value="quittance">Quittance disponible</option>
                                    <option value="personnalise">Message libre</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label-immo" style="font-size:.75rem">Sujet (email uniquement)</label>
                                <input type="text" name="sujet" id="sujetInput" class="form-control-immo"
                                       placeholder="Objet du message…">
                            </div>
                        </div>

                        {{-- Boutons templates rapides --}}
                        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
                            <span style="font-size:.72rem;color:#9CA3AF;align-self:center">Modèles :</span>
                            @foreach([
                                'alerte_loyer'   => ['#D97706','calendar-event','Rappel loyer'],
                                'relance_retard' => ['#DC2626','alarm','Relance retard'],
                                'quittance'      => ['#16A34A','file-earmark-check','Quittance'],
                            ] as $tpl=>[$col,$ico,$lab])
                            <button type="button" onclick="applyTemplate('{{ $tpl }}')"
                                    style="padding:4px 10px;border:1.5px solid {{ $col }}20;background:{{ $col }}10;
                                           color:{{ $col }};border-radius:6px;font-size:.72rem;font-weight:600;cursor:pointer">
                                <i class="bi bi-{{ $ico }} me-1"></i>{{ $lab }}
                            </button>
                            @endforeach
                        </div>

                        <textarea name="message" id="messageTextarea" class="form-control-immo"
                                  rows="6" placeholder="Votre message…" required
                                  oninput="updatePreview()"
                                  style="resize:vertical;font-family:inherit;line-height:1.6"></textarea>

                        <div style="margin-top:6px;display:flex;justify-content:space-between;align-items:center">
                            <span style="font-size:.7rem;color:#9CA3AF">
                                Variables : <code style="background:#F3F4F6;padding:1px 5px;border-radius:4px">[Prénom]</code>
                                <code style="background:#F3F4F6;padding:1px 5px;border-radius:4px">[Nom]</code>
                                <code style="background:#F3F4F6;padding:1px 5px;border-radius:4px">[Propriétaire]</code>
                            </span>
                            <span id="charCount" style="font-size:.7rem;color:#9CA3AF">0 caractères</span>
                        </div>
                    </div>

                    {{-- Aperçu ───────────────────────────────────────────── --}}
                    <div id="previewBox" style="display:none;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:10px;padding:14px 16px;margin-top:-6px">
                        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;margin-bottom:8px">
                            <i class="bi bi-eye me-1"></i>Aperçu
                        </div>
                        <div id="previewContent" style="font-size:.82rem;color:#374151;white-space:pre-wrap;line-height:1.7"></div>
                    </div>

                </div>

                <div class="modal-footer" style="border-top:1px solid #F3F4F6;padding:16px 24px;gap:10px">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-primary-immo" style="flex:1;justify-content:center">
                        <i class="bi bi-send-fill"></i> Envoyer la notification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODALS DÉTAIL                                                              --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@foreach($notifications as $n)
<div class="modal fade" id="modalDetail{{ $n->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 20px 40px rgba(0,0,0,.15)">
            <div class="modal-header border-0">
                <div>
                    <h6 class="modal-title fw-bold" style="font-size:.88rem">Notification #{{ $n->id }}</h6>
                    <div style="font-size:.72rem;color:#9CA3AF">{{ $n->created_at->format('d/m/Y à H:i') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <div style="display:flex;flex-direction:column;gap:10px">
                    <div style="display:flex;gap:8px;font-size:.8rem">
                        <span style="color:#9CA3AF;width:80px;flex-shrink:0">Destinataire</span>
                        <strong>{{ $n->locataire->name }}</strong>
                    </div>
                    <div style="display:flex;gap:8px;font-size:.8rem">
                        <span style="color:#9CA3AF;width:80px;flex-shrink:0">Contact</span>
                        <span>{{ $n->destinataire_contact ?: '—' }}</span>
                    </div>
                    <div style="display:flex;gap:8px;font-size:.8rem">
                        <span style="color:#9CA3AF;width:80px;flex-shrink:0">Canal</span>
                        <span>{{ \App\Models\NotificationEnvoyee::canalLabel($n->canal) }}</span>
                    </div>
                    <div style="display:flex;gap:8px;font-size:.8rem">
                        <span style="color:#9CA3AF;width:80px;flex-shrink:0">Type</span>
                        <span>{{ \App\Models\NotificationEnvoyee::typeLabel($n->type) }}</span>
                    </div>
                    @if($n->sujet)
                    <div style="display:flex;gap:8px;font-size:.8rem">
                        <span style="color:#9CA3AF;width:80px;flex-shrink:0">Sujet</span>
                        <span>{{ $n->sujet }}</span>
                    </div>
                    @endif
                    <div style="background:#F9FAFB;border-radius:8px;padding:12px;font-size:.8rem;white-space:pre-wrap;line-height:1.6;color:#374151;border:1px solid #E5E7EB">{{ $n->message }}</div>
                    @if($n->erreur)
                    <div style="background:#FFF1F2;border-radius:8px;padding:10px;font-size:.75rem;color:#DC2626">
                        <i class="bi bi-x-circle me-1"></i>{{ $n->erreur }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<script>
// ── Sélection destinataires ────────────────────────────────────────────────
const retardIds = @json($retardataires->pluck('location.locataire_id')->filter()->values());

function selectAll(state) {
    document.querySelectorAll('.loc-checkbox').forEach(c => c.checked = state);
    updateSelectionCount();
}

function selectRetardataires() {
    document.querySelectorAll('.loc-checkbox').forEach(c => {
        c.checked = retardIds.includes(parseInt(c.value));
    });
    updateSelectionCount();
}

function updateSelectionCount() {
    const n = document.querySelectorAll('.loc-checkbox:checked').length;
    document.getElementById('selectionCount').textContent = `${n} destinataire(s) sélectionné(s)`;
}

document.querySelectorAll('.loc-checkbox').forEach(c => c.addEventListener('change', updateSelectionCount));

// ── Sélection canal ────────────────────────────────────────────────────────
function selectCanal(val, color) {
    document.querySelectorAll('.canal-card').forEach(c => {
        c.style.borderColor = '#E5E7EB';
        c.style.background  = '';
    });
    const card = document.getElementById('cc_' + val);
    card.style.borderColor = color;
    card.style.background  = color + '10';
    card.querySelector('input').checked = true;
}

// ── Sélection canal bulk ───────────────────────────────────────────────────
function selectBulkCanal(val) {
    document.querySelectorAll('[id^=lbl_bulk_]').forEach(l => {
        l.style.borderColor = '#E5E7EB';
        l.style.background  = '';
        l.style.color       = '#6B7280';
    });
    const lbl = document.getElementById('lbl_bulk_' + val);
    if (lbl) {
        lbl.style.borderColor = '#2563EB';
        lbl.style.background  = '#EFF6FF';
        lbl.style.color       = '#2563EB';
        lbl.querySelector('input').checked = true;
    }
}

// ── Templates de messages ─────────────────────────────────────────────────
const templates = {
    alerte_loyer: {
        sujet: "Rappel loyer — {{ $moisLabel }}",
        message: "Bonjour [Prénom],\n\nNous vous rappelons que votre loyer mensuel est exigible. Merci de procéder au règlement dans les délais habituels.\n\nEn cas de question, n'hésitez pas à nous contacter.\n\nCordialement,\n[Propriétaire]"
    },
    relance_retard: {
        sujet: "⚠️ Loyer en retard — Régularisation urgente",
        message: "Bonjour [Prénom],\n\nNous constatons que votre loyer n'a pas encore été réglé à la date prévue.\n\nNous vous invitons à régulariser votre situation dans les meilleurs délais afin d'éviter toute procédure de recouvrement.\n\nSi vous rencontrez des difficultés, contactez-nous pour trouver une solution amiable.\n\nCordialement,\n[Propriétaire]"
    },
    quittance: {
        sujet: "🧾 Votre quittance de loyer est disponible",
        message: "Bonjour [Prénom],\n\nVotre quittance de loyer est disponible et peut être téléchargée depuis votre espace locataire sur ImmoGest.\n\nMerci pour votre règlement ponctuel.\n\nCordialement,\n[Propriétaire]"
    },
    personnalise: { sujet: '', message: '' }
};

function applyTemplate(type) {
    const tpl = templates[type];
    if (!tpl) return;
    document.getElementById('typeSelect').value = type;
    if (tpl.sujet) document.getElementById('sujetInput').value = tpl.sujet;
    document.getElementById('messageTextarea').value = tpl.message;
    updatePreview();
    updateCharCount();
}

function updatePreview() {
    const msg = document.getElementById('messageTextarea').value;
    const box = document.getElementById('previewBox');
    if (msg.trim()) {
        box.style.display = 'block';
        document.getElementById('previewContent').textContent =
            msg.replace('[Prénom]','Marie').replace('[Nom]','Dupont').replace('[Propriétaire]','{{ auth()->user()->name }}');
    } else {
        box.style.display = 'none';
    }
    updateCharCount();
}

function updateCharCount() {
    const len = document.getElementById('messageTextarea').value.length;
    document.getElementById('charCount').textContent = `${len} caractère(s)`;
}

// ── Preset depuis boutons extérieurs ─────────────────────────────────────
function presetCompose(type, sujet) {
    setTimeout(() => {
        applyTemplate(type);
        if (sujet) document.getElementById('sujetInput').value = sujet;
    }, 300);
}

// ── Init : email sélectionné par défaut ──────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    selectCanal('email', '#16A34A');
    document.querySelector('[name=canal][value=email]').checked = true;
    applyTemplate('alerte_loyer');
});
</script>
@endpush
