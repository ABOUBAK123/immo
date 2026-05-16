@extends('layouts.app')
@section('title', 'Ajouter un bien')
@php $sym = auth()->user()->deviseSymbole(); @endphp

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Ajouter un bien</h4>
    <p class="text-muted small">Renseignez les informations de votre bien immobilier</p>
</div>

<div class="card p-4" style="max-width:780px">
    <form method="POST" action="{{ route('biens.store') }}" enctype="multipart/form-data">
        @csrf

        <h6 class="fw-bold text-primary mb-3">Informations générales</h6>
        <div class="row g-3 mb-3">
            <div class="col-sm-8">
                <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                       value="{{ old('titre') }}" placeholder="Ex: Appartement T3 centre-ville" required>
                @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                <select name="type" id="typeSelect" class="form-select @error('type') is-invalid @enderror" required>
                    @foreach(['appartement','maison','villa','studio','bureau','commerce','terrain'] as $t)
                    <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-3 mb-3" id="nomResidenceRow" style="{{ old('type', 'appartement') === 'appartement' ? '' : 'display:none' }}">
            <div class="col-12">
                <label class="form-label fw-semibold">Nom de la résidence</label>
                <input type="text" name="nom_residence" class="form-control"
                       value="{{ old('nom_residence') }}"
                       placeholder="Ex: Résidence Les Palmiers, Cité Biafra...">
                <div class="form-text">Facultatif — uniquement pour les appartements en résidence.</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-3">
                <label class="form-label fw-semibold">Surface (m²)</label>
                <input type="number" name="surface" class="form-control" value="{{ old('surface') }}" step="0.01" min="0">
            </div>
            <div class="col-sm-3">
                <label class="form-label fw-semibold">Nb pièces</label>
                <input type="number" name="nb_pieces" class="form-control" value="{{ old('nb_pieces') }}" min="0">
            </div>
            <div class="col-sm-3">
                <label class="form-label fw-semibold">Nb chambres</label>
                <input type="number" name="nb_chambres" class="form-control" value="{{ old('nb_chambres') }}" min="0">
            </div>
            <div class="col-sm-3">
                <label class="form-label fw-semibold">Étage</label>
                <input type="number" name="etage" class="form-control" value="{{ old('etage') }}" min="0">
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="meuble" id="meuble" class="form-check-input" value="1"
                   {{ old('meuble') ? 'checked' : '' }}>
            <label for="meuble" class="form-check-label fw-semibold">Bien meublé</label>
        </div>

        <hr class="my-3">
        <h6 class="fw-bold text-primary mb-3">Adresse</h6>
        <div class="row g-3 mb-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Adresse <span class="text-danger">*</span></label>
                <input type="text" name="adresse" class="form-control @error('adresse') is-invalid @enderror"
                       value="{{ old('adresse') }}" required>
                @error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-5">
                <label class="form-label fw-semibold">Ville <span class="text-danger">*</span></label>
                <input type="text" name="ville" class="form-control @error('ville') is-invalid @enderror"
                       value="{{ old('ville') }}" required>
                @error('ville')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-3">
                <label class="form-label fw-semibold">Code postal</label>
                <input type="text" name="code_postal" class="form-control" value="{{ old('code_postal') }}">
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Pays</label>
                <input type="text" name="pays" class="form-control" value="{{ old('pays', 'France') }}">
            </div>
        </div>

        <hr class="my-3">
        <h6 class="fw-bold text-primary mb-3">Informations financières & statut</h6>
        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                <select name="statut" class="form-select" required>
                    @foreach(['disponible','loue','vendu','en_travaux'] as $s)
                    <option value="{{ $s }}" {{ old('statut', 'disponible') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Prix d'achat ({{ $sym }})</label>
                <input type="number" name="prix_achat" class="form-control" value="{{ old('prix_achat') }}" min="0">
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Valeur estimée ({{ $sym }})</label>
                <input type="number" name="valeur_estimee" class="form-control" value="{{ old('valeur_estimee') }}" min="0">
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Année construction</label>
                <input type="number" name="annee_construction" class="form-control" value="{{ old('annee_construction') }}"
                       min="1800" max="{{ date('Y') }}">
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">DPE</label>
                <select name="dpe" class="form-select">
                    <option value="">Non renseigné</option>
                    @foreach(['A','B','C','D','E','F','G'] as $d)
                    <option value="{{ $d }}" {{ old('dpe') === $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" rows="3" class="form-control" placeholder="Décrivez votre bien...">{{ old('description') }}</textarea>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Photos</label>
            <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Ajouter le bien
            </button>
            <a href="{{ route('biens.index') }}" class="btn btn-outline-secondary">Annuler</a>
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
