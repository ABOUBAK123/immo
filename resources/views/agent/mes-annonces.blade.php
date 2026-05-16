@extends('layouts.app')
@section('title', 'Mes Annonces')
@section('page-title', 'Mes Annonces')

@section('topbar-actions')
<a href="{{ route('agent.publier') }}" class="btn-primary-immo">
    <i class="bi bi-plus-lg"></i> Publier un bien
</a>
@endsection

@section('content')

{{-- KPIs --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    @php
    $kpis = [
        ['Total annonces',   $stats['total'],   '#2563EB','#EFF6FF','megaphone'],
        ['Actives',          $stats['actives'], '#16A34A','#F0FDF4','check-circle'],
        ['Vues totales',     number_format($stats['vues'],0,',',' '), '#F97316','#FFF7ED','eye'],
        ['Vendus / Loués',   $stats['vendus'],  '#7C3AED','#F5F3FF','house-check'],
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

{{-- Filtres --}}
<form method="GET" style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
    <select name="statut" onchange="this.form.submit()"
            style="border:1px solid #E5E7EB;border-radius:8px;padding:7px 12px;font-size:.8rem;background:#fff">
        <option value="">Tous les statuts</option>
        @foreach(['active'=>'Actives','inactive'=>'Inactives','vendu'=>'Vendus','loue'=>'Loués'] as $v=>$l)
        <option value="{{ $v }}" {{ request('statut') === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
    </select>
    <select name="type" onchange="this.form.submit()"
            style="border:1px solid #E5E7EB;border-radius:8px;padding:7px 12px;font-size:.8rem;background:#fff">
        <option value="">Tous les types</option>
        <option value="location" {{ request('type') === 'location' ? 'selected' : '' }}>Location</option>
        <option value="vente"    {{ request('type') === 'vente'    ? 'selected' : '' }}>Vente</option>
    </select>
    @if(request()->hasAny(['statut','type']))
    <a href="{{ route('agent.mes-annonces') }}" class="btn-ghost">
        <i class="bi bi-x"></i> Effacer filtres
    </a>
    @endif
</form>

{{-- Liste --}}
<div class="card-immo">
    @if($annonces->count())
    <table class="table-immo">
        <thead>
            <tr>
                <th>Annonce</th><th>Type</th><th>Localisation</th><th>Prix</th><th>Vues</th><th>Date</th><th>Statut</th><th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($annonces as $a)
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    @if($a->photos && count($a->photos))
                    <img src="{{ asset('storage/'.$a->photos[0]) }}"
                         style="width:50px;height:40px;border-radius:7px;object-fit:cover;flex-shrink:0" alt="">
                    @else
                    <div style="width:50px;height:40px;border-radius:7px;background:#F3F4F6;display:flex;align-items:center;justify-content:center;color:#D1D5DB;flex-shrink:0">
                        <i class="bi bi-house"></i>
                    </div>
                    @endif
                    <div style="min-width:0">
                        <div style="font-size:.83rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px">{{ $a->titre }}</div>
                        <div style="font-size:.7rem;color:#9CA3AF">{{ $a->bien?->type }}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge-pill {{ $a->type === 'vente' ? 'badge-success' : 'badge-info' }}">
                    {{ ucfirst($a->type) }}
                </span>
            </td>
            <td style="font-size:.78rem;color:#6B7280">
                <i class="bi bi-geo-alt me-1"></i>{{ $a->bien?->ville }}
            </td>
            <td style="font-size:.85rem;font-weight:700;color:#2563EB">
                {{ number_format($a->prix, 0, ',', ' ') }}
            </td>
            <td style="font-size:.83rem">
                <i class="bi bi-eye me-1" style="color:#9CA3AF"></i>{{ $a->vues }}
            </td>
            <td style="font-size:.75rem;color:#9CA3AF">{{ $a->created_at->format('d/m/Y') }}</td>
            <td>
                <span class="badge-pill {{ $a->statut === 'active' ? 'badge-success' : ($a->statut === 'vendu' || $a->statut === 'loue' ? 'badge-gray' : 'badge-warning') }}">
                    {{ ucfirst($a->statut) }}
                </span>
            </td>
            <td>
                <div style="display:flex;gap:6px">
                    <a href="{{ route('annonces.show', $a) }}"
                       style="display:inline-flex;align-items:center;padding:4px 10px;border:1px solid #E5E7EB;border-radius:6px;font-size:.72rem;color:#374151;text-decoration:none">
                        <i class="bi bi-eye"></i>
                    </a>
                    <form method="POST" action="{{ route('agent.annonces.toggle', $a) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                style="display:inline-flex;align-items:center;padding:4px 10px;
                                       border:1px solid {{ $a->statut === 'active' ? '#FECDD3' : '#BBF7D0' }};
                                       border-radius:6px;font-size:.72rem;
                                       color:{{ $a->statut === 'active' ? '#DC2626' : '#16A34A' }};
                                       background:transparent;cursor:pointer">
                            <i class="bi bi-{{ $a->statut === 'active' ? 'pause' : 'play' }}"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div style="padding:16px 20px">{{ $annonces->links() }}</div>
    @else
    <div style="padding:60px;text-align:center;color:#9CA3AF">
        <i class="bi bi-megaphone" style="font-size:3rem;display:block;margin-bottom:16px;color:#FDBA74"></i>
        <div style="font-size:.95rem;font-weight:600;color:#7C2D12;margin-bottom:8px">Aucune annonce publiée</div>
        <p style="font-size:.83rem;margin:0 0 20px">Commencez par publier votre premier bien immobilier.</p>
        <a href="{{ route('agent.publier') }}" class="btn-primary-immo">
            <i class="bi bi-plus-lg"></i> Publier un bien
        </a>
    </div>
    @endif
</div>
@endsection
