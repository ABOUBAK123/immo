@extends('layouts.app')
@section('title', 'Gestion des formules')
@section('page-title', 'Formules d\'abonnement')

@section('content')

@if(session('success'))
<div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:14px 20px;margin-bottom:20px;
            display:flex;align-items:center;gap:10px">
    <i class="bi bi-check-circle-fill" style="color:#16A34A"></i>
    <span style="font-size:.85rem;color:#15803D;font-weight:600">{{ session('success') }}</span>
</div>
@endif

{{-- Stats par formule --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px">
    @foreach($formules as $f)
    <div class="card-immo" style="padding:20px;border-top:4px solid {{ $f->couleur }}">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#9CA3AF;margin-bottom:6px">
            <i class="bi {{ $f->icone }} me-1" style="color:{{ $f->couleur }}"></i>{{ $f->nom }}
        </div>
        <div style="font-size:1.8rem;font-weight:800;color:#111827">{{ $f->abonnes_actifs }}</div>
        <div style="font-size:.75rem;color:#6B7280">abonné(s) actif(s)</div>
        <div style="font-size:.72rem;color:#9CA3AF;margin-top:4px">
            {{ $f->abonnes_total }} total · {{ number_format($f->prix_mensuel, 0, ',', ' ') }} {{ \App\Models\User::DEVISES[$f->devise]['symbole'] ?? $f->devise }}/mois
        </div>
    </div>
    @endforeach
</div>

{{-- Liste des formules --}}
<div class="card-immo" style="padding:0;overflow:hidden">
    <div style="padding:18px 24px;border-bottom:1px solid #F3F4F6;
                display:flex;align-items:center;justify-content:space-between">
        <div style="font-size:.9rem;font-weight:700;color:#111827">
            <i class="bi bi-grid-3x3-gap me-2" style="color:#9CA3AF"></i>Formules configurées
        </div>
        <a href="{{ route('admin.formules.create') }}"
           style="background:#111827;color:#fff;border-radius:8px;padding:8px 16px;
                  font-size:.8rem;font-weight:700;text-decoration:none">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle formule
        </a>
    </div>

    <table class="table-immo">
        <thead>
            <tr>
                <th>Formule</th>
                <th>Prix mensuel</th>
                <th>Limites</th>
                <th>Fonctionnalités</th>
                <th>Abonnés actifs</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($formules as $f)
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:38px;height:38px;border-radius:10px;background:{{ $f->couleur }}22;
                                color:{{ $f->couleur }};display:flex;align-items:center;justify-content:center;font-size:1.1rem">
                        <i class="bi {{ $f->icone }}"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:.88rem;color:#111827">
                            {{ $f->nom }}
                            @if($f->populaire)
                            <span style="font-size:.65rem;background:#FFF7ED;color:#EA580C;padding:1px 6px;border-radius:8px;font-weight:700;margin-left:4px">★ Populaire</span>
                            @endif
                        </div>
                        <div style="font-size:.72rem;color:#9CA3AF">{{ $f->slug }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div style="font-weight:700;font-size:.9rem;color:#111827">
                    {{ number_format($f->prix_mensuel, 0, ',', ' ') }} {{ \App\Models\User::DEVISES[$f->devise]['symbole'] ?? $f->devise }}
                </div>
                @if($f->prix_annuel)
                <div style="font-size:.72rem;color:#6B7280">{{ number_format($f->prix_annuel, 0, ',', ' ') }}/an</div>
                @endif
            </td>
            <td style="font-size:.78rem">
                <div><i class="bi bi-buildings me-1" style="color:#9CA3AF"></i>
                    <strong>{{ $f->limiteLabel('max_biens') }}</strong> biens
                </div>
                <div><i class="bi bi-people me-1" style="color:#9CA3AF"></i>
                    <strong>{{ $f->limiteLabel('max_locataires') }}</strong> locataires
                </div>
                @if($f->max_annonces !== 0)
                <div><i class="bi bi-megaphone me-1" style="color:#9CA3AF"></i>
                    <strong>{{ $f->limiteLabel('max_annonces') }}</strong> annonces
                </div>
                @endif
            </td>
            <td>
                <div style="display:flex;flex-wrap:wrap;gap:4px">
                    @foreach(['interventions' => 'tools', 'annonces' => 'megaphone', 'depenses' => 'wallet', 'ia' => 'robot', 'agents' => 'person-badge', 'notifications_sms' => 'bell'] as $feat => $icon)
                    @php $champ = "has_{$feat}"; @endphp
                    <span style="font-size:.65rem;padding:2px 6px;border-radius:6px;font-weight:700;
                                 background:{{ $f->$champ ? '#F0FDF4' : '#F9FAFB' }};
                                 color:{{ $f->$champ ? '#16A34A' : '#D1D5DB' }}">
                        <i class="bi bi-{{ $icon }}"></i>
                    </span>
                    @endforeach
                </div>
            </td>
            <td>
                <span style="font-size:1.1rem;font-weight:800;color:#111827">{{ $f->abonnes_actifs }}</span>
                <span style="font-size:.72rem;color:#9CA3AF"> / {{ $f->abonnes_total }} total</span>
            </td>
            <td>
                <form method="POST" action="{{ route('admin.formules.toggle', $f) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button type="submit"
                            style="border:none;cursor:pointer;padding:4px 12px;border-radius:8px;font-size:.75rem;font-weight:700;
                                   background:{{ $f->is_active ? '#F0FDF4' : '#FFF1F2' }};
                                   color:{{ $f->is_active ? '#16A34A' : '#DC2626' }}">
                        {{ $f->is_active ? 'Actif' : 'Inactif' }}
                    </button>
                </form>
            </td>
            <td>
                <a href="{{ route('admin.formules.edit', $f) }}"
                   style="font-size:.78rem;color:#374151;text-decoration:none;
                          background:#F9FAFB;border:1px solid #E5E7EB;padding:5px 12px;border-radius:7px;font-weight:600">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection
