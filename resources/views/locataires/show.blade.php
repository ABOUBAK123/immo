@extends('layouts.app')
@section('title', $locataire->name)
@section('page-title', 'Fiche locataire')

@section('topbar-actions')
<div style="display:flex;gap:8px">
    <a href="{{ route('locataires.edit', $locataire) }}" class="btn-ghost">
        <i class="bi bi-pencil"></i> Modifier
    </a>
    <a href="{{ route('locataires.index') }}" class="btn-ghost">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start">

    {{-- Profil --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card-immo" style="padding:24px;text-align:center">
            <div style="width:72px;height:72px;border-radius:50%;background:#DBEAFE;color:#1D4ED8;display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:700;margin:0 auto 16px">
                {{ strtoupper(substr($locataire->name, 0, 1)) }}
            </div>
            <h2 style="font-size:1rem;font-weight:700;margin-bottom:4px">{{ $locataire->name }}</h2>
            <p style="color:#9CA3AF;font-size:.8rem;margin-bottom:16px">Locataire depuis {{ $locataire->created_at->diffForHumans() }}</p>
            <div style="display:flex;flex-direction:column;gap:8px;text-align:left">
                <div style="display:flex;align-items:center;gap:10px;font-size:.83rem">
                    <i class="bi bi-envelope" style="color:#9CA3AF;width:16px"></i>
                    <span>{{ $locataire->email }}</span>
                </div>
                @if($locataire->phone)
                <div style="display:flex;align-items:center;gap:10px;font-size:.83rem">
                    <i class="bi bi-telephone" style="color:#9CA3AF;width:16px"></i>
                    <span>{{ $locataire->phone }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="card-immo" style="padding:16px">
            <div style="font-size:.78rem;font-weight:700;margin-bottom:12px;color:#6B7280">ACTIONS</div>
            <div style="display:flex;flex-direction:column;gap:8px">
                <a href="{{ route('locations.create') }}?locataire_id={{ $locataire->id }}" class="btn-primary-immo" style="justify-content:center;width:100%">
                    <i class="bi bi-file-earmark-plus"></i> Créer un bail
                </a>
                <a href="{{ route('interventions.create') }}?locataire_id={{ $locataire->id }}" class="btn-ghost" style="justify-content:center;width:100%">
                    <i class="bi bi-tools"></i> Déclarer une intervention
                </a>
                <form method="POST" action="{{ route('locataires.destroy', $locataire) }}"
                      onsubmit="return confirm('Supprimer définitivement ce locataire ?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="width:100%;padding:8px 16px;border-radius:8px;border:1px solid #FCA5A5;background:#FFF1F2;color:#DC2626;font-size:.82rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                        <i class="bi bi-trash"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Contenu principal --}}
    <div style="display:flex;flex-direction:column;gap:20px">

        {{-- Locations --}}
        <div class="card-immo">
            <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:.88rem;font-weight:700"><i class="bi bi-key me-2" style="color:#2563EB"></i>Baux</span>
                <span class="badge-pill badge-gray">{{ $locataire->locations->count() }} bail(s)</span>
            </div>

            @forelse($locataire->locations as $loc)
            <div style="padding:16px 20px;border-bottom:1px solid #F9FAFB">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
                    <div style="flex:1">
                        <div style="font-weight:600;font-size:.88rem;margin-bottom:4px">{{ optional($loc->bien)->titre ?? '—' }}</div>
                        <div style="font-size:.78rem;color:#9CA3AF;margin-bottom:10px">
                            <i class="bi bi-geo-alt me-1"></i>{{ optional($loc->bien)->adresse ?? '' }}{{ $loc->bien ? ', ' . $loc->bien->ville : '' }}
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:.78rem;color:#6B7280">
                            <span><i class="bi bi-calendar3 me-1"></i>Début : {{ $loc->date_debut->format('d/m/Y') }}</span>
                            @if($loc->date_fin)
                            <span><i class="bi bi-calendar-x me-1"></i>Fin : {{ $loc->date_fin->format('d/m/Y') }}</span>
                            @endif
                            <span><i class="bi bi-currency-euro me-1"></i>{{ number_format($loc->loyer_mensuel, 0, ',', ' ') }} €/mois</span>
                            @if($loc->charges)
                            <span>+ {{ number_format($loc->charges, 0, ',', ' ') }} € charges</span>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
                        <span class="badge-pill {{ $loc->statut === 'actif' ? 'badge-success' : ($loc->statut === 'en_attente' ? 'badge-warning' : 'badge-gray') }}">
                            {{ ucfirst($loc->statut) }}
                        </span>
                        <a href="{{ route('locations.show', $loc) }}" style="font-size:.75rem;color:#2563EB;text-decoration:none">Voir le bail →</a>
                    </div>
                </div>

                {{-- Paiements de cette location --}}
                @if($loc->paiements->count())
                <div style="margin-top:14px;border-top:1px solid #F3F4F6;padding-top:12px">
                    <div style="font-size:.75rem;font-weight:600;color:#6B7280;margin-bottom:8px">Derniers paiements</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px">
                        @foreach($loc->paiements->take(6) as $p)
                        <span class="badge-pill {{ $p->statut === 'paye' ? 'badge-success' : ($p->statut === 'en_attente' && $p->date_echeance->isPast() ? 'badge-danger' : 'badge-warning') }}"
                              title="{{ $p->date_echeance->format('d/m/Y') }} — {{ number_format($p->montant, 0, ',', ' ') }} €"
                              style="font-size:.65rem">
                            {{ $p->date_echeance->format('m/y') }}
                            @if($p->statut === 'paye')<i class="bi bi-check ms-1"></i>@endif
                        </span>
                        @endforeach
                        @if($loc->paiements->count() > 6)
                        <span style="font-size:.72rem;color:#9CA3AF">+{{ $loc->paiements->count() - 6 }} autres</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div style="padding:40px;text-align:center;color:#9CA3AF;font-size:.83rem">
                <i class="bi bi-file-earmark-x" style="font-size:2rem;display:block;margin-bottom:10px;color:#E5E7EB"></i>
                Aucun bail enregistré pour ce locataire.
            </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
