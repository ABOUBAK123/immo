@extends('layouts.app')
@section('title', 'Modifier le bien')
@php $sym = auth()->user()->deviseSymbole(); @endphp

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Modifier : {{ $bien->titre }}</h4>
</div>

<div class="card p-4" style="max-width:780px">
    <form method="POST" action="{{ route('biens.update', $bien) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row g-3 mb-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Nom du propriétaire <span class="text-danger">*</span></label>
                <input type="text" name="nom_proprietaire"
                       class="form-control @error('nom_proprietaire') is-invalid @enderror"
                       value="{{ old('nom_proprietaire', $bien->nom_proprietaire) }}"
                       placeholder="Ex : M. Coulibaly, Société Immo SA…" required>
                @error('nom_proprietaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-8">
                <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                       value="{{ old('titre', $bien->titre) }}" required>
                @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Type</label>
                <select name="type" id="typeSelect" class="form-select">
                    @foreach(['appartement','maison','villa','studio','bureau','commerce','terrain'] as $t)
                    <option value="{{ $t }}" {{ old('type', $bien->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-3 mb-3" id="nomResidenceRow"
             style="{{ old('type', $bien->type) === 'appartement' ? '' : 'display:none' }}">
            <div class="col-12">
                <label class="form-label fw-semibold">Nom de la résidence</label>
                <input type="text" name="nom_residence" class="form-control"
                       value="{{ old('nom_residence', $bien->nom_residence) }}"
                       placeholder="Ex: Résidence Les Palmiers, Cité Biafra...">
                <div class="form-text">Facultatif — uniquement pour les appartements en résidence.</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-3">
                <label class="form-label fw-semibold">Surface (m²)</label>
                <input type="number" name="surface" class="form-control" value="{{ old('surface', $bien->surface) }}" step="0.01" min="0">
            </div>
            <div class="col-sm-3">
                <label class="form-label fw-semibold">Nb pièces</label>
                <input type="number" name="nb_pieces" class="form-control" value="{{ old('nb_pieces', $bien->nb_pieces) }}" min="0">
            </div>
            <div class="col-sm-3">
                <label class="form-label fw-semibold">Nb chambres</label>
                <input type="number" name="nb_chambres" class="form-control" value="{{ old('nb_chambres', $bien->nb_chambres) }}" min="0">
            </div>
            <div class="col-sm-3">
                <label class="form-label fw-semibold">Étage</label>
                <input type="number" name="etage" class="form-control" value="{{ old('etage', $bien->etage) }}" min="0">
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="meuble" id="meuble" class="form-check-input" value="1"
                   {{ $bien->meuble ? 'checked' : '' }}>
            <label for="meuble" class="form-check-label fw-semibold">Bien meublé</label>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Adresse <span class="text-danger">*</span></label>
                <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $bien->adresse) }}" required>
            </div>
            <div class="col-sm-5">
                <label class="form-label fw-semibold">Ville <span class="text-danger">*</span></label>
                <input type="text" name="ville" class="form-control" value="{{ old('ville', $bien->ville) }}" required>
            </div>
            <div class="col-sm-3">
                <label class="form-label fw-semibold">Code postal</label>
                <input type="text" name="code_postal" class="form-control" value="{{ old('code_postal', $bien->code_postal) }}">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Statut</label>
                <select name="statut" class="form-select">
                    @foreach(['disponible','loue','vendu','en_travaux'] as $s)
                    <option value="{{ $s }}" {{ $bien->statut === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Valeur estimée ({{ $sym }})</label>
                <input type="number" name="valeur_estimee" class="form-control" value="{{ old('valeur_estimee', $bien->valeur_estimee) }}" min="0">
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">DPE</label>
                <select name="dpe" class="form-select">
                    <option value="">—</option>
                    @foreach(['A','B','C','D','E','F','G'] as $d)
                    <option value="{{ $d }}" {{ $bien->dpe === $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description', $bien->description) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Nouvelles photos (remplace les actuelles)</label>
            <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-floppy me-1"></i> Enregistrer
            </button>
            <a href="{{ route('biens.show', $bien) }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const sel = document.getElementById('typeSelect');
    const row = document.getElementById('nomResidenceRow');
    if (!sel || !row) return;
    sel.addEventListener('change', function () {
        row.style.display = this.value === 'appartement' ? '' : 'none';
        if (this.value !== 'appartement') row.querySelector('input').value = '';
    });
})();
</script>
@endpush
@endsection
