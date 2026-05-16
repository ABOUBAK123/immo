@extends('layouts.app')
@section('title', 'Modifier l\'annonce')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Modifier l'annonce</h4>
</div>

<div class="card p-4" style="max-width:720px">
    <form method="POST" action="{{ route('annonces.update', $annonce) }}">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
            <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                   value="{{ old('titre', $annonce->titre) }}" required>
            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Prix (€) <span class="text-danger">*</span></label>
                <input type="number" name="prix" class="form-control @error('prix') is-invalid @enderror"
                       value="{{ old('prix', $annonce->prix) }}" min="0" required>
                @error('prix')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Statut</label>
                <select name="statut" class="form-select">
                    @foreach(['active','inactive','vendu','loue','archive'] as $s)
                    <option value="{{ $s }}" {{ $annonce->statut === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" rows="4" class="form-control">{{ old('description', $annonce->description) }}</textarea>
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" name="prix_negociable" id="nego" class="form-check-input" value="1"
                   {{ $annonce->prix_negociable ? 'checked' : '' }}>
            <label for="nego" class="form-check-label">Prix négociable</label>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-floppy me-1"></i> Enregistrer
            </button>
            <a href="{{ route('annonces.show', $annonce) }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
