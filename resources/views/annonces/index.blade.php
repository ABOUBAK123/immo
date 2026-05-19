@extends('layouts.app')
@section('title', 'Annonces immobilières')
@php $sym = auth()->check() ? auth()->user()->deviseSymbole() : 'FCFA'; @endphp

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Annonces immobilières</h4>
        <p class="text-muted small mb-0">Trouvez votre bien idéal</p>
    </div>
    @auth
    @if(in_array(auth()->user()->role, ['admin','proprietaire','agent']))
    <a href="{{ route('annonces.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Publier
    </a>
    @endif
    @endauth
</div>

{{-- Filtres --}}
<div class="card mb-4 p-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-sm-3">
            <label class="form-label small fw-semibold">Type</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Tous</option>
                <option value="location" {{ request('type') === 'location' ? 'selected' : '' }}>Location</option>
                <option value="vente"    {{ request('type') === 'vente'    ? 'selected' : '' }}>Vente</option>
            </select>
        </div>
        <div class="col-sm-3">
            <label class="form-label small fw-semibold">Ville</label>
            <select name="ville" class="form-select form-select-sm">
                <option value="">Toutes</option>
                @foreach($villes as $v)
                <option value="{{ $v }}" {{ request('ville') === $v ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-2">
            <label class="form-label small fw-semibold">Prix max ({{ $sym }})</label>
            <input type="number" name="prix_max" class="form-control form-control-sm" value="{{ request('prix_max') }}" placeholder="Ex: 1500">
        </div>
        <div class="col-sm-2">
            <label class="form-label small fw-semibold">Surface min (m²)</label>
            <input type="number" name="surface_min" class="form-control form-control-sm" value="{{ request('surface_min') }}" placeholder="Ex: 40">
        </div>
        <div class="col-sm-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                <i class="bi bi-search me-1"></i> Filtrer
            </button>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x"></i>
            </a>
        </div>
    </form>
</div>

{{-- Résultats --}}
@if($annonces->count())
<div class="row g-3">
    @foreach($annonces as $annonce)
    <div class="col-sm-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm">
            @if($annonce->photos && count($annonce->photos))
            <img src="{{ asset('storage/' . $annonce->photos[0]) }}" class="card-img-top" style="height:180px;object-fit:cover" alt="">
            @else
            <div class="bg-light d-flex align-items-center justify-content-center" style="height:180px">
                <i class="bi bi-house text-muted fs-1"></i>
            </div>
            @endif
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge {{ $annonce->type === 'vente' ? 'bg-success' : 'bg-primary' }}">
                        {{ ucfirst($annonce->type) }}
                    </span>
                    <small class="text-muted"><i class="bi bi-eye me-1"></i>{{ $annonce->vues }}</small>
                </div>
                <h6 class="fw-bold mb-1">{{ $annonce->titre }}</h6>
                <p class="text-muted small mb-2">
                    <i class="bi bi-geo-alt me-1"></i>{{ $annonce->bien->ville }}
                    @if($annonce->bien->surface)
                    &bull; {{ $annonce->bien->surface }} m²
                    @endif
                </p>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="fw-bold text-primary fs-5">
                        {{ number_format($annonce->prix, 0, ',', ' ') }} {{ $sym }}{{ $annonce->type === 'location' ? ('/'.$annonce->type_tarif) : '' }}
                    </span>
                    <a href="{{ route('annonces.show', $annonce) }}" class="btn btn-sm btn-outline-primary">
                        Voir <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4">{{ $annonces->withQueryString()->links() }}</div>
@else
<div class="text-center py-5">
    <i class="bi bi-search text-muted" style="font-size:3rem"></i>
    <p class="text-muted mt-3">Aucune annonce ne correspond à vos critères.</p>
    <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">Voir toutes les annonces</a>
</div>
@endif
@endsection
