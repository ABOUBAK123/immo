@extends('layouts.app')
@section('title', $bien->titre)
@php $sym = auth()->user()->deviseSymbole(); @endphp

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="{{ route('biens.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Mes biens
        </a>
        <h4 class="fw-bold mb-0">{{ $bien->titre }}</h4>
        <p class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $bien->adresse }}, {{ $bien->ville }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('biens.edit', $bien) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Modifier
        </a>
        <a href="{{ route('annonces.create', ['bien_id' => $bien->id]) }}" class="btn btn-primary">
            <i class="bi bi-megaphone me-1"></i> Publier annonce
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Photos --}}
        @if($bien->photos && count($bien->photos))
        <div class="card mb-3 p-0 overflow-hidden">
            <div id="photos" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach($bien->photos as $i => $p)
                    <div class="carousel-item {{ $i===0 ? 'active' : '' }}">
                        <img src="{{ asset('storage/'.$p) }}" class="d-block w-100" style="height:300px;object-fit:cover">
                    </div>
                    @endforeach
                </div>
                @if(count($bien->photos) > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#photos" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                <button class="carousel-control-next" type="button" data-bs-target="#photos" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                @endif
            </div>
        </div>
        @endif

        {{-- Caractéristiques --}}
        <div class="card p-4 mb-3">
            <h6 class="fw-bold mb-3">Caractéristiques</h6>
            <div class="row g-3">
                <div class="col-6 col-md-3 text-center border-end">
                    <div class="fw-bold fs-5">{{ $bien->surface ?? '—' }} @if($bien->surface)<small>m²</small>@endif</div>
                    <div class="text-muted small">Surface</div>
                </div>
                <div class="col-6 col-md-3 text-center border-end">
                    <div class="fw-bold fs-5">{{ $bien->nb_pieces ?? '—' }}</div>
                    <div class="text-muted small">Pièces</div>
                </div>
                <div class="col-6 col-md-3 text-center border-end">
                    <div class="fw-bold fs-5">{{ $bien->nb_chambres ?? '—' }}</div>
                    <div class="text-muted small">Chambres</div>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <div class="fw-bold fs-5">{{ $bien->etage !== null ? ($bien->etage === 0 ? 'RDC' : $bien->etage) : '—' }}</div>
                    <div class="text-muted small">Étage</div>
                </div>
            </div>
            <hr>
            <div class="row g-2 small">
                <div class="col-sm-4"><strong>Type :</strong> {{ ucfirst($bien->type) }}</div>
                <div class="col-sm-4"><strong>Meublé :</strong> {{ $bien->meuble ? 'Oui' : 'Non' }}</div>
                <div class="col-sm-4"><strong>DPE :</strong> {{ $bien->dpe ?? 'Non renseigné' }}</div>
                <div class="col-sm-4"><strong>Statut :</strong>
                    <span class="badge {{ $bien->statut === 'disponible' ? 'bg-success' : ($bien->statut === 'loue' ? 'bg-primary' : 'bg-warning text-dark') }}">
                        {{ $bien->statut }}
                    </span>
                </div>
                @if($bien->annee_construction)<div class="col-sm-4"><strong>Construction :</strong> {{ $bien->annee_construction }}</div>@endif
                @if($bien->prix_achat)<div class="col-sm-4"><strong>Prix achat :</strong> {{ number_format($bien->prix_achat, 0, ',', ' ') }} {{ $sym }}</div>@endif
            </div>
            @if($bien->description)
            <hr>
            <p class="text-muted mb-0">{{ $bien->description }}</p>
            @endif
        </div>

        {{-- Locations --}}
        <div class="card p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Historique des locations</h6>
                <a href="{{ route('locations.create', ['bien_id' => $bien->id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus me-1"></i> Nouveau bail
                </a>
            </div>
            @if($bien->locations->count())
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light"><tr>
                        <th>Locataire</th><th>Début</th><th>Loyer</th><th>Statut</th>
                    </tr></thead>
                    <tbody>
                    @foreach($bien->locations as $loc)
                    <tr>
                        <td>{{ $loc->locataire->name }}</td>
                        <td>{{ $loc->date_debut->format('d/m/Y') }}</td>
                        <td>{{ number_format($loc->loyer_mensuel, 0, ',', ' ') }} {{ $sym }}</td>
                        <td><span class="badge {{ $loc->statut === 'actif' ? 'bg-success' : 'bg-secondary' }}">{{ $loc->statut }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else<p class="text-muted small mb-0">Aucune location enregistrée.</p>@endif
        </div>

        {{-- Interventions --}}
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Interventions</h6>
                <a href="{{ route('interventions.create') }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-tools me-1"></i> Déclarer
                </a>
            </div>
            @if($bien->interventions->count())
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-light"><tr><th>Titre</th><th>Priorité</th><th>Statut</th><th>Date</th></tr></thead>
                    <tbody>
                    @foreach($bien->interventions->take(5) as $i)
                    <tr>
                        <td>{{ $i->titre }}</td>
                        <td><span class="badge {{ $i->priorite === 'urgente' ? 'bg-danger' : 'bg-secondary' }}">{{ $i->priorite }}</span></td>
                        <td>{{ $i->statut }}</td>
                        <td>{{ $i->date_demande->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else<p class="text-muted small mb-0">Aucune intervention.</p>@endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-3 mb-3">
            <h6 class="fw-bold mb-3">Propriétaire</h6>
            <p class="mb-1 fw-semibold">{{ $bien->proprietaire->name }}</p>
            <p class="text-muted small mb-0">{{ $bien->proprietaire->email }}</p>
            @if($bien->proprietaire->phone)
            <p class="text-muted small mb-0">{{ $bien->proprietaire->phone }}</p>
            @endif
        </div>
        @if($bien->annonces->count())
        <div class="card p-3">
            <h6 class="fw-bold mb-3">Annonces actives</h6>
            @foreach($bien->annonces->where('statut', 'active') as $a)
            <div class="d-flex justify-content-between mb-2">
                <span class="badge {{ $a->type === 'vente' ? 'bg-success' : 'bg-primary' }}">{{ $a->type }}</span>
                <span class="fw-semibold">{{ number_format($a->prix, 0, ',', ' ') }} {{ $sym }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
