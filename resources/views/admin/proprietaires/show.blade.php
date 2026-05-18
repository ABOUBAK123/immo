@extends('layouts.app')
@section('title', $user->name . ' — Propriétaire')
@section('page-title', 'Fiche propriétaire')

@section('topbar-actions')
<div style="display:flex;gap:8px">
    <a href="{{ route('admin.proprietaires') }}" class="btn-ghost">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
          onsubmit="return confirm('Supprimer définitivement ce compte ?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn-ghost" style="color:#DC2626;border-color:#FECDD3">
            <i class="bi bi-trash"></i> Supprimer
        </button>
    </form>
</div>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">

    {{-- Colonne profil ───────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Identité --}}
        <div class="card-immo" style="padding:24px;text-align:center">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#DBEAFE,#BFDBFE);color:#1D4ED8;
                        display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;margin:0 auto 14px">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h2 style="font-size:1rem;font-weight:700;margin-bottom:4px">{{ $user->name }}</h2>
            <span class="badge-pill badge-info" style="margin-bottom:16px">Propriétaire</span>
            <div style="text-align:left;display:flex;flex-direction:column;gap:8px;margin-top:12px">
                <div style="display:flex;gap:10px;font-size:.82rem;align-items:center">
                    <i class="bi bi-envelope" style="color:#9CA3AF;width:16px;flex-shrink:0"></i>
                    <span style="word-break:break-all">{{ $user->email }}</span>
                </div>
                @if($user->phone)
                <div style="display:flex;gap:10px;font-size:.82rem;align-items:center">
                    <i class="bi bi-telephone" style="color:#9CA3AF;width:16px;flex-shrink:0"></i>
                    <span>{{ $user->phone }}</span>
                </div>
                @endif
                <div style="display:flex;gap:10px;font-size:.82rem;align-items:center">
                    <i class="bi bi-calendar3" style="color:#9CA3AF;width:16px;flex-shrink:0"></i>
                    <span>Inscrit le {{ $user->created_at->format('d/m/Y') }}</span>
                </div>
                <div style="display:flex;gap:10px;font-size:.82rem;align-items:center">
                    <i class="bi bi-clock-history" style="color:#9CA3AF;width:16px;flex-shrink:0"></i>
                    <span style="color:#9CA3AF">{{ $user->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        {{-- Devise ──────────────────────────────────── --}}
        <div class="card-immo" style="padding:16px 20px">
            <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;margin-bottom:12px">
                <i class="bi bi-currency-exchange" style="color:#F97316"></i> Devise du compte
            </div>
            @if(session('success'))
            <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;padding:8px 12px;font-size:.77rem;color:#16A34A;margin-bottom:12px">
                <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
            </div>
            @endif
            <form method="POST" action="{{ route('admin.proprietaires.devise', $user) }}">
                @csrf @method('PATCH')
                <div style="display:flex;gap:8px;align-items:center">
                    <select name="devise" style="flex:1;border:1.5px solid #E5E7EB;border-radius:8px;padding:7px 10px;font-size:.82rem;background:#fff;color:#111827;font-weight:600">
                        @foreach(\App\Models\User::DEVISES as $code => $info)
                        <option value="{{ $code }}" {{ ($user->devise ?? 'XOF') === $code ? 'selected' : '' }}>
                            {{ $info['flag'] }} {{ $code }} — {{ $info['label'] }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary-immo" style="padding:7px 14px;font-size:.8rem;white-space:nowrap">
                        <i class="bi bi-check2"></i> OK
                    </button>
                </div>
                <div style="margin-top:6px;font-size:.72rem;color:#9CA3AF">
                    Symbole actuel :
                    <strong style="color:#F97316">{{ \App\Models\User::DEVISES[$user->devise ?? 'XOF']['symbole'] ?? $user->devise }}</strong>
                    — affiché sur le dashboard du propriétaire
                </div>
            </form>
        </div>

        {{-- KPIs ─────────────────────────────────────── --}}
        <div class="card-immo" style="padding:16px 20px">
            <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;margin-bottom:14px">
                Statistiques du mois
            </div>
            <div style="display:flex;flex-direction:column;gap:12px">
                @php
                    $ds = \App\Models\User::DEVISES[$user->devise ?? 'XOF']['symbole'] ?? ($user->devise ?? 'XOF');
                    $rows = [
                        ['Loyers du mois',   number_format($stats['loyers_mois'],0,',',' ').' '.$ds,  '#2563EB', 'wallet2'],
                        ['Encaissés',        number_format($stats['loyers_payes'],0,',',' ').' '.$ds, '#16A34A', 'check-circle'],
                        ['Paiements en retard', $stats['nb_retards'].' paiement(s)',                   '#DC2626', 'exclamation-triangle'],
                        ['Biens loués',      $stats['nb_loues'].' / '.$stats['nb_biens'],              '#D97706', 'buildings'],
                        ['Interventions',    $stats['nb_interventions'].' en cours',                   '#7C3AED', 'tools'],
                    ];
                @endphp
                @foreach($rows as [$label, $val, $color, $icon])
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border-radius:8px;background:#F9FAFB">
                    <div style="display:flex;align-items:center;gap:8px;font-size:.78rem;color:#6B7280">
                        <i class="bi bi-{{ $icon }}" style="color:{{ $color }}"></i>
                        {{ $label }}
                    </div>
                    <strong style="font-size:.82rem;color:{{ $color }}">{{ $val }}</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Colonne principale ───────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:20px">

        {{-- Patrimoine --}}
        <div class="card-immo">
            <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:.88rem;font-weight:700">
                    <i class="bi bi-buildings me-2" style="color:#2563EB"></i>Patrimoine immobilier
                </span>
                <span class="badge-pill badge-gray">{{ $user->biens->count() }} bien(s)</span>
            </div>

            @forelse($user->biens as $bien)
            <div style="padding:14px 20px;display:flex;align-items:center;gap:14px;border-bottom:1px solid #F9FAFB">
                <div style="width:40px;height:40px;border-radius:10px;background:#F3F4F6;
                            display:flex;align-items:center;justify-content:center;color:#9CA3AF;flex-shrink:0">
                    <i class="bi bi-building"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $bien->titre }}
                    </div>
                    <div style="font-size:.72rem;color:#9CA3AF"><i class="bi bi-geo-alt me-1"></i>{{ $bien->adresse }}, {{ $bien->ville }}</div>
                    @if($bien->locationActive)
                    <div style="font-size:.72rem;color:#16A34A;margin-top:2px">
                        <i class="bi bi-person-fill me-1"></i>{{ $bien->locationActive->locataire->name }}
                        — {{ number_format($bien->locationActive->loyer_mensuel, 0, ',', ' ') }} {{ \App\Models\User::DEVISES[$user->devise ?? 'XOF']['symbole'] ?? $user->devise }}/mois
                    </div>
                    @endif
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
                    <span class="badge-pill {{ $bien->statut === 'loue' ? 'badge-success' : ($bien->statut === 'disponible' ? 'badge-info' : 'badge-gray') }}">
                        {{ ucfirst($bien->statut) }}
                    </span>
                    @if($bien->interventions->count())
                    <span class="badge-pill badge-warning" style="font-size:.65rem">
                        <i class="bi bi-tools"></i> {{ $bien->interventions->count() }} intervention(s)
                    </span>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding:32px;text-align:center;color:#9CA3AF;font-size:.82rem">
                Aucun bien enregistré.
            </div>
            @endforelse
        </div>

        {{-- Derniers paiements --}}
        <div class="card-immo">
            <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6">
                <span style="font-size:.88rem;font-weight:700">
                    <i class="bi bi-wallet2 me-2" style="color:#16A34A"></i>Derniers paiements
                </span>
            </div>
            @if($derniers_paiements->count())
            <table class="table-immo">
                <thead>
                    <tr>
                        <th>Locataire</th><th>Bien</th><th>Échéance</th><th>Montant</th><th>Statut</th><th>Quittance</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($derniers_paiements as $p)
                @php $retard = $p->statut === 'en_attente' && $p->date_echeance->isPast(); @endphp
                <tr>
                    <td style="font-size:.8rem;font-weight:500">{{ $p->location->locataire->name }}</td>
                    <td style="font-size:.78rem;color:#6B7280">{{ Str::limit(optional($p->location->bien)->titre ?? '—', 22) }}</td>
                    <td style="font-size:.78rem">{{ $p->date_echeance->format('d/m/Y') }}</td>
                    <td style="font-weight:700">{{ number_format($p->montant, 0, ',', ' ') }} {{ \App\Models\User::DEVISES[$user->devise ?? 'XOF']['symbole'] ?? $user->devise }}</td>
                    <td>
                        <span class="badge-pill {{ $p->statut === 'paye' ? 'badge-success' : ($retard ? 'badge-danger' : 'badge-warning') }}">
                            {{ $p->statut === 'paye' ? 'Payé' : ($retard ? 'En retard' : 'En attente') }}
                        </span>
                    </td>
                    <td style="font-size:.75rem;color:#16A34A">
                        {{ $p->quittance?->numero ?? '—' }}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <div style="padding:24px;text-align:center;color:#9CA3AF;font-size:.82rem">Aucun paiement enregistré.</div>
            @endif
        </div>

    </div>
</div>
@endsection
