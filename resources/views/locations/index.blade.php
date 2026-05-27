@extends('layouts.app')
@section('title', 'Locations')
@section('page-title', 'Locations & Baux')
@php $sym = auth()->user()->deviseSymbole(); @endphp

@section('topbar-actions')
@if(in_array(auth()->user()->role, ['admin','proprietaire']))
<a href="{{ route('locations.create') }}" class="btn-primary-immo">
    <i class="bi bi-file-earmark-plus"></i> Nouveau bail
</a>
@endif
@endsection

@section('content')

@if(session('success'))
<div class="alert-immo alert-immo-success mb-4">
    <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

{{-- Filtres statut --}}
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    @foreach([''=>'Tous', 'actif'=>'Actifs', 'en_attente'=>'En attente', 'resilie'=>'Résiliés', 'termine'=>'Terminés'] as $val => $lbl)
    <a href="{{ route('locations.index', $val ? ['statut'=>$val] : []) }}"
       style="padding:6px 14px;border-radius:20px;font-size:.78rem;font-weight:600;text-decoration:none;border:1.5px solid;transition:all .15s;
              {{ request('statut') === $val || (!request('statut') && $val === '') ? 'background:#2563EB;color:#fff;border-color:#2563EB' : 'background:#fff;color:#6B7280;border-color:#E5E7EB' }}">
        {{ $lbl }}
    </a>
    @endforeach
</div>

<div class="card-immo">
    @if($locations->count())
    <table class="table-immo">
        <thead>
            <tr>
                <th>Bien</th>
                <th>Locataire</th>
                <th>Loyer total</th>
                <th>Durée</th>
                <th>Type</th>
                <th>Statut</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($locations as $loc)
        <tr>
            <td>
                <div style="font-weight:600;font-size:.85rem">{{ optional($loc->bien)->titre ?? '—' }}</div>
                <div style="font-size:.72rem;color:#9CA3AF"><i class="bi bi-geo-alt me-1"></i>{{ optional($loc->bien)->ville ?? '—' }}</div>
            </td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:28px;height:28px;border-radius:50%;background:#DBEAFE;color:#1D4ED8;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($loc->locataire->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:.82rem;font-weight:500">{{ $loc->locataire->name }}</div>
                        <div style="font-size:.7rem;color:#9CA3AF">{{ $loc->locataire->email }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div style="font-weight:700;font-size:.9rem">{{ number_format($loc->montant_total, 0, ',', ' ') }} {{ $sym }}<span style="font-weight:400;font-size:.72rem;color:#9CA3AF">/mois</span></div>
                @if($loc->charges)
                <div style="font-size:.7rem;color:#9CA3AF">dont {{ number_format($loc->charges, 0, ',', ' ') }} {{ $sym }} charges</div>
                @endif
                @if($loc->frais_agence > 0)
                <div style="font-size:.7rem;color:#9CA3AF">dont {{ $loc->frais_agence }}% agence ({{ number_format($loc->montant_frais_agence, 0, ',', ' ') }} {{ $sym }})</div>
                @endif
            </td>
            <td>
                <div style="font-size:.8rem">{{ $loc->date_debut->format('d/m/Y') }}</div>
                @if($loc->date_fin)
                <div style="font-size:.72rem;color:#9CA3AF">→ {{ $loc->date_fin->format('d/m/Y') }}</div>
                @else
                <div style="font-size:.72rem;color:#9CA3AF">Indéterminé</div>
                @endif
            </td>
            <td>
                <span class="badge-pill badge-gray" style="text-transform:capitalize">{{ str_replace('_', ' ', $loc->type_bail) }}</span>
            </td>
            <td>
                <span class="badge-pill {{ $loc->statut === 'actif' ? 'badge-success' : ($loc->statut === 'en_attente' ? 'badge-warning' : ($loc->statut === 'resilie' ? 'badge-danger' : 'badge-gray')) }}">
                    {{ ucfirst(str_replace('_', ' ', $loc->statut)) }}
                </span>
            </td>
            <td style="white-space:nowrap">
                <div style="display:flex;gap:6px">
                    <a href="{{ route('locations.show', $loc) }}" class="btn-ghost" style="padding:5px 10px;font-size:.75rem">
                        <i class="bi bi-eye"></i> Voir
                    </a>
                    @if(in_array(auth()->user()->role, ['admin','proprietaire']))
                    <a href="{{ route('locations.edit', $loc) }}" class="btn-ghost"
                       style="padding:5px 10px;font-size:.75rem;color:#2563EB;border-color:#BFDBFE">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($locations->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #F3F4F6">
        {{ $locations->links() }}
    </div>
    @endif

    @else
    <div style="padding:60px;text-align:center">
        <i class="bi bi-file-earmark-text" style="font-size:2.5rem;color:#E5E7EB"></i>
        <p style="color:#9CA3AF;margin:16px 0 20px;font-size:.88rem">Aucune location enregistrée.</p>
        @if(in_array(auth()->user()->role, ['admin','proprietaire']))
        <a href="{{ route('locations.create') }}" class="btn-primary-immo">
            <i class="bi bi-file-earmark-plus"></i> Créer un premier bail
        </a>
        @endif
    </div>
    @endif
</div>
@endsection
