@extends('layouts.app')
@section('title', 'Gestion des abonnements')
@section('page-title', 'Abonnements & Revenus')

@section('topbar-actions')
<div style="display:flex;gap:8px;align-items:center">
    <form method="GET" style="display:flex;gap:8px">
        <select name="statut" class="form-select-immo" style="width:auto" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="actif"      {{ request('statut')==='actif'       ? 'selected':'' }}>Actifs</option>
            <option value="en_attente" {{ request('statut')==='en_attente'  ? 'selected':'' }}>En attente</option>
            <option value="expire"     {{ request('statut')==='expire'      ? 'selected':'' }}>Expirés</option>
            <option value="annule"     {{ request('statut')==='annule'      ? 'selected':'' }}>Annulés</option>
        </select>
    </form>
</div>
@endsection

@section('content')

@if(session('success'))
<div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:12px 18px;margin-bottom:18px;
            display:flex;align-items:center;gap:10px;font-size:.85rem;color:#15803D;font-weight:600">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- ── KPIs ─────────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">

    <div class="card-immo stat-card" style="padding:16px 20px">
        <div class="stat-icon" style="background:#F0FDF4;color:#16A34A"><i class="bi bi-shield-check-fill"></i></div>
        <div>
            <div class="stat-val" style="color:#16A34A">{{ $stats['actifs'] }}</div>
            <div class="stat-label">Abonnements actifs</div>
        </div>
    </div>

    <div class="card-immo stat-card" style="padding:16px 20px">
        <div class="stat-icon" style="background:#FFF7ED;color:#EA580C"><i class="bi bi-cash-coin"></i></div>
        <div>
            <div class="stat-val" style="color:#EA580C">{{ number_format($stats['revenu_mensuel'], 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">Revenu ce mois</div>
        </div>
    </div>

    <div class="card-immo stat-card" style="padding:16px 20px">
        <div class="stat-icon" style="background:#FFF1F2;color:#DC2626"><i class="bi bi-person-x"></i></div>
        <div>
            <div class="stat-val" style="color:#DC2626">{{ $stats['sans_abonnement'] }}</div>
            <div class="stat-label">Propriétaires sans abonnement</div>
        </div>
    </div>

    <div class="card-immo stat-card" style="padding:16px 20px">
        <div class="stat-icon" style="background:#FFFBEB;color:#D97706"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="stat-val" style="color:#D97706">{{ $stats['en_attente'] }}</div>
            <div class="stat-label">Paiements en attente</div>
        </div>
    </div>

    <div class="card-immo stat-card" style="padding:16px 20px">
        <div class="stat-icon" style="background:#EFF6FF;color:#2563EB"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-val" style="color:#2563EB">{{ number_format($stats['revenu_total'], 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">Revenu total</div>
        </div>
    </div>

    <div class="card-immo stat-card" style="padding:16px 20px">
        <div class="stat-icon" style="background:#F5F3FF;color:#7C3AED"><i class="bi bi-tag"></i></div>
        <div>
            <div class="stat-val" style="color:#7C3AED">{{ number_format($prix, 0, ',', ' ') }} {{ $devSymbole }}</div>
            <div class="stat-label">Tarif mensuel actuel</div>
        </div>
    </div>

</div>

{{-- ── Table abonnements ────────────────────────────────────────────────── --}}
<div class="card-immo">
    @if($abonnements->count())
    <table class="table-immo">
        <thead>
            <tr>
                <th>Propriétaire</th>
                <th>N° Facture</th>
                <th>Période</th>
                <th>Montant</th>
                <th>Méthode</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($abonnements as $a)
        @php
            $actif = $a->statut === 'actif' && $a->date_fin->isFuture();
            $jours = $actif ? $a->joursRestants() : 0;
        @endphp
        <tr>
            <td>
                <div style="font-weight:600;font-size:.84rem">{{ $a->user->name }}</div>
                <div style="font-size:.72rem;color:#9CA3AF">{{ $a->user->email }}</div>
                @if($a->essai)
                <span style="font-size:.67rem;background:#DBEAFE;color:#1D4ED8;padding:1px 6px;border-radius:8px">Essai</span>
                @endif
            </td>
            <td style="font-size:.78rem;font-weight:600;color:#374151">{{ $a->invoice_number ?? '—' }}</td>
            <td style="font-size:.78rem;color:#6B7280">
                {{ $a->date_debut->format('d/m/Y') }}<br>
                <span style="color:#9CA3AF">→ {{ $a->date_fin->format('d/m/Y') }}</span>
                @if($actif && $jours <= 5)
                <div style="font-size:.68rem;color:#D97706;font-weight:600">⚠ {{ $jours }}j restant(s)</div>
                @endif
            </td>
            <td style="font-weight:700">
                @if($a->montant == 0) <span style="color:#16A34A">Gratuit</span>
                @else {{ number_format($a->montant, 0, ',', ' ') }} {{ $a->deviseSymbole() }}
                @endif
            </td>
            <td style="font-size:.78rem;color:#6B7280">
                @if($a->canal_paiement)
                {{ \App\Models\Paiement::CANAUX_MOBILE[$a->canal_paiement]['emoji'] ?? '' }}
                {{ \App\Models\Paiement::CANAUX_MOBILE[$a->canal_paiement]['label'] ?? $a->canal_paiement }}
                @else
                {{ $a->methode_paiement ?? '—' }}
                @endif
            </td>
            <td>
                @if($actif)
                <span class="badge-pill badge-success" style="font-size:.72rem">Actif</span>
                @elseif($a->statut === 'actif')
                <span class="badge-pill" style="font-size:.72rem;background:#F3F4F6;color:#6B7280">Expiré</span>
                @elseif($a->statut === 'en_attente')
                <span class="badge-pill badge-warning" style="font-size:.72rem">En attente</span>
                @else
                <span class="badge-pill" style="font-size:.72rem;background:#FFF1F2;color:#DC2626">Annulé</span>
                @endif
            </td>
            <td>
                <button class="btn-ghost" style="padding:4px 10px;font-size:.72rem"
                        data-bs-toggle="modal" data-bs-target="#modalEssai{{ $a->user->id }}">
                    <i class="bi bi-gift"></i> Offrir essai
                </button>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @if($abonnements->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #F3F4F6">{{ $abonnements->links() }}</div>
    @endif
    @else
    <div style="padding:60px;text-align:center">
        <i class="bi bi-credit-card" style="font-size:2.5rem;color:#E5E7EB"></i>
        <p style="color:#9CA3AF;margin:16px 0 0;font-size:.88rem">Aucun abonnement trouvé.</p>
    </div>
    @endif
</div>

{{-- ── Modals offrir essai ──────────────────────────────────────────────── --}}
@foreach($abonnements as $a)
<div class="modal fade" id="modalEssai{{ $a->user->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 20px 40px rgba(0,0,0,.15)">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Offrir une période d'essai</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.abonnements.offrir', $a->user) }}">
                @csrf
                <div class="modal-body">
                    <div style="font-size:.82rem;color:#6B7280;margin-bottom:14px">
                        Propriétaire : <strong>{{ $a->user->name }}</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-immo">Nombre de jours</label>
                        <select name="jours" class="form-select-immo">
                            <option value="7">7 jours</option>
                            <option value="14">14 jours</option>
                            <option value="30" selected>30 jours</option>
                            <option value="60">60 jours</option>
                            <option value="90">90 jours</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0" style="flex-direction:column;gap:8px">
                    <button type="submit" class="btn-primary-immo" style="width:100%;justify-content:center">
                        <i class="bi bi-gift"></i> Activer l'essai gratuit
                    </button>
                    <button type="button" class="btn-ghost" style="width:100%;justify-content:center" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
