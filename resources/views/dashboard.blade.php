@extends('layouts.app')
@section('title', 'Mon Bureau')
@section('page-title', 'Mon Bureau')

@section('topbar-actions')
<span style="font-size:.8rem;color:#6B7280">
    {{ now()->isoFormat('dddd D MMMM YYYY') }}
</span>
@endsection

@section('content')
@php
    $user = auth()->user();
    $devise     = $devise ?? $user->devise ?? 'XOF';
    $devSymbole = \App\Models\User::DEVISES[$devise]['symbole'] ?? $devise;
@endphp

<div style="margin-bottom:24px;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
        <h2 style="font-size:1.2rem;font-weight:700;margin:0">
            Bonjour {{ explode(' ', $user->name)[0] }} 👋
        </h2>
        <p style="color:#6B7280;font-size:.83rem;margin:4px 0 0">
            Voici le résumé de votre activité pour {{ now()->translatedFormat('F Y') }}
        </p>
    </div>
    @if($user->isProprietaire() || $user->isAdmin())
    <form method="POST" action="{{ route('profil.devise') }}" style="display:flex;align-items:center;gap:8px">
        @csrf @method('PATCH')
        <label style="font-size:.78rem;color:#6B7280;font-weight:600;white-space:nowrap">
            <i class="bi bi-currency-exchange" style="color:#F97316"></i> Devise :
        </label>
        <select name="devise" onchange="this.form.submit()"
                style="border:1.5px solid #E5E7EB;border-radius:8px;padding:5px 10px;font-size:.8rem;
                       background:#fff;cursor:pointer;outline:none;color:#111827;font-weight:600">
            @foreach(\App\Models\User::DEVISES as $code => $info)
            <option value="{{ $code }}" {{ $devise === $code ? 'selected' : '' }}>
                {{ $info['flag'] }} {{ $code }} — {{ $info['symbole'] }}
            </option>
            @endforeach
        </select>
    </form>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- PROPRIÉTAIRE / ADMIN                                                   --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
@if(isset($nb_biens))

