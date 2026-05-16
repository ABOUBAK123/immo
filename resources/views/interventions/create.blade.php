@extends('layouts.app')
@section('title', 'Déclarer une intervention')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Déclarer une intervention</h4>
    <p class="text-muted small">Signalez un problème ou planifiez un entretien</p>
</div>

<div class="card p-4" style="max-width:680px">
    <form method="POST" action="{{ route('interventions.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Bien concerné <span class="text-danger">*</span></label>
            <select name="bien_id" class="form-select @error('bien_id') is-invalid @enderror" required>
                <option value="">Sélectionner...</option>
                @foreach($biens as $b)
                <option value="{{ $b->id }}" {{ old('bien_id') == $b->id ? 'selected' : '' }}>
                    {{ $b->titre }} — {{ $b->ville }}
                </option>
                @endforeach
            </select>
            @error('bien_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
            <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                   value="{{ old('titre') }}" placeholder="Ex: Fuite robinet cuisine" required>
            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                      placeholder="Décrivez le problème en détail..." required>{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    <option value="normal"    {{ old('type') === 'normal'    ? 'selected' : '' }}>Normal</option>
                    <option value="urgence"   {{ old('type') === 'urgence'   ? 'selected' : '' }}>Urgence</option>
                    <option value="preventif" {{ old('type') === 'preventif' ? 'selected' : '' }}>Préventif</option>
                </select>
            </div>
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Priorité <span class="text-danger">*</span></label>
                <select name="priorite" class="form-select" required>
                    <option value="basse"   {{ old('priorite') === 'basse'   ? 'selected' : '' }}>Basse</option>
                    <option value="moyenne" {{ old('priorite', 'moyenne') === 'moyenne' ? 'selected' : '' }}>Moyenne</option>
                    <option value="haute"   {{ old('priorite') === 'haute'   ? 'selected' : '' }}>Haute</option>
                    <option value="urgente" {{ old('priorite') === 'urgente' ? 'selected' : '' }}>Urgente</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Photos (optionnel)</label>
            <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-send me-1"></i> Envoyer la demande
            </button>
            <a href="{{ route('interventions.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
