@extends('layouts.app')
@section('title', $user->name . ' — Locataire')
@section('page-title', 'Fiche locataire')

@section('topbar-actions')
<div style="display:flex;gap:8px">
    <a href="{{ route('admin.locataires') }}" class="btn-ghost">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
          onsubmit="return confirm('Supprimer définitivement ce compte ?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn-ghost" style="color:#DC2626;border-color:#FECDD3">
            <i class="bi bi-trash"></i> Supprimer
        </button>
    </form>
</div>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start">

    {{-- Colonne profil ───────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Identité --}}
        <div class="card-immo" style="padding:24px;text-align:center">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#F3E8FF,#EDE9FE);color:#7C3AED;
                        display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;margin:0 auto 14px">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h2 style="font-size:1rem;font-weight:700;margin-bottom:4px">{{ $user->name }}</h2>
            <span class="badge-pill" style="background:#F3E8FF;color:#7C3AED;margin-bottom:16px">Locataire</span>

            <div style="text-align:left;display:flex;flex-direction:column;gap:8px;margin-top:12px">
                <div style="display:flex;gap:10px;font-size:.82rem;align-items:center">
                    <i class="bi bi-envelope" style="color:#9CA3AF;width:16px;flex-shrink:0"></i>
                    <span style="word-break:break-all">{{ $user->email }}</span>
                </div>
                @if($user->phone)
                <div style="display:flex;gap:10px;font-size:.82rem;align-items:center">
                    <i class="bi bi-telephone" style="color:#9CA3AF;width:16px;flex-shrink:0"></i>
                    <span>{{ $user->phone }}</span>
                </div>
                @endif
                <div style="display:flex;gap:10px;font-size:.82rem;align-items:center">
                    <i class="bi bi-calendar3" style="color:#9CA3AF;width:16px;flex-shrink:0"></i>
                    <span>Inscrit le {{ $user->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Résumé financier --}}
        <div class="card-immo" style="padding:16px 20px">
            <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;margin-bottom:14px">
                Résumé
            </div>
            <div style="display:flex;flex-direction:column;gap:10px">
                @php
                    $locAct = $stats['location_actuelle'];
                @endphp
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:#F9FAFB;border-radius:8px;font-size:.8rem">
                    <span style="color:#6B7280"><i class="bi bi-key me-2" style="color:#7C3AED"></i>Baux au total</span>
                    <strong>{{ $stats['nb_locations'] }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:#F9FAFB;border-radius:8px;font-size:.8rem">
                    <span style="color:#6B7280"><i class="bi bi-check-circle me-2" style="color:#16A34A"></i>Total payé</span>
                    <strong style="color:#16A34A">{{ number_format($stats['total_paye'],0,',',' ') }} €</strong>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:#F9FAFB;border-radius:8px;font-size:.8rem">
                    <span style="color:#6B7280"><i class="bi bi-exclamation-triangle me-2" style="color:#DC2626"></i>Retards</span>
                    <strong style="color:{{ $stats['nb_retards'] > 0 ? '#DC2626' : '#16A34A' }}">
                        {{ $stats['nb_retards'] }} paiement(s)
                    </strong>
                </div>
                @if($locAct)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:#F0FDF4;border-radius:8px;font-size:.8rem">
                    <span style="color:#6B7280"><i class="bi bi-house-check me-2" style="color:#16A34A"></i>Bail actif</span>
                    <span class="badge-pill badge-success">Oui</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Colonne principale ───────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:20px">

        {{-- Logement actuel --}}
        @if($stats['location_actuelle'])
        @php $loc = $stats['location_actuelle']; @endphp
        <div class="card-immo">
            <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6">
                <span style="font-size:.88rem;font-weight:700">
                    <i class="bi bi-house-door me-2" style="color:#16A34A"></i>Logement actuel
                </span>
            </div>
            <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
                <div>
                    <div style="font-size:.7rem;color:#9CA3AF;font-weight:600;text-transform:uppercase;margin-bottom:4px">Bien</div>
                    <div style="font-size:.88rem;font-weight:700">{{ optional($loc->bien)->titre ?? '—' }}</div>
                    <div style="font-size:.75rem;color:#9CA3AF"><i class="bi bi-geo-alt me-1"></i>{{ optional($loc->bien)->ville ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.7rem;color:#9CA3AF;font-weight:600;text-transform:uppercase;margin-bottom:4px">Loyer mensuel</div>
                    <div style="font-size:1.2rem;font-weight:800;color:#2563EB">{{ number_format($loc->loyer_mensuel + $loc->charges,0,',',' ') }} €</div>
                    <div style="font-size:.72rem;color:#9CA3AF">dont {{ number_format($loc->charges,0,',',' ') }} € charges</div>
                </div>
                <div>
                    <div style="font-size:.7rem;color:#9CA3AF;font-weight:600;text-transform:uppercase;margin-bottom:4px">Durée</div>
                    <div style="font-size:.88rem;font-weight:600">depuis {{ $loc->date_debut->format('d/m/Y') }}</div>
                    <div style="font-size:.72rem;color:#9CA3AF">{{ $loc->date_debut->diffForHumans(['options' => \Carbon\Carbon::ONE_DAY_WORDS]) }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Historique des baux --}}
        <div class="card-immo">
            <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:.88rem;font-weight:700">
                    <i class="bi bi-file-earmark-text me-2" style="color:#7C3AED"></i>Historique des baux
                </span>
                <span class="badge-pill badge-gray">{{ $user->locations->count() }}</span>
            </div>

            @forelse($user->locations as $loc)
            <div style="padding:14px 20px;border-bottom:1px solid #F9FAFB">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
                    <div style="flex:1">
                        <div style="font-weight:600;font-size:.85rem;margin-bottom:4px">{{ optional($loc->bien)->titre ?? '—' }}</div>
                        <div style="font-size:.75rem;color:#9CA3AF;margin-bottom:8px">
                            <i class="bi bi-geo-alt me-1"></i>{{ optional($loc->bien)->ville ?? '—' }}
                            &nbsp;·&nbsp;
                            {{ $loc->date_debut->format('d/m/Y') }}
                            @if($loc->date_fin) → {{ $loc->date_fin->format('d/m/Y') }} @endif
                        </div>
                        {{-- Mini timeline paiements --}}
                        @if($loc->paiements->count())
                        <div style="display:flex;flex-wrap:wrap;gap:4px">
                            @foreach($loc->paiements->sortBy('date_echeance') as $p)
                            <span title="{{ $p->date_echeance->format('d/m/Y') }} — {{ number_format($p->montant,0,',',' ') }} €"
                                  style="width:20px;height:20px;border-radius:4px;font-size:.6rem;display:inline-flex;align-items:center;justify-content:center;font-weight:700;
                                         background:{{ $p->statut==='paye' ? '#DCFCE7' : ($p->statut==='en_attente' && $p->date_echeance->isPast() ? '#FEE2E2' : '#FEF3C7') }};
                                         color:{{ $p->statut==='paye' ? '#15803D' : ($p->statut==='en_attente' && $p->date_echeance->isPast() ? '#B91C1C' : '#B45309') }}">
                                {{ $p->date_echeance->format('m') }}
                            </span>
                            @endforeach
                        </div>
                        <div style="font-size:.68rem;color:#9CA3AF;margin-top:4px">
                            <span style="color:#15803D">■</span> Payé &nbsp;
                            <span style="color:#B91C1C">■</span> En retard &nbsp;
                            <span style="color:#B45309">■</span> En attente
                        </div>
                        @endif
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <span class="badge-pill {{ $loc->statut === 'actif' ? 'badge-success' : ($loc->statut === 'en_attente' ? 'badge-warning' : 'badge-gray') }}">
                            {{ ucfirst($loc->statut) }}
                        </span>
                        <div style="font-size:.78rem;font-weight:700;color:#2563EB;margin-top:6px">
                            {{ number_format($loc->loyer_mensuel + $loc->charges,0,',',' ') }} €/mois
                        </div>
                        <a href="{{ route('locations.show', $loc) }}" style="font-size:.72rem;color:#2563EB;text-decoration:none">Voir bail →</a>
                    </div>
                </div>
            </div>
            @empty
            <div style="padding:32px;text-align:center;color:#9CA3AF;font-size:.82rem">Aucun bail enregistré.</div>
            @endforelse
        </div>

    </div>
</div>
@endsection
