@extends('layouts.app')
@section('title', 'Mes biens')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Mes biens</h4>
        <p class="text-muted small mb-0">{{ $biens->total() }} bien(s) enregistré(s)</p>
    </div>
    <a href="{{ route('biens.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Ajouter un bien
    </a>
</div>

@if($biens->count())
<div class="row g-3">
    @foreach($biens as $bien)
    <div class="col-sm-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm">
            @if($bien->photos && count($bien->photos))
            <img src="{{ asset('storage/'.$bien->photos[0]) }}" class="card-img-top" style="height:160px;object-fit:cover" alt="">
            @else
            <div class="bg-light d-flex align-items-center justify-content-center" style="height:160px">
                <i class="bi bi-building text-muted fs-1"></i>
            </div>
            @endif
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-secondary">{{ $bien->type }}</span>
                    <span class="badge {{ $bien->statut === 'disponible' ? 'bg-success' : ($bien->statut === 'loue' ? 'bg-primary' : 'bg-warning text-dark') }}">
                        {{ $bien->statut }}
                    </span>
                </div>
                <h6 class="fw-bold mb-1">{{ $bien->titre }}</h6>
                <p class="text-muted small mb-1">
                    <i class="bi bi-geo-alt me-1"></i>{{ $bien->adresse }}, {{ $bien->ville }}
                </p>
                <div class="d-flex gap-2 text-muted small">
                    @if($bien->surface)<span><i class="bi bi-aspect-ratio me-1"></i>{{ $bien->surface }} m²</span>@endif
                    @if($bien->nb_pieces)<span><i class="bi bi-grid me-1"></i>{{ $bien->nb_pieces }} p.</span>@endif
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pb-3 d-flex gap-2">
                <a href="{{ route('biens.show', $bien) }}" class="btn btn-sm btn-outline-primary flex-fill">Détail</a>
                <a href="{{ route('biens.edit', $bien) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="{{ route('biens.destroy', $bien) }}"
                      onsubmit="return confirm('Supprimer ce bien ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4">{{ $biens->links() }}</div>
@else
<div class="text-center py-5">
    <i class="bi bi-building text-muted" style="font-size:3rem"></i>
    <p class="text-muted mt-3">Vous n'avez pas encore de bien enregistré.</p>
    <a href="{{ route('biens.create') }}" class="btn btn-primary">Ajouter votre premier bien</a>
</div>
@endif
@endsection
