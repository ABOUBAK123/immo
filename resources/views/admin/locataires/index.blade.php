@extends('layouts.app')
@section('title', 'Gestion Locataires')
@section('page-title', 'Locataires inscrits')

@section('topbar-actions')
<div style="display:flex;gap:8px;align-items:center">
    <form method="GET" style="display:flex;gap:8px">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Rechercher…" class="form-control-immo" style="width:200px">
        <select name="statut" class="form-select-immo" style="width:auto" onchange="this.form.submit()">
            <option value="">Tous</option>
            <option value="actif"     {{ request('statut')==='actif'     ? 'selected' : '' }}>Avec bail actif</option>
            <option value="sans_bail" {{ request('statut')==='sans_bail' ? 'selected' : '' }}>Sans bail</option>
        </select>
        <button type="submit" class="btn-ghost"><i class="bi bi-search"></i></button>
        @if(request('q') || request('statut'))
        <a href="{{ route('admin.locataires') }}" class="btn-ghost">✕</a>
        @endif
    </form>
</div>
@endsection

@section('content')

{{-- KPIs ─────────────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#EFF6FF;color:#2563EB">
            <i class="bi bi-people"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['total'] }}</div>
            <div class="stat-label">Locataires</div>
        </div>
    </div>
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#F0FDF4;color:#16A34A">
            <i class="bi bi-house-check"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['actifs'] }}</div>
            <div class="stat-label">Avec bail actif</div>
        </div>
    </div>
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#FFF1F2;color:#DC2626">
            <i class="bi bi-house-x"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['sans_bail'] }}</div>
            <div class="stat-label">Sans bail actif</div>
        </div>
    </div>
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#F5F3FF;color:#7C3AED">
            <i class="bi bi-person-plus"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['ce_mois'] }}</div>
            <div class="stat-label">Inscrits ce mois</div>
        </div>
    </div>
</div>

{{-- Tableau ──────────────────────────────────────────────────────────────── --}}
<div class="card-immo">
    <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:.88rem;font-weight:700">
            <i class="bi bi-people me-2" style="color:#2563EB"></i>Liste des locataires
        </span>
        <span style="font-size:.78rem;color:#9CA3AF">{{ $locataires->total() }} compte(s)</span>
    </div>

    @if($locataires->count())
    <table class="table-immo">
        <thead>
            <tr>
                <th>Locataire</th>
                <th>Contact</th>
                <th>Logement actuel</th>
                <th>Loyer</th>
                <th>Baux</th>
                <th>Inscrit le</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($locataires as $loc)
        @php $locationActive = $loc->locations->first(); @endphp
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:11px">
                    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#F3E8FF,#EDE9FE);color:#7C3AED;
                                display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($loc->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem">{{ $loc->name }}</div>
                        <div style="font-size:.7rem;color:#9CA3AF">ID #{{ $loc->id }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div style="font-size:.82rem">{{ $loc->email }}</div>
                @if($loc->phone)
                <div style="font-size:.72rem;color:#9CA3AF"><i class="bi bi-telephone me-1"></i>{{ $loc->phone }}</div>
                @endif
            </td>
            <td>
                @if($locationActive)
                <div style="font-size:.82rem;font-weight:500">{{ Str::limit(optional($locationActive->bien)->titre ?? '—', 26) }}</div>
                <div style="font-size:.7rem;color:#9CA3AF"><i class="bi bi-geo-alt me-1"></i>{{ optional($locationActive->bien)->ville ?? '—' }}</div>
                @else
                <span style="color:#D1D5DB;font-size:.8rem">— Aucun logement</span>
                @endif
            </td>
            <td>
                @if($locationActive)
                <div style="font-weight:700;font-size:.9rem;color:#2563EB">
                    {{ number_format($locationActive->loyer_mensuel + $locationActive->charges, 0, ',', ' ') }} €
                    <span style="font-weight:400;font-size:.7rem;color:#9CA3AF">/mois</span>
                </div>
                @else
                <span style="color:#D1D5DB;font-size:.8rem">—</span>
                @endif
            </td>
            <td>
                @if($locationActive)
                <span class="badge-pill badge-success">Actif</span>
                @else
                <span class="badge-pill badge-gray">Sans bail</span>
                @endif
                <div style="font-size:.7rem;color:#9CA3AF;margin-top:3px">{{ $loc->locations_count }} bail(s) au total</div>
            </td>
            <td style="font-size:.8rem;color:#6B7280">
                {{ $loc->created_at->format('d/m/Y') }}
                <div style="font-size:.7rem;color:#9CA3AF">{{ $loc->created_at->diffForHumans() }}</div>
            </td>
            <td>
                <div style="display:flex;gap:6px;justify-content:flex-end">
                    <a href="{{ route('admin.locataires.show', $loc) }}" class="btn-ghost"
                       style="padding:5px 10px;font-size:.75rem" title="Voir le profil">
                        <i class="bi bi-eye"></i>
                    </a>
                    <form method="POST" action="{{ route('admin.users.destroy', $loc) }}"
                          onsubmit="return confirm('Supprimer le compte de {{ addslashes($loc->name) }} ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-ghost"
                                style="padding:5px 10px;font-size:.75rem;color:#DC2626;border-color:#FECDD3" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($locataires->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #F3F4F6">
        {{ $locataires->links() }}
    </div>
    @endif

    @else
    <div style="padding:64px;text-align:center">
        <i class="bi bi-people" style="font-size:2.5rem;color:#E5E7EB"></i>
        <p style="color:#9CA3AF;margin:16px 0 0;font-size:.88rem">
            @if(request('q') || request('statut'))
                Aucun locataire trouvé pour ces critères.
            @else
                Aucun locataire inscrit pour l'instant.
            @endif
        </p>
    </div>
    @endif
</div>
@endsection
