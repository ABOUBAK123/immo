@extends('layouts.app')
@section('title', 'Bail — ' . $location->bien->titre)
@php $sym = auth()->user()->deviseSymbole(); @endphp

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="{{ route('locations.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Locations
        </a>
        <h4 class="fw-bold mb-0">{{ $location->bien->titre }}</h4>
        <p class="text-muted small mb-0">Bail {{ $location->type_bail }} — {{ $location->locataire->name }}</p>
    </div>
    <span class="badge fs-6 {{ $location->statut === 'actif' ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
        {{ ucfirst($location->statut) }}
    </span>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Infos bail --}}
        <div class="card p-4 mb-3">
            <h6 class="fw-bold mb-3">Détails du contrat</h6>
            <div class="row g-3 small">
                <div class="col-sm-4"><strong>Loyer :</strong> {{ number_format($location->loyer_mensuel, 0, ',', ' ') }} {{ $sym }}/mois</div>
                <div class="col-sm-4"><strong>Charges locatives :</strong> {{ number_format($location->charges, 0, ',', ' ') }} {{ $sym }}/mois</div>
                <div class="col-sm-4"><strong>Frais d'agence :</strong>
                    @if($location->frais_agence > 0)
                        {{ $location->frais_agence }}% — {{ number_format($location->montant_frais_agence, 0, ',', ' ') }} {{ $sym }}/mois
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
                <div class="col-sm-4"><strong>Total locataire :</strong> <span class="text-primary fw-bold">{{ number_format($location->montant_total, 0, ',', ' ') }} {{ $sym }}/mois</span></div>
                <div class="col-sm-4"><strong>Dépôt garantie :</strong> {{ number_format($location->depot_garantie, 0, ',', ' ') }} {{ $sym }}</div>
                <div class="col-sm-4"><strong>Début :</strong> {{ $location->date_debut->format('d/m/Y') }}</div>
                <div class="col-sm-4"><strong>Fin :</strong> {{ $location->date_fin ? $location->date_fin->format('d/m/Y') : 'Sans terme fixe' }}</div>
            </div>
            @if($location->conditions_particulieres)
            <hr>
            <p class="text-muted small mb-0"><strong>Conditions :</strong> {{ $location->conditions_particulieres }}</p>
            @endif
        </div>

        {{-- Paiements --}}
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Suivi des paiements</h6>
            @if($location->paiements->count())
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Échéance</th><th>Montant</th><th>Statut</th><th>Paiement</th><th>Quittance</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($location->paiements->sortBy('date_echeance') as $p)
                    <tr class="{{ $p->isEnRetard() ? 'table-danger' : '' }}">
                        <td>{{ $p->date_echeance->format('d/m/Y') }}</td>
                        <td>{{ number_format($p->montant, 0, ',', ' ') }} {{ $sym }}</td>
                        <td>
                            <span class="badge {{ $p->statut === 'paye' ? 'bg-success' : ($p->statut === 'en_retard' || $p->isEnRetard() ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ $p->isEnRetard() && $p->statut === 'en_attente' ? 'en retard' : $p->statut }}
                            </span>
                        </td>
                        <td>{{ $p->date_paiement ? $p->date_paiement->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if($p->quittance)
                            <span class="badge bg-success-subtle text-success">
                                <i class="bi bi-check-circle me-1"></i>{{ $p->quittance->numero }}
                            </span>
                            @else — @endif
                        </td>
                        <td>
                            @if($p->statut !== 'paye' && in_array(auth()->user()->role, ['admin','proprietaire']))
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalPayer{{ $p->id }}">
                                <i class="bi bi-check me-1"></i> Encaisser
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else<p class="text-muted small">Aucun paiement enregistré.</p>@endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-3 mb-3">
            <h6 class="fw-bold mb-3">Locataire</h6>
            <p class="mb-1 fw-semibold">{{ $location->locataire->name }}</p>
            <p class="text-muted small mb-0">{{ $location->locataire->email }}</p>
            @if($location->locataire->phone)
            <p class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $location->locataire->phone }}</p>
            @endif
        </div>
        <div class="card p-3">
            <h6 class="fw-bold mb-3">Bien</h6>
            <p class="mb-1 fw-semibold">{{ $location->bien->titre }}</p>
            <p class="text-muted small">{{ $location->bien->adresse }}, {{ $location->bien->ville }}</p>
            <a href="{{ route('biens.show', $location->bien) }}" class="btn btn-sm btn-outline-primary">Voir le bien</a>
        </div>
    </div>
</div>

{{-- Modals paiement --}}
@foreach($location->paiements as $p)
@if($p->statut !== 'paye')
<div class="modal fade" id="modalPayer{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Encaisser le paiement</h6></div>
            <form method="POST" action="{{ route('paiements.payer', $p) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p class="mb-3">Échéance : <strong>{{ $p->date_echeance->format('d/m/Y') }}</strong> — <strong>{{ number_format($p->montant, 0, ',', ' ') }} {{ $sym }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Date de paiement</label>
                        <input type="date" name="date_paiement" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Méthode</label>
                        <select name="methode_paiement" class="form-select form-select-sm" required>
                            <option value="virement">Virement</option>
                            <option value="cheque">Chèque</option>
                            <option value="especes">Espèces</option>
                            <option value="prelevement">Prélèvement</option>
                            <option value="cb">Carte bancaire</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Référence (optionnel)</label>
                        <input type="text" name="reference" class="form-control form-control-sm" placeholder="N° de chèque, virement...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-success">Valider</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection
