@extends('layouts.app')
@section('title', 'Publier une annonce')
@php $sym = auth()->user()->deviseSymbole(); @endphp

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Publier une annonce</h4>
    <p class="text-muted small">Mettez votre bien en location ou en vente</p>
</div>

<div class="card p-4" style="max-width:720px">
    <form method="POST" action="{{ route('annonces.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Bien concerné <span class="text-danger">*</span></label>
            <select name="bien_id" class="form-select @error('bien_id') is-invalid @enderror" required>
                <option value="">Choisir un bien...</option>
                @foreach($biens as $bien)
                <option value="{{ $bien->id }}" {{ old('bien_id') == $bien->id ? 'selected' : '' }}>
                    {{ $bien->titre }} — {{ $bien->ville }}
                </option>
                @endforeach
            </select>
            @error('bien_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Type d'annonce <span class="text-danger">*</span></label>
                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                    <option value="location" {{ old('type') === 'location' ? 'selected' : '' }}>Location</option>
                    <option value="vente"    {{ old('type') === 'vente'    ? 'selected' : '' }}>Vente</option>
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Prix ({{ $sym }}) <span class="text-danger">*</span></label>
                <input type="number" name="prix" class="form-control @error('prix') is-invalid @enderror"
                       value="{{ old('prix') }}" min="0" step="1" required>
                @error('prix')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3" id="typeTarifRow">
            <label class="form-label fw-semibold">Tarification</label>
            <div class="d-flex gap-2">
                @foreach(['mois' => 'Par mois', 'jour' => 'Par jour'] as $v => $l)
                <div class="form-check form-check-inline border rounded px-3 py-2" style="cursor:pointer">
                    <input class="form-check-input" type="radio" name="type_tarif" id="tarif_{{ $v }}"
                           value="{{ $v }}" {{ old('type_tarif','mois') === $v ? 'checked' : '' }}>
                    <label class="form-check-label" for="tarif_{{ $v }}" style="cursor:pointer">{{ $l }}</label>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Titre de l'annonce <span class="text-danger">*</span></label>
            <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                   value="{{ old('titre') }}" placeholder="Ex: Bel appartement 3 pièces lumineux" required>
            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                      placeholder="Décrivez le bien, son environnement, les équipements...">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Date de disponibilité</label>
                <input type="date" name="date_disponibilite" class="form-control" value="{{ old('date_disponibilite') }}">
            </div>
            <div class="col-sm-6 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="prix_negociable" id="nego" class="form-check-input" value="1"
                           {{ old('prix_negociable') ? 'checked' : '' }}>
                    <label for="nego" class="form-check-label">Prix négociable</label>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Photos</label>
            <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
            <div class="form-text">Maximum 5 Mo par photo. Formats acceptés : JPG, PNG, WebP.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-megaphone me-1"></i> Publier l'annonce
            </button>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
