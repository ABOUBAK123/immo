@extends('layouts.app')
@section('title', 'Paiements')
@section('page-title', 'Paiements')

@php $hasFilters = request()->hasAny(['statut','bien_id','residence','locataire_id','proprietaire_id','mois']); @endphp
@push('styles')
<style>
/* ── Canal card (choix paiement mobile) ── */
.canal-card {
    display:flex; flex-direction:column; align-items:center; gap:8px;
    padding:16px 12px; border:2px solid #E5E7EB; border-radius:12px;
    cursor:pointer; transition:.2s; background:#fff; text-align:center;
}
.canal-card:hover  { transform:translateY(-2px); box-shadow:0 4px 14px rgba(0,0,0,.1); }
.canal-card.active { border-width:2.5px; }
.canal-emoji  { font-size:1.9rem; line-height:1; }
.canal-label  { font-size:.76rem; font-weight:700; color:#374151; }
/* ── Status dots ── */
.status-dot { width:8px; height:8px; border-radius:50%; display:inline-block; }
/* ── Pulse animation (loading) ── */
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
.pulse { animation: pulse 1.2s ease-in-out infinite; }
</style>
@endpush

@section('topbar-actions')
@if(!in_array(auth()->user()->role, ['locataire']))
<button type="button" onclick="document.getElementById('exportModal').style.display='flex'"
        style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
               background:linear-gradient(135deg,#EA580C,#F97316);color:#fff;
               border:none;border-radius:8px;font-size:.8rem;font-weight:700;
               cursor:pointer;white-space:nowrap;box-shadow:0 2px 8px rgba(234,88,12,.25)">
    <i class="bi bi-download"></i> Exporter
</button>
@endif

<form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">

    {{-- Filtre par propriétaire (admin uniquement) --}}
    @if(auth()->user()->isAdmin() && $proprietaires->count())
    <select name="proprietaire_id" class="form-select-immo" style="width:auto;max-width:200px" onchange="this.form.submit()" title="Filtrer par propriétaire">
        <option value="">Tous les propriétaires</option>
        @foreach($proprietaires as $pr)
        <option value="{{ $pr->id }}" {{ request('proprietaire_id') == $pr->id ? 'selected' : '' }}>
            {{ Str::limit($pr->name, 26) }}
        </option>
        @endforeach
    </select>
    @endif

    {{-- Filtre par bien --}}
    @if($biens->count())
    <select name="bien_id" class="form-select-immo" style="width:auto;max-width:180px" onchange="this.form.submit()" title="Filtrer par bien">
        <option value="">Tous les biens</option>
        @foreach($biens as $b)
        <option value="{{ $b->id }}" {{ request('bien_id') == $b->id ? 'selected' : '' }}>
            {{ Str::limit($b->titre, 28) }}
        </option>
        @endforeach
    </select>
    @endif

    {{-- Filtre par résidence --}}
    @if($residences->count())
    <select name="residence" class="form-select-immo" style="width:auto;max-width:180px" onchange="this.form.submit()" title="Filtrer par résidence">
        <option value="">Toutes les résidences</option>
        @foreach($residences as $r)
        <option value="{{ $r }}" {{ request('residence') === $r ? 'selected' : '' }}>
            {{ Str::limit($r, 26) }}
        </option>
        @endforeach
    </select>
    @endif

    {{-- Filtre par locataire (admin / proprio uniquement) --}}
    @if($locataires->count())
    <select name="locataire_id" class="form-select-immo" style="width:auto;max-width:180px" onchange="this.form.submit()" title="Filtrer par locataire">
        <option value="">Tous les locataires</option>
        @foreach($locataires as $l)
        <option value="{{ $l->id }}" {{ request('locataire_id') == $l->id ? 'selected' : '' }}>
            {{ Str::limit($l->name, 26) }}
        </option>
        @endforeach
    </select>
    @endif

    {{-- Filtre statut --}}
    <select name="statut" class="form-select-immo" style="width:auto" onchange="this.form.submit()">
        <option value="">Tous les statuts</option>
        <option value="paye"       {{ request('statut')==='paye'       ? 'selected' : '' }}>Payés</option>
        <option value="en_attente" {{ request('statut')==='en_attente' ? 'selected' : '' }}>En attente</option>
        <option value="en_retard"  {{ request('statut')==='en_retard'  ? 'selected' : '' }}>En retard</option>
    </select>

    {{-- Filtre par période (mois) --}}
    <input type="month" name="mois" value="{{ request('mois') }}"
           class="form-control-immo" style="width:auto"
           onchange="this.form.submit()" title="Filtrer par mois">

    {{-- Bouton reset --}}
    @if($hasFilters)
    <a href="{{ route('paiements.index') }}"
       style="display:inline-flex;align-items:center;gap:5px;padding:7px 12px;
              background:#F3F4F6;border:1px solid #E5E7EB;border-radius:8px;
              font-size:.78rem;font-weight:600;color:#6B7280;text-decoration:none;white-space:nowrap"
       title="Réinitialiser les filtres">
        <i class="bi bi-x-circle"></i> Réinitialiser
    </a>
    @endif

</form>
@endsection

@section('content')
@php
    $user          = auth()->user();
    $isLocataire   = $user->role === 'locataire';
    $total_paye    = $paiements->where('statut', 'paye')->sum('montant');
    $total_attente = $paiements->where('statut', 'en_attente')->sum('montant');
    $total_retard  = $paiements->filter(fn($p) => $p->statut === 'en_attente' && $p->date_echeance->isPast())->sum('montant');
    $devSymbole    = \App\Models\User::DEVISES[$user->devise ?? 'XOF']['symbole'] ?? ($user->devise ?? 'XOF');
    $moisCourantY  = now()->year;
    $moisCourantM  = now()->month;
@endphp

{{-- ── Bannière dernier paiement confirmé (locataire vue propre OU admin filtré par locataire) --}}
@if($dernierPaiementPaye)
<div style="background:linear-gradient(135deg,#F0FDF4,#DCFCE7);border:1px solid #BBF7D0;border-radius:14px;
            padding:14px 20px;margin-bottom:16px;display:flex;align-items:center;gap:14px;flex-wrap:wrap">
    <div style="width:40px;height:40px;border-radius:10px;background:#16A34A;display:flex;align-items:center;
                justify-content:center;color:#fff;font-size:1.2rem;flex-shrink:0">
        <i class="bi bi-check-circle-fill"></i>
    </div>
    <div style="flex:1;min-width:180px">
        <div style="font-size:.85rem;font-weight:700;color:#15803D">
            @if(!$isLocataire && $dernierPaiementPaye->location->locataire ?? null)
            {{ $dernierPaiementPaye->location->locataire->name }} —
            @endif
            Loyer {{ ucfirst($dernierPaiementPaye->date_echeance->isoFormat('MMMM YYYY')) }} — Payé ✓
        </div>
        <div style="font-size:.76rem;color:#166534;margin-top:2px">
            {{ number_format($dernierPaiementPaye->montant, 0, ',', ' ') }} {{ $devSymbole }}
            @if($dernierPaiementPaye->date_paiement)
            · réglé le {{ $dernierPaiementPaye->date_paiement->isoFormat('D MMMM YYYY') }}
            @endif
        </div>
    </div>
    @if($dernierPaiementPaye->quittance)
    <a href="{{ route('quittances.download', $dernierPaiementPaye->quittance) }}"
       style="display:inline-flex;align-items:center;gap:6px;background:#16A34A;color:#fff;
              padding:8px 16px;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none;white-space:nowrap">
        <i class="bi bi-file-earmark-pdf"></i> Télécharger la quittance
    </a>
    @endif
</div>
@endif

{{-- ── Bannière "Payer maintenant" pour locataire avec retards ─────────── --}}
@if($isLocataire && $total_retard > 0)
<div style="background:linear-gradient(135deg,#FFF1F2,#FFE4E6);border:1px solid #FECDD3;border-radius:14px;
            padding:18px 22px;margin-bottom:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="width:44px;height:44px;border-radius:12px;background:#DC2626;display:flex;align-items:center;
                justify-content:center;color:#fff;font-size:1.3rem;flex-shrink:0">
        <i class="bi bi-exclamation-triangle-fill"></i>
    </div>
    <div style="flex:1;min-width:200px">
        <div style="font-size:.9rem;font-weight:700;color:#9F1239">Loyer(s) en retard</div>
        <div style="font-size:.8rem;color:#BE123C;margin-top:2px">
            {{ number_format($total_retard, 0, ',', ' ') }} {{ $devSymbole }} à régulariser
        </div>
    </div>
    <a href="#loyers-en-attente" style="background:#DC2626;color:#fff;padding:9px 18px;border-radius:8px;
                                        font-size:.82rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
        <i class="bi bi-phone-fill"></i> Payer maintenant
    </a>
</div>
@endif

{{-- ── KPIs ─────────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
    <div class="card-immo stat-card" style="padding:16px 20px">
        <div class="stat-icon" style="background:#F0FDF4;color:#16A34A"><i class="bi bi-check-circle"></i></div>
        <div>
            <div class="stat-val" style="color:#16A34A;font-size:1.2rem">{{ number_format($total_paye, 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">{{ $isLocataire ? 'Total payé' : 'Encaissé (page)' }}</div>
        </div>
    </div>
    <div class="card-immo stat-card" style="padding:16px 20px">
        <div class="stat-icon" style="background:#FFFBEB;color:#D97706"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="stat-val" style="color:#D97706;font-size:1.2rem">{{ number_format($total_attente, 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">En attente</div>
        </div>
    </div>
    <div class="card-immo stat-card" style="padding:16px 20px">
        <div class="stat-icon" style="background:#FFF1F2;color:#DC2626"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <div class="stat-val" style="color:#DC2626;font-size:1.2rem">{{ number_format($total_retard, 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">En retard</div>
        </div>
    </div>
</div>

{{-- ── Table paiements ──────────────────────────────────────────────────── --}}
<div class="card-immo" id="loyers-en-attente">
    @if($paiements->count())
    <table class="table-immo">
        <thead>
            <tr>
                <th>{{ $isLocataire ? 'Bien' : 'Bien / Locataire' }}</th>
                <th>Échéance</th>
                <th>Montant</th>
                @if(!$isLocataire)
                <th title="Total des coûts d'interventions sur ce bien pour le mois en cours">
                    Interv. du mois
                </th>
                @endif
                <th>Statut</th>
                <th>Date paiement</th>
                <th>Quittance</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($paiements as $p)
        @php
            $enRetard      = $p->statut === 'en_attente' && $p->date_echeance->isPast();
            $estMoisCourant = $p->date_echeance->year === $moisCourantY
                           && $p->date_echeance->month === $moisCourantM;
            $rowBg = $enRetard ? 'background:#FFF1F2' : ($estMoisCourant && $isLocataire ? 'background:#F0FDF4' : '');
        @endphp
        <tr style="{{ $rowBg }}">
            <td>
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="font-weight:600;font-size:.83rem">{{ Str::limit(optional($p->location->bien)->titre ?? '—', 24) }}</div>
                    @if($estMoisCourant && $isLocataire)
                    <span style="font-size:.62rem;font-weight:700;background:#DCFCE7;color:#15803D;
                                 border:1px solid #BBF7D0;border-radius:4px;padding:1px 5px;white-space:nowrap">
                        Mois en cours
                    </span>
                    @endif
                </div>
                @if(!$isLocataire)
                <div style="font-size:.72rem;color:#9CA3AF">{{ $p->location->locataire->name }}</div>
                @endif
            </td>
            <td style="font-size:.82rem">
                {{ $p->date_echeance->format('d/m/Y') }}
                @if($enRetard)
                <div style="font-size:.68rem;color:#DC2626">{{ $p->date_echeance->diffForHumans() }}</div>
                @endif
            </td>
            <td style="font-weight:700">{{ number_format($p->montant, 0, ',', ' ') }} {{ $devSymbole }}</td>
            @if(!$isLocataire)
            @php $coutInterv = $interventionsMois[$p->location->bien_id] ?? 0; @endphp
            <td style="font-size:.82rem;white-space:nowrap">
                @if($coutInterv > 0)
                <span style="display:inline-flex;align-items:center;gap:4px;
                             background:#FFF7ED;border:1px solid #FDBA74;
                             border-radius:6px;padding:3px 8px;
                             color:#C2410C;font-weight:700">
                    <i class="bi bi-tools" style="font-size:.72rem"></i>
                    {{ number_format($coutInterv, 0, ',', ' ') }} {{ $devSymbole }}
                </span>
                @else
                <span style="color:#D1D5DB">—</span>
                @endif
            </td>
            @endif
            <td>
                @if($p->statut === 'paye')
                <span class="badge-pill badge-success">
                    <span class="status-dot" style="background:#16A34A"></span> Payé
                    @if($p->canal_paiement && $p->methode_paiement === 'mobile_money')
                    <span style="margin-left:4px">{{ \App\Models\Paiement::CANAUX_MOBILE[$p->canal_paiement]['emoji'] ?? '' }}</span>
                    @endif
                </span>
                @elseif($enRetard)
                <span class="badge-pill badge-danger"><span class="status-dot" style="background:#DC2626"></span> En retard</span>
                @else
                <span class="badge-pill badge-warning"><span class="status-dot" style="background:#D97706"></span> En attente</span>
                @endif
            </td>
            <td style="font-size:.8rem;color:#6B7280">
                @if($p->date_paiement)
                <div>{{ $p->date_paiement->format('d/m/Y') }}</div>
                @if($p->methode_paiement)
                <div style="font-size:.7rem;color:#9CA3AF">
                    {{ $p->methode_paiement === 'mobile_money' ? ucfirst(str_replace('_', ' ', $p->canal_paiement ?? 'Mobile')) : ucfirst($p->methode_paiement) }}
                </div>
                @endif
                @else
                <span style="color:#D1D5DB">—</span>
                @endif
            </td>
            <td>
                @if($p->quittance)
                <div style="display:flex;flex-direction:column;gap:4px">
                    <span style="font-size:.7rem;font-weight:700;color:#6B7280;font-family:monospace">{{ $p->quittance->numero }}</span>
                    <div style="display:flex;gap:4px">
                        <a href="{{ route('quittances.pdf', $p->quittance) }}" target="_blank"
                           style="display:inline-flex;align-items:center;gap:4px;font-size:.72rem;
                                  color:#2563EB;font-weight:600;text-decoration:none;
                                  background:#EFF6FF;border:1px solid #BFDBFE;border-radius:5px;
                                  padding:3px 8px;white-space:nowrap"
                           title="Aperçu HTML">
                            <i class="bi bi-eye"></i> Voir
                        </a>
                        <a href="{{ route('quittances.download', $p->quittance) }}"
                           style="display:inline-flex;align-items:center;gap:4px;font-size:.72rem;
                                  color:#16A34A;font-weight:600;text-decoration:none;
                                  background:#F0FDF4;border:1px solid #BBF7D0;border-radius:5px;
                                  padding:3px 8px;white-space:nowrap"
                           title="Télécharger le PDF">
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
                    @if($isLocataire)
                    {{-- Bouton paiement mobile pour locataire --}}
                    <button onclick="ouvrirPaiementMobile({{ $p->id }}, '{{ addslashes(optional($p->location->bien)->titre ?? '') }}', {{ $p->montant }}, '{{ $devSymbole }}')"
                            style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                                   background:linear-gradient(135deg,#EA580C,#F97316);color:#fff;
                                   border:none;border-radius:8px;font-size:.78rem;font-weight:700;
                                   cursor:pointer;white-space:nowrap;box-shadow:0 2px 8px rgba(234,88,12,.3)">
                        <i class="bi bi-phone-fill"></i> Payer maintenant
                    </button>
                    @else
                    {{-- Bouton encaisser admin/proprio --}}
                    <div style="display:flex;gap:6px;align-items:center">
                        <button class="btn-ghost" style="padding:5px 12px;font-size:.75rem"
                                data-bs-toggle="modal" data-bs-target="#pay{{ $p->id }}">
                            <i class="bi bi-check-circle"></i> Encaisser
                        </button>
                        <form method="POST" action="{{ route('paiements.relance', $p) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn-ghost"
                                    style="padding:5px 10px;font-size:.72rem;color:#D97706;border-color:#FDE68A;position:relative"
                                    title="{{ $p->nb_relances ? 'Relance IA — '.$p->nb_relances.' déjà envoyée(s)'.($p->derniere_relance_at ? ' (dernière : '.$p->derniere_relance_at->diffForHumans().')' : '') : 'Envoyer une relance IA' }}">
                                <i class="bi bi-robot"></i>
                                @if($p->nb_relances > 0)
                                <span style="position:absolute;top:-5px;right:-5px;background:#D97706;color:#fff;
                                             border-radius:50%;width:14px;height:14px;font-size:.55rem;
                                             display:flex;align-items:center;justify-content:center;font-weight:700">
                                    {{ $p->nb_relances }}
                                </span>
                                @endif
                            </button>
                        </form>
                    </div>
                    @endif
                @else
                    {{-- Payé : lien quittance déjà affiché dans la colonne Quittance --}}
                    <span style="font-size:.72rem;color:#9CA3AF">—</span>
                @endif
                @if(!$isLocataire && !$p->location->bien)
                <form method="POST" action="{{ route('paiements.destroy', $p) }}"
                      onsubmit="return confirm('Supprimer ce paiement ?')" style="display:inline;margin-left:4px">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-ghost"
                            style="padding:4px 8px;font-size:.72rem;color:#DC2626;border-color:#FECDD3"
                            title="Bien supprimé — supprimer ce paiement">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($paiements->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #F3F4F6">{{ $paiements->links() }}</div>
    @endif

    @else
    <div style="padding:60px;text-align:center">
        <i class="bi bi-wallet2" style="font-size:2.5rem;color:#E5E7EB"></i>
        <p style="color:#9CA3AF;margin:16px 0 0;font-size:.88rem">Aucun paiement trouvé.</p>
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL PAIEMENT MOBILE (locataire)                                       --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@if($isLocataire)
<div class="modal fade" id="modalPaiementMobile" tabindex="-1" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content" style="border-radius:18px;border:none;box-shadow:0 24px 60px rgba(0,0,0,.18)">

            {{-- En-tête --}}
            <div style="padding:20px 22px 0;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h5 style="font-size:.95rem;font-weight:800;margin:0">Payer mon loyer</h5>
                    <div id="mpBienInfo" style="font-size:.78rem;color:#9CA3AF;margin-top:2px"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:.75rem"></button>
            </div>

            <div style="padding:20px 22px">

                {{-- Montant à payer --}}
                <div style="background:linear-gradient(135deg,#FFEDD5,#FEF3C7);border-radius:12px;padding:14px 16px;
                            margin-bottom:20px;display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:.82rem;color:#92400E;font-weight:600">Montant à payer</span>
                    <span id="mpMontant" style="font-size:1.4rem;font-weight:800;color:#C2410C"></span>
                </div>

                {{-- Choix canal --}}
                <div style="font-size:.78rem;font-weight:700;color:#374151;margin-bottom:10px">
                    Choisissez votre moyen de paiement
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px" id="canalGrid">
                    @foreach(\App\Models\Paiement::CANAUX_MOBILE as $code => $info)
                    <div class="canal-card" data-canal="{{ $code }}"
                         style="border-color:#E5E7EB"
                         onclick="selectCanal('{{ $code }}', '{{ $info['color'] }}', '{{ $info['bg'] }}')">
                        <div class="canal-emoji">{{ $info['emoji'] }}</div>
                        <div class="canal-label">{{ $info['label'] }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Numéro téléphone (mobile money uniquement) --}}
                <div id="phoneField" style="display:none;margin-bottom:16px">
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:6px">
                        <i class="bi bi-phone me-1"></i>Numéro de téléphone
                    </label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <span style="background:#F3F4F6;border:1px solid #E5E7EB;border-radius:8px;padding:9px 12px;font-size:.82rem;color:#374151;font-weight:600;white-space:nowrap">
                            +225
                        </span>
                        <input type="tel" id="mpTelephone" name="telephone"
                               placeholder="07 XX XX XX XX"
                               style="flex:1;border:1px solid #E5E7EB;border-radius:8px;padding:9px 12px;font-size:.83rem;outline:none;font-family:inherit"
                               onfocus="this.style.borderColor='#EA580C'" onblur="this.style.borderColor='#E5E7EB'">
                    </div>
                </div>

                {{-- Statut / message --}}
                <div id="mpMessage" style="display:none;margin-bottom:14px;font-size:.8rem;border-radius:8px;padding:10px 12px"></div>

                {{-- Bouton payer --}}
                <button id="mpBtn" onclick="lancerPaiement()"
                        disabled
                        style="width:100%;padding:12px;border:none;border-radius:10px;font-size:.9rem;font-weight:700;
                               background:#E5E7EB;color:#9CA3AF;cursor:not-allowed;transition:.2s;display:flex;align-items:center;justify-content:center;gap:8px">
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
@endif

{{-- ── Modals encaissement (admin/proprio) ────────────────────────────── --}}
@foreach($paiements as $p)
@if($p->statut !== 'paye' && !$isLocataire)
<div class="modal fade" id="pay{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 20px 40px rgba(0,0,0,.15)">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Encaisser le loyer</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('paiements.payer', $p) }}">
                @csrf @method('PATCH')
                <div class="modal-body pt-2">
                    <div style="background:#F9FAFB;border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:.82rem">
                        <strong>{{ $p->location->locataire->name }}</strong><br>
                        {{ optional($p->location->bien)->titre ?? '—' }}<br>
                        <span style="color:#9CA3AF">{{ $p->date_echeance->format('d/m/Y') }}</span>
                        <span style="font-size:1.1rem;font-weight:800;color:#2563EB;display:block;margin-top:4px">
                            {{ number_format($p->montant, 0, ',', ' ') }} {{ $devSymbole }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-immo">Date de réception</label>
                        <input type="date" name="date_paiement" class="form-control-immo" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-immo">Méthode</label>
                        <select name="methode_paiement" class="form-select-immo" required>
                            <option value="virement">Virement bancaire</option>
                            <option value="cheque">Chèque</option>
                            <option value="especes">Espèces</option>
                            <option value="prelevement">Prélèvement auto.</option>
                            <option value="cb">Carte bancaire</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-immo">Référence (optionnel)</label>
                        <input type="text" name="reference" class="form-control-immo" placeholder="N° virement...">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0" style="flex-direction:column;gap:8px">
                    <button type="submit" class="btn-primary-immo" style="width:100%;justify-content:center">
                        <i class="bi bi-check-circle"></i> Valider — Quittance auto.
                    </button>
                    <button type="button" class="btn-ghost" style="width:100%;justify-content:center" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL EXPORT                                                            --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@if(!$isLocataire)
<div id="exportModal"
     style="display:none;position:fixed;inset:0;z-index:9999;
            background:rgba(0,0,0,.45);align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:460px;
                box-shadow:0 24px 60px rgba(0,0,0,.2);overflow:hidden">

        {{-- En-tête --}}
        <div style="background:linear-gradient(135deg,#EA580C,#F97316);padding:18px 22px;
                    display:flex;justify-content:space-between;align-items:center">
            <div>
                <div style="font-size:.95rem;font-weight:800;color:#fff">
                    <i class="bi bi-download me-2"></i>Exporter les paiements
                </div>
                <div style="font-size:.75rem;color:rgba(255,255,255,.8);margin-top:2px">
                    PDF ou Excel avec récapitulatif et signatures
                </div>
            </div>
            <button onclick="document.getElementById('exportModal').style.display='none'"
                    style="background:rgba(255,255,255,.2);border:none;border-radius:8px;
                           width:30px;height:30px;color:#fff;cursor:pointer;font-size:1rem">
                ×
            </button>
        </div>

        <div style="padding:22px">

            {{-- Sélection période --}}
            <div style="font-size:.82rem;font-weight:700;color:#374151;margin-bottom:10px">
                <i class="bi bi-calendar3 me-1" style="color:#EA580C"></i>Période
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:20px" id="periodeGrid">
                @foreach([
                    ['mois',      'Mois courant',  'bi-calendar-date'],
                    ['2mois',     '2 mois',        'bi-calendar2'],
                    ['trimestre', 'Trimestre',     'bi-calendar-range'],
                    ['semestre',  'Semestre',      'bi-calendar3'],
                    ['annuel',    'Annuel',        'bi-calendar-check'],
                ] as [$val, $lbl, $icon])
                <label style="display:flex;align-items:center;gap:8px;padding:10px 12px;
                              border:2px solid #E5E7EB;border-radius:10px;cursor:pointer;
                              font-size:.82rem;font-weight:600;color:#374151;transition:.15s"
                       onclick="selectPeriode('{{ $val }}', this)">
                    <input type="radio" name="periode_radio" value="{{ $val }}"
                           style="display:none" {{ $val === 'mois' ? 'checked' : '' }}>
                    <i class="bi {{ $icon }}" style="color:#EA580C;font-size:1rem"></i>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>

            {{-- Sélection format --}}
            <div style="font-size:.82rem;font-weight:700;color:#374151;margin-bottom:10px">
                <i class="bi bi-file-earmark me-1" style="color:#EA580C"></i>Format
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:22px">
                <button type="button" onclick="lancerExport('pdf')" id="btnPdf"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;
                               padding:12px;border:2px solid #FECDD3;border-radius:10px;
                               background:#FFF1F2;color:#DC2626;font-size:.85rem;font-weight:700;
                               cursor:pointer;transition:.15s;font-family:inherit">
                    <i class="bi bi-file-earmark-pdf-fill" style="font-size:1.2rem"></i>
                    <div>
                        <div>PDF</div>
                        <div style="font-size:.68rem;font-weight:400;color:#9CA3AF">Mise en page A4</div>
                    </div>
                </button>
                <button type="button" onclick="lancerExport('excel')" id="btnExcel"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;
                               padding:12px;border:2px solid #BBF7D0;border-radius:10px;
                               background:#F0FDF4;color:#15803D;font-size:.85rem;font-weight:700;
                               cursor:pointer;transition:.15s;font-family:inherit">
                    <i class="bi bi-file-earmark-excel-fill" style="font-size:1.2rem"></i>
                    <div>
                        <div>Excel</div>
                        <div style="font-size:.68rem;font-weight:400;color:#9CA3AF">Format .xls</div>
                    </div>
                </button>
            </div>

            <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:8px;
                        padding:10px 12px;font-size:.74rem;color:#92400E">
                <i class="bi bi-info-circle me-1"></i>
                Le document inclut le récapitulatif (recouvrement − interventions − frais agence) et les zones de signature.
            </div>
        </div>
    </div>
</div>

<script>
let periodeSelectionnee = 'mois';

function selectPeriode(val, el) {
    periodeSelectionnee = val;
    document.querySelectorAll('#periodeGrid label').forEach(l => {
        l.style.borderColor = '#E5E7EB';
        l.style.background  = '#fff';
        l.style.color       = '#374151';
    });
    el.style.borderColor = '#EA580C';
    el.style.background  = '#FFF7ED';
    el.style.color       = '#C2410C';
}
// Activer le mois par défaut
document.addEventListener('DOMContentLoaded', function() {
    const first = document.querySelector('#periodeGrid label');
    if (first) selectPeriode('mois', first);
});

function lancerExport(format) {
    const url = '{{ route("paiements.export") }}?periode=' + periodeSelectionnee + '&format=' + format;
    window.location.href = url;
    document.getElementById('exportModal').style.display = 'none';
}
</script>
@endif

@push('scripts')
@if($isLocataire)
<script>
let currentPaiementId = null;
let canalSelectionne   = null;
const canalsMobiles    = ['orange_money', 'mtn_money', 'wave'];

function ouvrirPaiementMobile(id, titre, montant, symbole) {
    currentPaiementId = id;
    canalSelectionne  = null;

    document.getElementById('mpBienInfo').textContent = titre;
    document.getElementById('mpMontant').textContent  = parseInt(montant).toLocaleString('fr-FR') + ' ' + symbole;
    document.getElementById('mpMessage').style.display = 'none';
    document.getElementById('phoneField').style.display = 'none';
    document.getElementById('mpTelephone').value = '';

    // Reset canal cards
    document.querySelectorAll('.canal-card').forEach(c => {
        c.classList.remove('active');
        c.style.borderColor = '#E5E7EB';
        c.style.background  = '#fff';
    });

    // Reset bouton
    const btn = document.getElementById('mpBtn');
    btn.disabled = true;
    btn.style.background  = '#E5E7EB';
    btn.style.color       = '#9CA3AF';
    btn.style.cursor      = 'not-allowed';
    btn.innerHTML = '<i class="bi bi-lock-fill"></i> Choisissez un moyen de paiement';

    const modal = new bootstrap.Modal(document.getElementById('modalPaiementMobile'));
    modal.show();
}

function selectCanal(code, color, bg) {
    canalSelectionne = code;

    // Mise à jour visuelle des cartes
    document.querySelectorAll('.canal-card').forEach(c => {
        const isSel = c.dataset.canal === code;
        c.classList.toggle('active', isSel);
        c.style.borderColor = isSel ? color : '#E5E7EB';
        c.style.background  = isSel ? bg    : '#fff';
    });

    // Afficher le champ téléphone pour mobile money
    const isMobile = canalsMobiles.includes(code);
    document.getElementById('phoneField').style.display = isMobile ? '' : 'none';

    // Activer le bouton
    const btn = document.getElementById('mpBtn');
    btn.disabled = false;
    btn.style.background  = color;
    btn.style.color       = '#fff';
    btn.style.cursor      = 'pointer';
    btn.innerHTML = '<i class="bi bi-send-fill"></i> Confirmer le paiement';
}

async function lancerPaiement() {
    if (!canalSelectionne || !currentPaiementId) return;

    const btn = document.getElementById('mpBtn');
    const msg = document.getElementById('mpMessage');

    btn.disabled  = true;
    btn.innerHTML = '<span class="pulse"><i class="bi bi-hourglass-split"></i> Traitement en cours…</span>';

    msg.style.display = 'none';

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('canal',  canalSelectionne);
    const tel = document.getElementById('mpTelephone').value;
    if (tel) formData.append('telephone', tel);

    try {
        const res = await fetch(`{{ url('/paiements') }}/${currentPaiementId}/mobile`, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        let data;
        try { data = await res.json(); } catch (_) { data = {}; }

        if (!res.ok) {
            throw new Error(data.message || (res.status === 403 ? 'Accès refusé. Actualisez la page et réessayez.' : `Erreur ${res.status}`));
        }

        if (data.ok) {
            if (data.url) {
                // Redirection vers la passerelle de paiement
                window.location.href = data.url;
            } else {
                // Simulation ou succès immédiat
                btn.style.background = '#16A34A';
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Paiement validé';

                let quittanceLink = '';
                if (data.quittance_id) {
                    quittanceLink = `<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
                        <a href="/quittances/${data.quittance_id}" target="_blank"
                           style="display:inline-flex;align-items:center;gap:6px;background:#2563EB;color:#fff;
                                  padding:7px 14px;border-radius:8px;font-size:.8rem;font-weight:700;text-decoration:none">
                            <i class="bi bi-eye"></i> Voir la quittance
                        </a>
                        <a href="/quittances/${data.quittance_id}/telecharger"
                           style="display:inline-flex;align-items:center;gap:6px;background:#16A34A;color:#fff;
                                  padding:7px 14px;border-radius:8px;font-size:.8rem;font-weight:700;text-decoration:none">
                            <i class="bi bi-file-earmark-pdf"></i> Télécharger PDF
                        </a>
                    </div>`;
                }
                msg.style.cssText = 'display:block;background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D';
                msg.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i><strong>Paiement confirmé !</strong> Votre quittance est générée.' + quittanceLink;

                setTimeout(() => window.location.reload(), 4000);
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
@endif
@endpush

@endsection
