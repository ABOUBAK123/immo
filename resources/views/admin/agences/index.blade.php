@extends('layouts.app')
@section('title', 'Agences Immobilières')
@section('page-title', 'Agences Immobilières')

@section('topbar-actions')
<div style="display:flex;gap:8px;align-items:center">
    <form method="GET" style="display:flex;gap:8px;align-items:center">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un agent…"
               style="border:1px solid #E5E7EB;border-radius:8px;padding:7px 12px;font-size:.8rem;width:200px">
        <select name="statut" onchange="this.form.submit()"
                style="border:1px solid #E5E7EB;border-radius:8px;padding:7px 12px;font-size:.8rem;background:#fff">
            <option value="">Tous les statuts</option>
            <option value="actif"   {{ request('statut') === 'actif'   ? 'selected' : '' }}>Actifs</option>
            <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactifs</option>
        </select>
    </form>
</div>
@endsection

@section('content')

{{-- KPIs --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    @php
    $kpis = [
        ['Agents total',    $stats['total'],    '#2563EB','#EFF6FF','person-badge'],
        ['Actifs',          $stats['actifs'],   '#16A34A','#F0FDF4','check-circle'],
        ['Inactifs',        $stats['inactifs'], '#DC2626','#FEF2F2','x-circle'],
        ['Nouveaux ce mois',$stats['ce_mois'],  '#F97316','#FFF7ED','person-plus'],
    ];
    @endphp
    @foreach($kpis as [$label,$val,$color,$bg,$icon])
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:{{ $bg }};color:{{ $color }}">
            <i class="bi bi-{{ $icon }}"></i>
        </div>
        <div>
            <div class="stat-val" style="color:{{ $color }}">{{ $val }}</div>
            <div class="stat-label">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Table agents --}}
<div class="card-immo">
    <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:.88rem;font-weight:700">
            <i class="bi bi-buildings me-2" style="color:#F97316"></i>Liste des agents immobiliers
        </span>
        <span class="badge-pill badge-gray">{{ $agents->total() }} agent(s)</span>
    </div>

    @if($agents->count())
    <table class="table-immo">
        <thead>
            <tr>
                <th>Agent</th>
                <th>Contact</th>
                <th>Annonces</th>
                <th>Vues totales</th>
                <th>Inscrit le</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($agents as $agent)
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:36px;height:36px;border-radius:50%;background:{{ $agent->statut === 'actif' ? '#FFEDD5' : '#F3F4F6' }};
                                color:{{ $agent->statut === 'actif' ? '#EA580C' : '#9CA3AF' }};
                                display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:.83rem;font-weight:600">{{ $agent->name }}</div>
                        <div style="font-size:.7rem;color:#9CA3AF">Agent immobilier</div>
                    </div>
                </div>
            </td>
            <td>
                <div style="font-size:.78rem">{{ $agent->email }}</div>
                @if($agent->phone)
                <div style="font-size:.72rem;color:#9CA3AF">{{ $agent->phone }}</div>
                @endif
            </td>
            <td>
                <div style="font-size:.88rem;font-weight:700;color:#2563EB">{{ $agent->nb_annonces ?? 0 }}</div>
                <div style="font-size:.7rem;color:#9CA3AF">annonce(s)</div>
            </td>
            <td>
                <div style="font-size:.85rem;font-weight:600">{{ number_format($agent->total_vues ?? 0, 0, ',', ' ') }}</div>
                <div style="font-size:.7rem;color:#9CA3AF">vue(s)</div>
            </td>
            <td style="font-size:.78rem;color:#6B7280">{{ $agent->created_at->format('d/m/Y') }}</td>
            <td>
                <span class="badge-pill {{ $agent->statut === 'actif' ? 'badge-success' : 'badge-danger' }}">
                    <i class="bi bi-{{ $agent->statut === 'actif' ? 'check-circle' : 'x-circle' }}-fill"></i>
                    {{ ucfirst($agent->statut) }}
                </span>
            </td>
            <td>
                <div style="display:flex;gap:6px;align-items:center">
                    <a href="{{ route('admin.agences.show', $agent) }}"
                       style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border:1px solid #E5E7EB;
                              border-radius:7px;font-size:.75rem;color:#374151;text-decoration:none;background:#fff">
                        <i class="bi bi-eye"></i> Voir
                    </a>
                    <form method="POST" action="{{ route('admin.agences.toggle', $agent) }}"
                          onsubmit="return confirm('{{ $agent->statut === 'actif' ? 'Désactiver' : 'Activer' }} ce compte ?')">
                        @csrf @method('PATCH')
                        <button type="submit"
                                style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;
                                       border:1px solid {{ $agent->statut === 'actif' ? '#FECDD3' : '#BBF7D0' }};
                                       border-radius:7px;font-size:.75rem;
                                       color:{{ $agent->statut === 'actif' ? '#DC2626' : '#16A34A' }};
                                       background:{{ $agent->statut === 'actif' ? '#FFF1F2' : '#F0FDF4' }};cursor:pointer">
                            <i class="bi bi-{{ $agent->statut === 'actif' ? 'pause-circle' : 'play-circle' }}"></i>
                            {{ $agent->statut === 'actif' ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div style="padding:16px 20px">
        {{ $agents->links() }}
    </div>
    @else
    <div style="padding:48px;text-align:center;color:#9CA3AF">
        <i class="bi bi-person-badge" style="font-size:3rem;display:block;margin-bottom:12px"></i>
        <div style="font-size:.88rem">Aucun agent immobilier enregistré.</div>
        <div style="font-size:.78rem;margin-top:4px">Les comptes avec le rôle <strong>agent</strong> apparaîtront ici.</div>
    </div>
    @endif
</div>

@endsection
