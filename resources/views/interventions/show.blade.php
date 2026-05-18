@extends('layouts.app')
@section('title', $intervention->titre)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="{{ route('interventions.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Interventions
        </a>
        <h4 class="fw-bold mb-0">{{ $intervention->titre }}</h4>
        <p class="text-muted small mb-0">{{ optional($intervention->bien)->titre ?? '—' }} — {{ optional($intervention->bien)->ville ?? '—' }}</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge {{ $intervention->priorite === 'urgente' ? 'bg-danger' : ($intervention->priorite === 'haute' ? 'bg-warning text-dark' : 'bg-secondary') }} px-3 py-2">
            {{ ucfirst($intervention->priorite) }}
        </span>
        <span class="badge {{ $intervention->statut === 'termine' ? 'bg-success' : ($intervention->statut === 'en_cours' ? 'bg-primary' : 'bg-secondary') }} px-3 py-2">
            {{ ucfirst(str_replace('_', ' ', $intervention->statut)) }}
        </span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4 mb-3">
            <h6 class="fw-bold mb-3">Description</h6>
            <p>{{ $intervention->description }}</p>

            <div class="row g-2 mt-3 small text-muted">
                <div class="col-sm-4"><strong>Type :</strong> {{ $intervention->type }}</div>
                <div class="col-sm-4"><strong>Date demande :</strong> {{ $intervention->date_demande->format('d/m/Y') }}</div>
                @if($intervention->date_intervention)
                <div class="col-sm-4"><strong>Date intervention :</strong> {{ $intervention->date_intervention->format('d/m/Y') }}</div>
                @endif
                @if($intervention->cout)
                <div class="col-sm-4"><strong>Coût :</strong> {{ number_format($intervention->cout, 0, ',', ' ') }} €</div>
                @endif
            </div>

            @if($intervention->note_resolution)
            <hr>
            <h6 class="fw-semibold">Note de résolution</h6>
            <p class="text-muted">{{ $intervention->note_resolution }}</p>
            @endif

            @if($intervention->photos && count($intervention->photos))
            <hr>
            <h6 class="fw-semibold mb-2">Photos</h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach($intervention->photos as $p)
                <img src="{{ asset('storage/'.$p) }}" style="height:120px;width:160px;object-fit:cover;border-radius:8px" alt="">
                @endforeach
            </div>
            @endif
        </div>

        {{-- Mise à jour statut (propriétaire / admin) --}}
        @if(in_array(auth()->user()->role, ['admin','proprietaire']) && $intervention->statut !== 'termine')
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Mettre à jour l'intervention</h6>
            <form method="POST" action="{{ route('interventions.update', $intervention) }}">
                @csrf @method('PATCH')
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Statut</label>
                        <select name="statut" class="form-select form-select-sm">
                            @foreach(['en_attente','en_cours','termine','annule'] as $s)
                            <option value="{{ $s }}" {{ $intervention->statut === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Date intervention</label>
                        <input type="date" name="date_intervention" class="form-control form-control-sm"
                               value="{{ $intervention->date_intervention?->format('Y-m-d') }}">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Coût (€)</label>
                        <input type="number" name="cout" class="form-control form-control-sm" value="{{ $intervention->cout }}" min="0" step="0.01">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Note de résolution</label>
                        <textarea name="note_resolution" rows="3" class="form-control form-control-sm"
                                  placeholder="Décrivez les travaux effectués...">{{ $intervention->note_resolution }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-floppy me-1"></i> Mettre à jour
                </button>
            </form>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        @if($intervention->locataire)
        <div class="card p-3 mb-3">
            <h6 class="fw-bold mb-2">Demandeur</h6>
            <p class="mb-0 fw-semibold">{{ $intervention->locataire->name }}</p>
            <p class="text-muted small">{{ $intervention->locataire->email }}</p>
        </div>
        @endif
        @if($intervention->prestataire)
        <div class="card p-3 mb-3">
            <h6 class="fw-bold mb-2">Prestataire</h6>
            <p class="mb-0 fw-semibold">{{ $intervention->prestataire->name }}</p>
        </div>
        @endif
        <div class="card p-3">
            <h6 class="fw-bold mb-2">Bien</h6>
            <p class="mb-1 fw-semibold">{{ optional($intervention->bien)->titre ?? '—' }}</p>
            <p class="text-muted small mb-2">{{ optional($intervention->bien)->adresse ?? '' }}{{ $intervention->bien ? ', ' . $intervention->bien->ville : '' }}</p>
            @if($intervention->bien)<a href="{{ route('biens.show', $intervention->bien) }}" class="btn btn-sm btn-outline-primary">Voir le bien</a>@endif
        </div>
    </div>
</div>
@endsection
