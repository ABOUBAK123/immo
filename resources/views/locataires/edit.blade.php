@extends('layouts.app')
@section('title', 'Modifier ' . $locataire->name)
@section('page-title', 'Modifier le locataire')

@section('topbar-actions')
<a href="{{ route('locataires.show', $locataire) }}" class="btn-ghost">
    <i class="bi bi-arrow-left"></i> Retour
</a>
@endsection

@section('content')
<div style="max-width:600px;margin:0 auto">
    <div class="card-immo" style="padding:28px 32px">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid #F3F4F6">
            <div style="width:44px;height:44px;border-radius:50%;background:#DBEAFE;color:#1D4ED8;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;flex-shrink:0">
                {{ strtoupper(substr($locataire->name, 0, 1)) }}
            </div>
            <div>
                <h2 style="font-size:.95rem;font-weight:700;margin:0">{{ $locataire->name }}</h2>
                <p style="font-size:.78rem;color:#9CA3AF;margin:2px 0 0">{{ $locataire->email }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="alert-immo alert-immo-warning mb-4">
            <i class="bi bi-exclamation-triangle fs-5 flex-shrink-0"></i>
            <ul style="margin:0;padding-left:16px">
                @foreach($errors->all() as $e)<li style="font-size:.83rem">{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        @if(session('success'))
        <div class="alert-immo alert-immo-success mb-4">
            <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('locataires.update', $locataire) }}">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="form-label-immo">Nom complet <span style="color:#DC2626">*</span></label>
                <input type="text" name="name" class="form-control-immo @error('name') is-invalid @enderror"
                       value="{{ old('name', $locataire->name) }}" required>
                @error('name')<div style="color:#DC2626;font-size:.78rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label-immo">Téléphone</label>
                <input type="text" name="phone" class="form-control-immo @error('phone') is-invalid @enderror"
                       value="{{ old('phone', $locataire->phone) }}" placeholder="06 12 34 56 78">
                @error('phone')<div style="color:#DC2626;font-size:.78rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div style="background:#F9FAFB;border-radius:8px;padding:14px 16px;margin-bottom:24px;font-size:.8rem;color:#6B7280">
                <i class="bi bi-info-circle me-2"></i>
                Pour modifier l'adresse e-mail ou le mot de passe, le locataire doit le faire depuis son espace personnel.
            </div>

            <div style="display:flex;gap:12px;padding-top:20px;border-top:1px solid #F3F4F6">
                <button type="submit" class="btn-primary-immo" style="flex:1;justify-content:center">
                    <i class="bi bi-check-circle"></i> Enregistrer
                </button>
                <a href="{{ route('locataires.show', $locataire) }}" class="btn-ghost" style="flex:1;justify-content:center">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
