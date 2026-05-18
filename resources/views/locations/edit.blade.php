@extends('layouts.app')
@section('title', 'Modifier le bail')
@php $sym = auth()->user()->deviseSymbole(); @endphp

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Modifier le bail</h4>
</div>
<div class="card p-4" style="max-width:600px">
    <form method="POST" action="{{ route('locations.update', $location) }}">
        @csrf @method('PUT')

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Loyer mensuel ({{ $sym }})</label>
                <input type="number" name="loyer_mensuel" class="form-control"
                       value="{{ old('loyer_mensuel', $location->loyer_mensuel) }}" min="0" step="0.01" required>
            </div>
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Charges locatives ({{ $sym }})</label>
                <input type="number" name="charges" class="form-control"
                       value="{{ old('charges', $location->charges) }}" min="0" step="0.01">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Frais d'agence (%)</label>
                <div class="input-group">
                    <input type="number" name="frais_agence" class="form-control"
                           value="{{ old('frais_agence', $location->frais_agence) }}"
                           min="0" max="100" step="0.01">
                    <span class="input-group-text">%</span>
                </div>
                <div class="form-text">% du loyer mensuel reversé à l'agence</div>
            </div>
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Total mensuel actuel</label>
                <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;
                            padding:10px 14px;font-weight:700;color:#15803D">
                    {{ number_format($location->montant_total, 0, ',', ' ') }} {{ $sym }}/mois
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Statut</label>
                <select name="statut" class="form-select">
                    @foreach(['en_attente','actif','resilie','termine'] as $s)
                    <option value="{{ $s }}" {{ $location->statut === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6">
                <label class="form-label fw-semibold">Date de fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', $location->date_fin?->format('Y-m-d')) }}">
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-floppy me-1"></i> Enregistrer</button>
            <a href="{{ route('locations.show', $location) }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
