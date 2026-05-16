@extends('layouts.app')
@section('title', $user->name . ' — Agent')
@section('page-title', 'Fiche agent')

@section('topbar-actions')
<div style="display:flex;gap:8px">
    <a href="{{ route('admin.agences') }}" class="btn-ghost">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
    <form method="POST" action="{{ route('admin.agences.toggle', $user) }}"
          onsubmit="return confirm('{{ $user->statut === 'actif' ? 'Désactiver' : 'Activer' }} ce compte ?')">
        @csrf @method('PATCH')
        <button type="submit" class="btn-ghost"
                style="color:{{ $user->statut === 'actif' ? '#DC2626' : '#16A34A' }};
                       border-color:{{ $user->statut === 'actif' ? '#FECDD3' : '#BBF7D0' }}">
            <i class="bi bi-{{ $user->statut === 'actif' ? 'pause-circle' : 'play-circle' }}"></i>
            {{ $user->statut === 'actif' ? 'Désactiver le compte' : 'Activer le compte' }}
        </button>
    </form>
</div>
@endsection

@section('content')

@if(session('success'))
<div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3"
     style="background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D;font-size:.83rem">
    <i class="bi bi-check-circle-fill fs-5"></i><span>{{ session('success') }}</span>
</div>
@endif

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start">

    {{-- Profil ──────────────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card-immo" style="padding:24px;text-align:center">
            <div style="width:72px;height:72px;border-radius:50%;
                        background:{{ $user->statut === 'actif' ? 'linear-gradient(135deg,#FFEDD5,#FED7AA)' : '#F3F4F6' }};
                        color:{{ $user->statut === 'actif' ? '#EA580C' : '#9CA3AF' }};
                        display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;margin:0 auto 14px">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h2 style="font-size:1rem;font-weight:700;margin-bottom:4px">{{ $user->name }}</h2>
            <div style="display:flex;gap:6px;justify-content:center;margin-bottom:14px">
                <span class="badge-pill badge-info">Agent immobilier</span>
                <span class="badge-pill {{ $user->statut === 'actif' ? 'badge-success' : 'badge-danger' }}">
                    {{ ucfirst($user->statut) }}
                </span>
            </div>
            <div style="text-align:left;display:flex;flex-direction:column;gap:8px">
                <div style="display:flex;gap:10px;font-size:.82rem;align-items:center">
                    <i class="bi bi-envelope" style="color:#9CA3AF;width:16px"></i>
                    <span style="word-break:break-all">{{ $user->email }}</span>
                </div>
                @if($user->phone)
                <div style="display:flex;gap:10px;font-size:.82rem;align-items:center">
                    <i class="bi bi-telephone" style="color:#9CA3AF;width:16px"></i>
                    <span>{{ $user->phone }}</span>
                </div>
                @endif
                <div style="display:flex;gap:10px;font-size:.82rem;align-items:center">
                    <i class="bi bi-calendar3" style="color:#9CA3AF;width:16px"></i>
                    <span>Inscrit le {{ $user->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="card-immo" style="padding:16px 20px">
            <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;margin-bottom:14px">
                Performance
            </div>
            @php
            $rows = [
                ['Annonces publiées', $stats['nb_annonces'],  '#2563EB', 'megaphone'],
                ['Annonces actives',  $stats['nb_actives'],   '#16A34A', 'check-circle'],
                ['Vues totales',      number_format($stats['total_vues'],0,',',' '), '#F97316', 'eye'],
                ['Biens vendus/loués',$stats['nb_vendus'],    '#7C3AED', 'house-check'],
            ];
            @endphp
            @foreach($rows as [$label,$val,$color,$icon])
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border-radius:8px;background:#F9FAFB;margin-bottom:6px">
                <div style="display:flex;align-items:center;gap:8px;font-size:.78rem;color:#6B7280">
                    <i class="bi bi-{{ $icon }}" style="color:{{ $color }}"></i> {{ $label }}
                </div>
                <strong style="font-size:.82rem;color:{{ $color }}">{{ $val }}</strong>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Annonces ─────────────────────────────────────────────────────────── --}}
    <div class="card-immo">
        <div style="padding:16px 20px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:.88rem;font-weight:700">
                <i class="bi bi-megaphone me-2" style="color:#F97316"></i>Annonces publiées
            </span>
            <span class="badge-pill badge-gray">{{ $annonces->count() }} annonce(s)</span>
        </div>
        @forelse($annonces as $a)
        <div style="padding:14px 20px;display:flex;align-items:center;gap:14px;border-bottom:1px solid #F9FAFB">
            @if($a->photos && count($a->photos))
            <img src="{{ asset('storage/'.$a->photos[0]) }}"
                 style="width:56px;height:44px;border-radius:8px;object-fit:cover;flex-shrink:0" alt="">
            @else
            <div style="width:56px;height:44px;border-radius:8px;background:#F3F4F6;display:flex;align-items:center;justify-content:center;color:#D1D5DB;flex-shrink:0">
                <i class="bi bi-house"></i>
            </div>
            @endif
            <div style="flex:1;min-width:0">
                <div style="font-size:.83rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $a->titre }}</div>
                <div style="font-size:.72rem;color:#9CA3AF"><i class="bi bi-geo-alt me-1"></i>{{ $a->bien?->ville }}</div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:.88rem;font-weight:700;color:#2563EB">{{ number_format($a->prix, 0, ',', ' ') }}</div>
                <div style="font-size:.7rem;color:#9CA3AF"><i class="bi bi-eye me-1"></i>{{ $a->vues }} vues</div>
            </div>
            <span class="badge-pill {{ $a->statut === 'active' ? 'badge-success' : ($a->statut === 'vendu' || $a->statut === 'loue' ? 'badge-gray' : 'badge-warning') }}">
                {{ ucfirst($a->statut) }}
            </span>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:#9CA3AF;font-size:.83rem">Aucune annonce publiée.</div>
        @endforelse
    </div>

</div>
@endsection