{{-- KPIs loyers du mois --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
    {{-- Loyers du mois --}}
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#EFF6FF;color:#2563EB">
            <i class="bi bi-wallet2"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($loyers_mois, 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">Loyers du mois</div>
            <div class="stat-delta">
                <span class="badge-pill badge-info">{{ now()->translatedFormat('F') }}</span>
            </div>
        </div>
    </div>
    {{-- Encaissés --}}
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#F0FDF4;color:#16A34A">
            <i class="bi bi-check-circle"></i>
        </div>
        <div>
            <div class="stat-val" style="color:#16A34A">{{ number_format($loyers_payes, 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">Encaissés</div>
            <div class="stat-delta">
                <span class="badge-pill badge-success">
                    {{ $loyers_mois > 0 ? round($loyers_payes / $loyers_mois * 100) : 0 }}% collecté
                </span>
            </div>
        </div>
    </div>
    {{-- En retard --}}
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#FFF1F2;color:#DC2626">
            <i class="bi bi-clock-history"></i>
        </div>
        <div>
            <div class="stat-val" style="color:#DC2626">{{ number_format($loyers_retard, 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">En retard</div>
            <div class="stat-delta">
                <span class="badge-pill {{ $nb_retards > 0 ? 'badge-danger' : 'badge-success' }}">
                    {{ $nb_retards }} paiement(s)
                </span>
            </div>
        </div>
    </div>
    {{-- Taux occupation --}}
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#F5F3FF;color:#7C3AED">
            <i class="bi bi-buildings"></i>
        </div>
        <div>
            <div class="stat-val">{{ $taux_occupation }}%</div>
            <div class="stat-label">Taux d'occupation</div>
            <div class="stat-delta">
                <div class="progress-immo" style="width:80px;margin-top:6px">
                    <div class="progress-fill" style="width:{{ $taux_occupation }}%;background:#7C3AED"></div>
                </div>
            </div>
        </div>
    </div>
    {{-- Biens --}}
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#FFFBEB;color:#D97706">
            <i class="bi bi-house-door"></i>
        </div>
        <div>
            <div class="stat-val">{{ $nb_biens }}</div>
            <div class="stat-label">Biens gérés</div>
            <div class="stat-delta">
                <span class="badge-pill badge-gray">{{ $nb_locations }} loués</span>
            </div>
        </div>
    </div>
    {{-- Interventions --}}
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#FFF1F2;color:#E11D48">
            <i class="bi bi-tools"></i>
        </div>
        <div>
            <div class="stat-val">{{ $nb_interventions }}</div>
            <div class="stat-label">Interventions en cours</div>
            <div class="stat-delta">
                @if($nb_urgences > 0)
                <span class="badge-pill badge-danger">{{ $nb_urgences }} urgente(s)</span>
                @else
                <span class="badge-pill badge-success">Aucune urgence</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Alertes --}}
@if($alertes->count())
<div class="card-immo mb-4" style="padding:16px 20px">
    <div style="font-size:.8rem;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:8px">
        <i class="bi bi-exclamation-triangle-fill" style="color:#D97706"></i> Alertes
    </div>
    @foreach($alertes as $alerte)
    <div class="alert-immo alert-immo-{{ $alerte['type'] }}">
        <i class="bi bi-{{ $alerte['icon'] }} fs-5 flex-shrink-0"></i>
        <div>
            <strong>{{ $alerte['titre'] }}</strong><br>
            <span style="font-size:.77rem">{{ $alerte['message'] }}</span>
        </div>
    </div>
    @endforeach
</div>
@endif

<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:20px">

    {{-- Paiements récents --}}
    <div class="card-immo">
        <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:.88rem;font-weight:700">Paiements du mois</span>
            <a href="{{ route('paiements.index') }}" style="font-size:.78rem;color:#2563EB;text-decoration:none">Tout voir →</a>
        </div>
        @if($derniers_paiements->count())
        <table class="table-immo">
            <thead>
                <tr>
                    <th>Locataire</th><th>Bien</th><th>Montant</th><th>Statut</th><th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($derniers_paiements as $p)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:28px;height:28px;border-radius:50%;background:#DBEAFE;color:#1D4ED8;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0">
                            {{ strtoupper(substr($p->location->locataire->name, 0, 1)) }}
                        </div>
                        <span style="font-weight:500;font-size:.8rem">{{ $p->location->locataire->name }}</span>
                    </div>
                </td>
                <td style="color:#6B7280;font-size:.78rem">{{ Str::limit(optional($p->location->bien)->titre ?? '—', 20) }}</td>
                <td style="font-weight:600">{{ number_format($p->montant, 0, ',', ' ') }} {{ $devSymbole }}</td>
                <td>
                    @php
                        $isRetard = $p->statut === 'en_attente' && $p->date_echeance->isPast();
                    @endphp
                    <span class="badge-pill {{ $p->statut === 'paye' ? 'badge-success' : ($isRetard ? 'badge-danger' : 'badge-warning') }}">
                        {{ $p->statut === 'paye' ? 'Payé' : ($isRetard ? 'En retard' : 'En attente') }}
                    </span>
                </td>
                <td>
                    @if($p->statut !== 'paye')
                    <button class="btn-ghost" style="padding:4px 10px;font-size:.72rem"
                            data-bs-toggle="modal" data-bs-target="#pay{{ $p->id }}">
                        Encaisser
                    </button>
                    @elseif($p->quittance)
                    <span style="font-size:.72rem;color:#16A34A"><i class="bi bi-file-earmark-check"></i> {{ $p->quittance->numero }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @else
        <div style="padding:32px;text-align:center;color:#9CA3AF;font-size:.83rem">
            Aucun paiement ce mois.
        </div>
        @endif
    </div>

    {{-- Urgences & Activité --}}
    <div style="display:flex;flex-direction:column;gap:20px">

        @if($urgences->count())
        <div class="card-immo">
            <div style="padding:14px 18px;border-bottom:1px solid #F3F4F6;font-size:.85rem;font-weight:700;color:#DC2626">
                <i class="bi bi-exclamation-triangle me-1"></i> Urgences
            </div>
            @foreach($urgences as $u)
            <div style="padding:12px 18px;display:flex;gap:10px;align-items:flex-start;border-bottom:1px solid #F9FAFB">
                <div style="width:8px;height:8px;border-radius:50%;background:#DC2626;flex-shrink:0;margin-top:5px"></div>
                <div>
                    <div style="font-size:.8rem;font-weight:600">{{ $u->titre }}</div>
                    <div style="font-size:.72rem;color:#6B7280">{{ optional($u->bien)->titre ?? '—' }}</div>
                </div>
                <a href="{{ route('interventions.show', $u) }}" style="margin-left:auto;font-size:.72rem;color:#2563EB;white-space:nowrap">Voir →</a>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Biens récents --}}
        <div class="card-immo">
            <div style="padding:14px 18px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:.85rem;font-weight:700">Mon patrimoine</span>
                <a href="{{ route('biens.index') }}" style="font-size:.75rem;color:#2563EB;text-decoration:none">Gérer →</a>
            </div>
            @foreach($biens_recents as $b)
            <div style="padding:10px 18px;display:flex;align-items:center;gap:10px;border-bottom:1px solid #F9FAFB">
                <div style="width:36px;height:36px;border-radius:8px;background:#F3F4F6;display:flex;align-items:center;justify-content:center;color:#9CA3AF;flex-shrink:0">
                    <i class="bi bi-building"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $b->titre }}</div>
                    <div style="font-size:.7rem;color:#9CA3AF">{{ $b->ville }}</div>
                </div>
                <span class="badge-pill {{ $b->statut === 'loue' ? 'badge-success' : ($b->statut === 'disponible' ? 'badge-info' : 'badge-gray') }}">
                    {{ $b->statut }}
                </span>
            </div>
            @endforeach
            <div style="padding:12px 18px;text-align:center">
                <a href="{{ route('biens.create') }}" class="btn-ghost" style="width:100%;justify-content:center">
                    <i class="bi bi-plus"></i> Ajouter un bien
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Raccourcis actions rapides --}}
<div style="margin-top:20px;display:flex;flex-wrap:wrap;gap:10px">
    <a href="{{ route('biens.create') }}" class="btn-primary-immo"><i class="bi bi-buildings"></i> Ajouter un bien</a>
    <a href="{{ route('locataires.create') }}" class="btn-ghost"><i class="bi bi-person-plus"></i> Nouveau locataire</a>
    <a href="{{ route('locations.create') }}" class="btn-ghost"><i class="bi bi-file-earmark-plus"></i> Créer un bail</a>
    <a href="{{ route('annonces.create') }}" class="btn-ghost"><i class="bi bi-megaphone"></i> Publier une annonce</a>
</div>

{{-- Modals encaissement rapide --}}
@foreach($derniers_paiements as $p)
@if($p->statut !== 'paye')
<div class="modal fade" id="pay{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 40px rgba(0,0,0,.15)">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Encaisser le loyer</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('paiements.payer', $p) }}">
                @csrf @method('PATCH')
                <div class="modal-body pt-2">
                    <div class="mb-3 p-3 rounded-3" style="background:#F9FAFB;font-size:.82rem">
                        <strong>{{ $p->location->locataire->name }}</strong><br>
                        {{ optional($p->location->bien)->titre ?? '—' }} · {{ $p->date_echeance->format('d/m/Y') }}<br>
                        <span style="font-size:1.1rem;font-weight:700;color:#2563EB">{{ number_format($p->montant, 0, ',', ' ') }} {{ $devSymbole }}</span>
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
                        </select>
                    </div>
                    <div>
                        <label class="form-label-immo">Référence (optionnel)</label>
                        <input type="text" name="reference" class="form-control-immo" placeholder="N° virement, chèque...">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn-ghost w-100" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-primary-immo w-100" style="justify-content:center">
                        <i class="bi bi-check-circle"></i> Valider — Quittance auto.
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endif

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- LOCATAIRE                                                               --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
@if(isset($location))
@if($location)
<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;margin-bottom:20px">
    {{-- Mon logement --}}
    <div class="card-immo">
        <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;font-size:.88rem;font-weight:700">
            <i class="bi bi-house-door me-2" style="color:#2563EB"></i>Mon logement
        </div>
        <div style="padding:20px">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:4px">{{ optional($location->bien)->titre ?? '—' }}</h3>
            <p style="color:#6B7280;font-size:.83rem;margin-bottom:16px">
                <i class="bi bi-geo-alt me-1"></i>{{ $location->bien->adresse }}, {{ $location->bien->ville }}
            </p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div style="background:#F9FAFB;border-radius:8px;padding:12px;text-align:center">
                    <div style="font-size:1.2rem;font-weight:700;color:#2563EB">{{ number_format($location->montant_total, 0, ',', ' ') }} {{ $devSymbole }}</div>
                    <div style="font-size:.7rem;color:#9CA3AF">Loyer + charges/mois</div>
                </div>
                <div style="background:#F9FAFB;border-radius:8px;padding:12px;text-align:center">
                    <div style="font-size:1.2rem;font-weight:700">{{ $location->date_debut->format('m/Y') }}</div>
                    <div style="font-size:.7rem;color:#9CA3AF">Entrée dans les lieux</div>
                </div>
            </div>
            <span class="badge-pill {{ $location->statut === 'actif' ? 'badge-success' : 'badge-warning' }}">
                Bail {{ $location->statut }}
            </span>
        </div>
    </div>
    {{-- Paiements : dernier payé + prochains en attente --}}
    <div class="card-immo" style="overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;font-size:.88rem;font-weight:700">
            <i class="bi bi-wallet2 me-2" style="color:#16A34A"></i>Mes paiements
        </div>

        {{-- Dernier paiement confirmé --}}
        @if($dernier_paiement_paye ?? null)
        @php $dp = $dernier_paiement_paye; @endphp
        <div style="background:linear-gradient(135deg,#F0FDF4,#DCFCE7);border-bottom:1px solid #BBF7D0;
                    padding:12px 16px;display:flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;border-radius:8px;background:#16A34A;display:flex;align-items:center;
                        justify-content:center;color:#fff;font-size:.9rem;flex-shrink:0">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:.78rem;font-weight:700;color:#15803D">
                    Loyer {{ ucfirst($dp->date_echeance->isoFormat('MMMM YYYY')) }} — Payé ✓
                </div>
                <div style="font-size:.7rem;color:#166534">
                    {{ number_format($dp->montant, 0, ',', ' ') }} {{ $devSymbole }}
                    @if($dp->date_paiement)· {{ $dp->date_paiement->isoFormat('D MMM YYYY') }}@endif
                </div>
            </div>
            @if($dp->quittance)
            <a href="{{ route('quittances.download', $dp->quittance) }}"
               style="display:inline-flex;align-items:center;gap:4px;background:#16A34A;color:#fff;
                      padding:5px 10px;border-radius:6px;font-size:.7rem;font-weight:700;
                      text-decoration:none;white-space:nowrap;flex-shrink:0">
                <i class="bi bi-file-earmark-pdf"></i> Quittance
            </a>
            @endif
        </div>
        @endif

        {{-- Prochains paiements en attente --}}
        @if($prochains_paiements->isNotEmpty())
        <div style="padding:10px 16px 4px;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.04em">
            À venir
        </div>
        @endif
        @forelse($prochains_paiements as $p)
        <div style="padding:10px 16px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #F9FAFB">
            <div>
                <div style="font-size:.82rem;font-weight:600">{{ $p->date_echeance->isoFormat('D MMMM YYYY') }}</div>
                <div style="font-size:.7rem;color:#9CA3AF">Loyer mensuel</div>
            </div>
            <div style="text-align:right">
                <div style="font-weight:700;color:#2563EB;font-size:.88rem">{{ number_format($p->montant, 0, ',', ' ') }} {{ $devSymbole }}</div>
                <span class="badge-pill badge-warning" style="font-size:.62rem">En attente</span>
            </div>
        </div>
        @empty
        @if(!($dernier_paiement_paye ?? null))
        <div style="padding:20px;text-align:center;color:#9CA3AF;font-size:.82rem">Aucun paiement enregistré.</div>
        @endif
        @endforelse
        <div style="padding:10px 16px">
            <a href="{{ route('locataire.reglements') }}" style="font-size:.76rem;color:#2563EB;text-decoration:none;font-weight:600">Voir tout l'historique →</a>
        </div>
    </div>
