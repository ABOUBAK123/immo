@extends('layouts.app')
@section('title', 'Locataires')
@section('page-title', 'Locataires')

@section('topbar-actions')
<a href="{{ route('locataires.create') }}" class="btn-primary-immo">
    <i class="bi bi-person-plus"></i> Nouveau locataire
</a>
@endsection

@section('content')

@if(session('success'))
<div class="alert-immo alert-immo-success mb-4">
    <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

<div class="card-immo">
    <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;gap:12px">
        <form method="GET" style="display:flex;gap:10px;flex:1">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un locataire…"
                   class="form-control-immo" style="max-width:280px">
            <button type="submit" class="btn-ghost">
                <i class="bi bi-search"></i> Rechercher
            </button>
            @if(request('q'))
            <a href="{{ route('locataires.index') }}" class="btn-ghost">Réinitialiser</a>
            @endif
        </form>
        <span style="font-size:.8rem;color:#9CA3AF">{{ $locataires->total() }} locataire(s)</span>
    </div>

    @if($locataires->count())
    <table class="table-immo">
        <thead>
            <tr>
                <th>Locataire</th>
                <th>Contact</th>
                <th>Locations</th>
                <th>Logement actuel</th>
                <th>Statut</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($locataires as $locataire)
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:36px;height:36px;border-radius:50%;background:#DBEAFE;color:#1D4ED8;display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($locataire->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem">{{ $locataire->name }}</div>
                        <div style="font-size:.72rem;color:#9CA3AF">Depuis {{ $locataire->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div style="font-size:.8rem">{{ $locataire->email }}</div>
                @if($locataire->phone)
                <div style="font-size:.75rem;color:#9CA3AF">{{ $locataire->phone }}</div>
                @endif
            </td>
            <td>
                <span class="badge-pill badge-gray">{{ $locataire->locations_count }} bail(s)</span>
            </td>
            <td>
                @if($locataire->locations->first())
                    @php $loc = $locataire->locations->first(); @endphp
                    <div style="font-size:.8rem;font-weight:500">{{ Str::limit($loc->bien->titre, 25) }}</div>
                    <div style="font-size:.72rem;color:#9CA3AF">{{ number_format($loc->loyer_mensuel, 0, ',', ' ') }} €/mois</div>
                @else
                    <span style="color:#9CA3AF;font-size:.8rem">—</span>
                @endif
            </td>
            <td>
                @if($locataire->locations->first())
                <span class="badge-pill badge-success">Actif</span>
                @else
                <span class="badge-pill badge-gray">Sans bail</span>
                @endif
            </td>
            <td>
                <div style="display:flex;gap:6px;justify-content:flex-end">
                    <a href="{{ route('locataires.show', $locataire) }}" class="btn-ghost" style="padding:5px 10px;font-size:.75rem">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('locataires.edit', $locataire) }}" class="btn-ghost" style="padding:5px 10px;font-size:.75rem">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="{{ route('locataires.destroy', $locataire) }}"
                          onsubmit="return confirm('Supprimer ce locataire ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-ghost" style="padding:5px 10px;font-size:.75rem;color:#DC2626">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($locataires->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #F3F4F6">
        {{ $locataires->links() }}
    </div>
    @endif

    @else
    <div style="padding:60px;text-align:center">
        <i class="bi bi-people" style="font-size:2.5rem;color:#E5E7EB"></i>
        <p style="color:#9CA3AF;margin:16px 0 20px;font-size:.88rem">Aucun locataire trouvé.</p>
        <a href="{{ route('locataires.create') }}" class="btn-primary-immo">
            <i class="bi bi-person-plus"></i> Ajouter un locataire
        </a>
    </div>
    @endif
</div>
@endsection
