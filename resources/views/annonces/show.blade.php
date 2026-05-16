@extends('layouts.app')
@section('title', $annonce->titre)
@php $sym = auth()->check() ? auth()->user()->deviseSymbole() : 'FCFA'; @endphp

@section('content')
<div class="mb-3">
    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour aux annonces
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-0 overflow-hidden">
            {{-- Photos --}}
            @if($annonce->photos && count($annonce->photos))
            <div id="carousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach($annonce->photos as $i => $photo)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <img src="{{ asset('storage/'.$photo) }}" class="d-block w-100" style="height:380px;object-fit:cover" alt="">
                    </div>
                    @endforeach
                </div>
                @if(count($annonce->photos) > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#carousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
                @endif
            </div>
            @else
            <div class="bg-light d-flex align-items-center justify-content-center" style="height:300px">
                <i class="bi bi-house text-muted" style="font-size:4rem"></i>
            </div>
            @endif

            <div class="p-4">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge {{ $annonce->type === 'vente' ? 'bg-success' : 'bg-primary' }} fs-6 px-3 py-2">
                        {{ ucfirst($annonce->type) }}
                    </span>
                    <span class="badge bg-light text-dark fs-6 px-3 py-2">{{ $annonce->bien->type }}</span>
                    @if($annonce->prix_negociable)
                    <span class="badge bg-warning text-dark">Prix négociable</span>
                    @endif
                </div>

                <h3 class="fw-bold">{{ $annonce->titre }}</h3>
                <p class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $annonce->bien->adresse }}, {{ $annonce->bien->ville }} {{ $annonce->bien->code_postal }}</p>

                {{-- Caractéristiques --}}
                <div class="row g-2 my-3">
                    @if($annonce->bien->surface)
                    <div class="col-auto">
                        <div class="border rounded px-3 py-2 text-center">
                            <div class="fw-bold">{{ $annonce->bien->surface }} m²</div>
                            <div class="text-muted small">Surface</div>
                        </div>
                    </div>
                    @endif
                    @if($annonce->bien->nb_pieces)
                    <div class="col-auto">
                        <div class="border rounded px-3 py-2 text-center">
                            <div class="fw-bold">{{ $annonce->bien->nb_pieces }}</div>
                            <div class="text-muted small">Pièces</div>
                        </div>
                    </div>
                    @endif
                    @if($annonce->bien->nb_chambres)
                    <div class="col-auto">
                        <div class="border rounded px-3 py-2 text-center">
                            <div class="fw-bold">{{ $annonce->bien->nb_chambres }}</div>
                            <div class="text-muted small">Chambres</div>
                        </div>
                    </div>
                    @endif
                    @if($annonce->bien->etage !== null)
                    <div class="col-auto">
                        <div class="border rounded px-3 py-2 text-center">
                            <div class="fw-bold">{{ $annonce->bien->etage === 0 ? 'RDC' : $annonce->bien->etage }}</div>
                            <div class="text-muted small">Étage</div>
                        </div>
                    </div>
                    @endif
                    @if($annonce->bien->meuble)
                    <div class="col-auto">
                        <div class="border rounded px-3 py-2 text-center bg-info-subtle">
                            <i class="bi bi-check-circle-fill text-info"></i>
                            <div class="text-muted small">Meublé</div>
                        </div>
                    </div>
                    @endif
                    @if($annonce->bien->dpe)
                    <div class="col-auto">
                        <div class="border rounded px-3 py-2 text-center">
                            <div class="fw-bold">{{ $annonce->bien->dpe }}</div>
                            <div class="text-muted small">DPE</div>
                        </div>
                    </div>
                    @endif
                </div>

                @if($annonce->description)
                <h6 class="fw-bold mt-4 mb-2">Description</h6>
                <p class="text-muted">{{ $annonce->description }}</p>
                @endif

                @if($annonce->date_disponibilite)
                <p class="small text-muted mt-3">
                    <i class="bi bi-calendar3 me-1"></i>
                    Disponible à partir du <strong>{{ $annonce->date_disponibilite->format('d/m/Y') }}</strong>
                </p>
                @endif
            </div>
        </div>

        {{-- Annonces similaires --}}
        @if($similaires->count())
        <h6 class="fw-bold mt-4 mb-3">Annonces similaires</h6>
        <div class="row g-3">
            @foreach($similaires as $s)
            <div class="col-sm-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <span class="badge {{ $s->type === 'vente' ? 'bg-success' : 'bg-primary' }} mb-1">{{ $s->type }}</span>
                        <h6 class="mb-1 small fw-bold">{{ Str::limit($s->titre, 40) }}</h6>
                        <p class="text-primary fw-bold mb-1">{{ number_format($s->prix, 0, ',', ' ') }} {{ $sym }}</p>
                        <a href="{{ route('annonces.show', $s) }}" class="btn btn-sm btn-outline-primary w-100">Voir</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Colonne contact --}}
    <div class="col-lg-4">
        <div class="card p-4 sticky-top" style="top:80px">
            <div class="text-center mb-3">
                <span class="display-5 fw-bold text-primary">
                    {{ number_format($annonce->prix, 0, ',', ' ') }} {{ $sym }}
                </span>
                <span class="text-muted">{{ $annonce->type === 'location' ? '/ mois' : '' }}</span>
            </div>

            @auth
            <div class="d-grid gap-2">
                <a href="mailto:{{ optional($annonce->agent ?? $annonce->bien->proprietaire)->email }}"
                   class="btn btn-primary">
                    <i class="bi bi-envelope me-1"></i> Contacter
                </a>
                <a href="tel:{{ optional($annonce->agent ?? $annonce->bien->proprietaire)->phone }}"
                   class="btn btn-outline-primary">
                    <i class="bi bi-telephone me-1"></i> Appeler
                </a>
            </div>
            @else
            <a href="{{ route('login') }}" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter pour contacter
            </a>
            @endauth

            @if($annonce->agent)
            <hr>
            <p class="small text-muted mb-1">Agent :</p>
            <p class="fw-semibold mb-0">{{ $annonce->agent->name }}</p>
            @endif

            <hr>
            <div class="text-muted small">
                <p class="mb-1"><i class="bi bi-eye me-1"></i>{{ $annonce->vues }} vues</p>
                <p class="mb-0"><i class="bi bi-calendar me-1"></i>Publiée le {{ $annonce->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