</div>
{{-- Interventions locataire --}}
<div class="card-immo">
    <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:.88rem;font-weight:700"><i class="bi bi-tools me-2" style="color:#D97706"></i>Mes demandes d'intervention</span>
        <a href="{{ route('interventions.create') }}" class="btn-primary-immo" style="padding:6px 14px;font-size:.78rem">
            <i class="bi bi-plus"></i> Déclarer
        </a>
    </div>
    @forelse($mes_interventions as $i)
    <div style="padding:12px 20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid #F9FAFB">
        <span class="badge-pill {{ $i->priorite === 'urgente' ? 'badge-danger' : ($i->statut === 'termine' ? 'badge-success' : 'badge-warning') }}">
            {{ $i->statut === 'termine' ? 'Terminé' : ucfirst($i->priorite) }}
        </span>
        <div style="flex:1">
            <div style="font-size:.82rem;font-weight:500">{{ $i->titre }}</div>
            <div style="font-size:.72rem;color:#9CA3AF">{{ $i->date_demande->format('d/m/Y') }}</div>
        </div>
        <a href="{{ route('interventions.show', $i) }}" style="font-size:.75rem;color:#2563EB;text-decoration:none">Voir →</a>
    </div>
    @empty
    <div style="padding:24px;text-align:center;color:#9CA3AF;font-size:.82rem">Aucune demande en cours.</div>
    @endforelse
