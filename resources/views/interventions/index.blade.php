@extends('layouts.app')
@section('title', 'Interventions')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Interventions & Maintenance</h4>
        <p class="text-muted small mb-0">Suivi des demandes de travaux</p>
    </div>
    <a href="{{ route('interventions.create') }}" class="btn btn-warning">
        <i class="bi bi-tools me-1"></i> Déclarer une intervention
    </a>
</div>

@if($interventions->count())
<div class="row g-3">
    @foreach($interventions as $i)
    <div class="col-md-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge {{ $i->priorite === 'urgente' ? 'bg-danger' : ($i->priorite === 'haute' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $i->priorite }}
                    </span>
                    <span class="badge {{ $i->statut === 'termine' ? 'bg-success' : ($i->statut === 'en_cours' ? 'bg-primary' : 'bg-secondary') }}">
                        {{ $i->statut }}
                    </span>
                </div>
                <h6 class="fw-bold mb-1">{{ $i->titre }}</h6>
                <p class="text-muted small mb-2">
                    <i class="bi bi-building me-1"></i>{{ $i->bien->titre }}
                </p>
                <p class="text-muted small mb-2">{{ Str::limit($i->description, 80) }}</p>
                <div class="d-flex justify-content-between align-items-center mt-3 small text-muted">
                    <span><i class="bi bi-calendar me-1"></i>{{ $i->date_demande->format('d/m/Y') }}</span>
                    @if($i->cout)<span><i class="bi bi-currency-euro me-1"></i>{{ number_format($i->cout, 0, ',', ' ') }} €</span>@endif
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pb-3">
                <a href="{{ route('interventions.show', $i) }}" class="btn btn-sm btn-outline-primary w-100">
                    Voir le détail
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4">{{ $interventions->links() }}</div>
@else
<div class="text-center py-5">
    <i class="bi bi-tools text-muted" style="font-size:3rem"></i>
    <p class="text-muted mt-3">Aucune intervention enregistrée.</p>
    <a href="{{ route('interventions.create') }}" class="btn btn-warning">Déclarer une intervention</a>
</div>
@endif
@endsection
