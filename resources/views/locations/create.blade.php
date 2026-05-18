@extends('layouts.app')
@section('title', 'Nouveau bail')
@php $sym = auth()->user()->deviseSymbole(); @endphp

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Créer un bail</h4>
    <p class="text-muted small">Enregistrez un contrat de location et générez les paiements automatiquement</p>
</div>

<div class="card p-4" style="max-width:720px">
    <form method="POST" action="{{ route('locations.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Bien loué <span class="text-danger">*</span></label>
            <select name="bien_id" class="form-select @error('bien_id') is-invalid @enderror" required>
                <option value="">Sélectionner un bien disponible...</option>
                @foreach($biens as $b)
                <option value="{{ $b->id }}" {{ (old('bien_id', $bien?->id) == $b->id) ? 'selected' : '' }}>
                    {{ $b->titre }} — {{ $b->ville }}
                </option>
                @endforeach
            </select>
            @error('bien_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Locataire <span class="text-danger">*</span></label>
            <select name="locataire_id" class="form-select @error('locataire_id') is-invalid @enderror" required>
                <option value="">Sélectionner un locataire...</option>
                @foreach($locataires as $l)
                <option value="{{ $l->id }}" {{ old('locataire_id') == $l->id ? 'selected' : '' }}>
                    {{ $l->name }} ({{ $l->email }})
                </option>
                @endforeach
            </select>
            @error('locataire_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Type de bail <span class="text-danger">*</span></label>
                <select name="type_bail" class="form-select" required>
                    <option value="vide"     {{ old('type_bail') === 'vide'     ? 'selected' : '' }}>Vide</option>
                    <option value="meuble"   {{ old('type_bail') === 'meuble'   ? 'selected' : '' }}>Meublé</option>
                    <option value="etudiant" {{ old('type_bail') === 'etudiant' ? 'selected' : '' }}>Étudiant</option>
                    <option value="mobilite" {{ old('type_bail') === 'mobilite' ? 'selected' : '' }}>Mobilité</option>
                </select>
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Date de début <span class="text-danger">*</span></label>
                <input type="date" name="date_debut" class="form-control @error('date_debut') is-invalid @enderror"
                       value="{{ old('date_debut', date('Y-m-d')) }}" required>
                @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Date de fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin') }}">
                <div class="form-text">Laisser vide pour un bail sans terme fixe</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Loyer mensuel ({{ $sym }}) <span class="text-danger">*</span></label>
                <input type="number" id="loyer_mensuel" name="loyer_mensuel"
                       class="form-control @error('loyer_mensuel') is-invalid @enderror"
                       value="{{ old('loyer_mensuel') }}" min="0" step="0.01" required
                       oninput="calculerTotal()">
                @error('loyer_mensuel')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Charges locatives ({{ $sym }})</label>
                <input type="number" id="charges" name="charges" class="form-control"
                       value="{{ old('charges', 0) }}" min="0" step="0.01"
                       oninput="calculerTotal()">
                <div class="form-text">Entretien parties communes, eau, ordures…</div>
            </div>
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Dépôt de garantie ({{ $sym }})</label>
                <input type="number" name="depot_garantie" class="form-control" value="{{ old('depot_garantie', 0) }}" min="0" step="0.01">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <label class="form-label fw-semibold">Frais d'agence (%)</label>
                <div class="input-group">
                    <input type="number" id="frais_agence" name="frais_agence"
                           class="form-control @error('frais_agence') is-invalid @enderror"
                           value="{{ old('frais_agence', 0) }}" min="0" max="100" step="0.01"
                           oninput="calculerTotal()">
                    <span class="input-group-text">%</span>
                </div>
                <div class="form-text">% du loyer mensuel reversé à l'agence</div>
                @error('frais_agence')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-8">
                <label class="form-label fw-semibold">Total mensuel locataire</label>
                <div id="totalMensuel"
                     style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;
                            padding:10px 14px;font-weight:700;font-size:1rem;color:#15803D">
                    — {{ $sym }}/mois
                </div>
                <div class="form-text">Loyer + Charges + Frais d'agence</div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Conditions particulières</label>
            <textarea name="conditions_particulieres" rows="3" class="form-control"
                      placeholder="Ex: Animaux autorisés, parking inclus...">{{ old('conditions_particulieres') }}</textarea>
        </div>

        <div class="alert alert-info small">
            <i class="bi bi-info-circle me-1"></i>
            La création du bail génère automatiquement <strong>12 paiements mensuels</strong> à partir de la date de début.
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-file-earmark-check me-1"></i> Créer le bail
            </button>
            <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
function calculerTotal() {
    const loyer  = parseFloat(document.getElementById('loyer_mensuel').value)  || 0;
    const charges = parseFloat(document.getElementById('charges').value)        || 0;
    const pct    = parseFloat(document.getElementById('frais_agence').value)    || 0;
    const frais  = Math.round(loyer * pct / 100);
    const total  = loyer + charges + frais;

    let detail = '';
    if (pct > 0) {
        detail = loyer.toLocaleString('fr-FR') + ' + ' + charges.toLocaleString('fr-FR')
               + ' + ' + frais.toLocaleString('fr-FR') + ' (agence) = ';
    }
    document.getElementById('totalMensuel').textContent =
        detail + total.toLocaleString('fr-FR') + ' {{ $sym }}/mois';
}
calculerTotal();
</script>
@endsection