</div>
@else
<div class="card-immo" style="padding:40px;text-align:center">
    <i class="bi bi-house-door" style="font-size:3rem;color:#E5E7EB"></i>
    <p style="color:#9CA3AF;margin:16px 0 0;font-size:.88rem">Vous n'avez pas encore de location active.</p>
</div>
@endif
@endif

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- AGENT / ACHETEUR                                                        --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
@if(isset($annonces_recentes) && !isset($nb_biens) && !isset($location))
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px">
    @foreach($annonces_recentes as $a)
    <a href="{{ route('annonces.show', $a) }}" class="card-immo property-card" style="text-decoration:none;color:inherit">
        @if($a->photos && count($a->photos))
        <img src="{{ asset('storage/'.$a->photos[0]) }}" class="property-img" alt="">
        @else
        <div class="property-img-placeholder"><i class="bi bi-house"></i></div>
        @endif
        <div style="padding:14px 16px">
            <span class="badge-pill {{ $a->type === 'vente' ? 'badge-success' : 'badge-info' }} mb-2">{{ ucfirst($a->type) }}</span>
            <div style="font-size:.85rem;font-weight:600;margin-bottom:4px">{{ $a->titre }}</div>
            <div style="font-size:.75rem;color:#9CA3AF"><i class="bi bi-geo-alt me-1"></i>{{ $a->bien->ville }}</div>
            <div style="font-size:1rem;font-weight:700;color:#2563EB;margin-top:8px">
                {{ number_format($a->prix, 0, ',', ' ') }} {{ $devSymbole }}{{ $a->type === 'location' ? '/mois' : '' }}
            </div>
        </div>
    </a>
    @endforeach
</div>
@endif
@endsection
