@extends('layouts.app')
@section('title', 'Gestion Propriétaires')
@section('page-title', 'Propriétaires inscrits')

@section('topbar-actions')
<div style="display:flex;gap:8px;align-items:center">
    <form method="GET" style="display:flex;gap:8px">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Rechercher…" class="form-control-immo" style="width:220px">
        <button type="submit" class="btn-ghost"><i class="bi bi-search"></i></button>
        @if(request('q'))
        <a href="{{ route('admin.proprietaires') }}" class="btn-ghost">✕</a>
        @endif
    </form>
</div>
@endsection

@section('content')

{{-- KPIs ─────────────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#EFF6FF;color:#2563EB">
            <i class="bi bi-person-badge"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['total'] }}</div>
            <div class="stat-label">Propriétaires</div>
        </div>
    </div>
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#F0FDF4;color:#16A34A">
            <i class="bi bi-buildings"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['total_biens'] }}</div>
            <div class="stat-label">Biens au total</div>
        </div>
    </div>
    <div class="card-immo stat-card">
        <div class="stat-icon" style="background:#FFFBEB;color:#D97706">
            <i class="bi bi-key-fill"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['biens_loues'] }}</div>
            <div class="stat-label">Biens loués</div>
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
            <i class="bi bi-person-badge me-2" style="color:#2563EB"></i>
            Liste des propriétaires
        </span>
        <span style="font-size:.78rem;color:#9CA3AF">{{ $proprietaires->total() }} compte(s)</span>
    </div>

    @if($proprietaires->count())
    <table class="table-immo">
        <thead>
            <tr>
                <th>Propriétaire</th>
                <th>Contact</th>
                <th>Patrimoine</th>
                <th>Taux occupation</th>
                <th>Inscrit le</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($proprietaires as $p)
        @php
            $tauxOccup = $p->biens_count > 0
                ? round($p->biens_loues_count / $p->biens_count * 100)
                : 0;
        @endphp
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:11px">
                    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#DBEAFE,#EFF6FF);color:#1D4ED8;
                                display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($p->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem">{{ $p->name }}</div>
                        <div style="font-size:.7rem;color:#9CA3AF">ID #{{ $p->id }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div style="font-size:.82rem">{{ $p->email }}</div>
                @if($p->phone)
                <div style="font-size:.72rem;color:#9CA3AF"><i class="bi bi-telephone me-1"></i>{{ $p->phone }}</div>
                @endif
            </td>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="text-align:center;padding:6px 10px;background:#EFF6FF;border-radius:8px;min-width:44px">
                        <div style="font-size:1rem;font-weight:700;color:#2563EB">{{ $p->biens_count }}</div>
                        <div style="font-size:.62rem;color:#6B7280">biens</div>
                    </div>
                    <div style="text-align:center;padding:6px 10px;background:#F0FDF4;border-radius:8px;min-width:44px">
                        <div style="font-size:1rem;font-weight:700;color:#16A34A">{{ $p->biens_loues_count }}</div>
                        <div style="font-size:.62rem;color:#6B7280">loués</div>
                    </div>
                </div>
            </td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="progress-immo" style="width:70px">
                        <div class="progress-fill" style="width:{{ $tauxOccup }}%;background:{{ $tauxOccup >= 80 ? '#16A34A' : ($tauxOccup >= 50 ? '#D97706' : '#DC2626') }}"></div>
                    </div>
                    <span style="font-size:.78rem;font-weight:600;color:{{ $tauxOccup >= 80 ? '#16A34A' : ($tauxOccup >= 50 ? '#D97706' : '#DC2626') }}">
                        {{ $tauxOccup }}%
                    </span>
                </div>
            </td>
            <td style="font-size:.8rem;color:#6B7280">
                {{ $p->created_at->format('d/m/Y') }}
                <div style="font-size:.7rem;color:#9CA3AF">{{ $p->created_at->diffForHumans() }}</div>
            </td>
            <td>
                <div style="display:flex;gap:6px;justify-content:flex-end">
                    <a href="{{ route('admin.proprietaires.show', $p) }}" class="btn-ghost" style="padding:5px 10px;font-size:.75rem" title="Voir le profil">
                        <i class="bi bi-eye"></i>
                    </a>
                    <form method="POST" action="{{ route('admin.users.destroy', $p) }}"
                          onsubmit="return confirm('Supprimer le compte de {{ addslashes($p->name) }} ? Cette action est irréversible.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-ghost" style="padding:5px 10px;font-size:.75rem;color:#DC2626;border-color:#FECDD3" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($proprietaires->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #F3F4F6">
        {{ $proprietaires->links() }}
    </div>
    @endif

    @else
    <div style="padding:64px;text-align:center">
        <i class="bi bi-person-badge" style="font-size:2.5rem;color:#E5E7EB"></i>
        <p style="color:#9CA3AF;margin:16px 0 0;font-size:.88rem">
            @if(request('q'))
                Aucun propriétaire trouvé pour « {{ request('q') }} ».
            @else
                Aucun propriétaire inscrit pour l'instant.
            @endif
        </p>
    </div>
    @endif
</div>
@endsection
