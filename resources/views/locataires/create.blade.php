@extends('layouts.app')
@section('title', 'Nouveau locataire')
@section('page-title', 'Nouveau locataire')

@section('topbar-actions')
<a href="{{ route('locataires.index') }}" class="btn-ghost">
    <i class="bi bi-arrow-left"></i> Retour
</a>
@endsection

@section('content')
<div style="max-width:600px;margin:0 auto">
    <div class="card-immo" style="padding:28px 32px">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid #F3F4F6">
            <i class="bi bi-person-plus me-2" style="color:#2563EB"></i>Informations du locataire
        </h2>

        @if($errors->any())
        <div class="alert-immo alert-immo-warning mb-4">
            <i class="bi bi-exclamation-triangle fs-5 flex-shrink-0"></i>
            <ul style="margin:0;padding-left:16px">
                @foreach($errors->all() as $e)<li style="font-size:.83rem">{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('locataires.store') }}">
            @csrf

            <div class="mb-4">
                <label class="form-label-immo">Nom complet <span style="color:#DC2626">*</span></label>
                <input type="text" name="name" class="form-control-immo @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="Jean Dupont" required>
                @error('name')<div style="color:#DC2626;font-size:.78rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label-immo">Adresse e-mail <span style="color:#DC2626">*</span></label>
                <input type="email" name="email" class="form-control-immo @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="jean.dupont@email.com" required>
                @error('email')<div style="color:#DC2626;font-size:.78rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label-immo">Téléphone</label>
                <input type="text" name="phone" class="form-control-immo @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}" placeholder="06 12 34 56 78">
                @error('phone')<div style="color:#DC2626;font-size:.78rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label-immo">Mot de passe temporaire <span style="color:#DC2626">*</span></label>
                <input type="password" name="password" class="form-control-immo @error('password') is-invalid @enderror"
                       placeholder="Min. 8 caractères" required>
                <div style="font-size:.75rem;color:#9CA3AF;margin-top:4px">
                    Le locataire pourra le modifier après sa première connexion.
                </div>
                @error('password')<div style="color:#DC2626;font-size:.78rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex;gap:12px;margin-top:28px;padding-top:20px;border-top:1px solid #F3F4F6">
                <button type="submit" class="btn-primary-immo" style="flex:1;justify-content:center">
                    <i class="bi bi-person-check"></i> Créer le locataire
                </button>
                <a href="{{ route('locataires.index') }}" class="btn-ghost" style="flex:1;justify-content:center">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
