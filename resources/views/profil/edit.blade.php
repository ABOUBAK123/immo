@extends('layouts.app')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@push('styles')
<style>
.profil-section {
    background:#fff;border:1px solid #E5E7EB;border-radius:16px;
    overflow:hidden;margin-bottom:20px;
}
.profil-section-header {
    padding:16px 24px;border-bottom:1px solid #F3F4F6;
    display:flex;align-items:center;gap:10px;
}
.profil-section-header h3 {
    font-size:.95rem;font-weight:700;margin:0;color:#111827;
}
.profil-section-body { padding:24px; }
.profil-field { margin-bottom:18px; }
.profil-field label {
    display:block;font-size:.8rem;font-weight:700;color:#374151;
    margin-bottom:6px;letter-spacing:.02em;
}
.profil-input {
    width:100%;padding:10px 14px;border:1.5px solid #E5E7EB;
    border-radius:10px;font-size:.88rem;font-family:inherit;
    color:#111827;outline:none;transition:border-color .15s,box-shadow .15s;
    background:#fff;
}
.profil-input:focus { border-color:#EA580C;box-shadow:0 0 0 3px rgba(234,88,12,.1); }
.profil-input.is-invalid { border-color:#DC2626; }
.field-error { font-size:.74rem;color:#DC2626;margin-top:4px; }
.avatar-ring {
    width:96px;height:96px;border-radius:50%;object-fit:cover;
    border:3px solid #FDBA74;box-shadow:0 4px 14px rgba(234,88,12,.2);
    flex-shrink:0;
}
.avatar-initials {
    width:96px;height:96px;border-radius:50%;
    background:linear-gradient(135deg,#EA580C,#F97316);
    display:flex;align-items:center;justify-content:center;
    font-size:2rem;font-weight:800;color:#fff;flex-shrink:0;
    border:3px solid #FDBA74;box-shadow:0 4px 14px rgba(234,88,12,.2);
}
.upload-zone {
    border:2px dashed #FDBA74;border-radius:12px;padding:24px;
    text-align:center;cursor:pointer;transition:.15s;background:#FFFBF7;
}
.upload-zone:hover { border-color:#EA580C;background:#FFF7ED; }
.btn-save {
    display:inline-flex;align-items:center;gap:7px;
    padding:10px 22px;border:none;border-radius:10px;
    font-size:.86rem;font-weight:700;cursor:pointer;
    background:linear-gradient(135deg,#EA580C,#F97316);
    color:#fff;transition:.15s;font-family:inherit;
}
.btn-save:hover { opacity:.9;transform:translateY(-1px); }
.btn-secondary {
    display:inline-flex;align-items:center;gap:7px;
    padding:10px 20px;border:1.5px solid #E5E7EB;border-radius:10px;
    font-size:.86rem;font-weight:600;cursor:pointer;
    background:#fff;color:#6B7280;transition:.15s;font-family:inherit;
}
.btn-secondary:hover { border-color:#EA580C;color:#EA580C; }
.strength-bar { display:flex;gap:4px;margin-top:8px; }
.strength-bar div { height:4px;border-radius:2px;flex:1;background:#E5E7EB;transition:.3s; }
.alert-profil {
    padding:12px 16px;border-radius:10px;font-size:.82rem;
    display:flex;align-items:center;gap:10px;margin-bottom:16px;
}
</style>
@endpush

@section('topbar-actions')
<a href="{{ route('dashboard') }}" class="btn-secondary" style="font-size:.8rem;padding:7px 14px;text-decoration:none;display:inline-flex;align-items:center;gap:5px">
    <i class="bi bi-arrow-left"></i> Tableau de bord
</a>
@endsection

@section('content')
@php $u = $user; @endphp

<div style="max-width:700px;margin:0 auto">

    {{-- ── En-tête profil ──────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:28px;
                background:#fff;border:1px solid #E5E7EB;border-radius:16px;padding:24px">
        @if($u->avatar)
        <img src="{{ asset('storage/'.$u->avatar) }}" alt="Avatar" class="avatar-ring">
        @else
        <div class="avatar-initials">{{ strtoupper(substr($u->name,0,1)) }}</div>
        @endif
        <div>
            <div style="font-size:1.25rem;font-weight:800;color:#111827">{{ $u->name }}</div>
            <div style="font-size:.82rem;color:#6B7280;margin-top:2px">{{ $u->email }}</div>
            <span style="display:inline-flex;align-items:center;gap:5px;margin-top:8px;
                         background:#FFF7ED;border:1px solid #FDBA74;color:#C2410C;
                         padding:3px 10px;border-radius:20px;font-size:.74rem;font-weight:700">
                <i class="bi bi-person-badge"></i> {{ ucfirst($u->role) }}
            </span>
        </div>
    </div>

    {{-- ── 1. Informations personnelles ─────────────────────────────────── --}}
    <div class="profil-section">
        <div class="profil-section-header">
            <div style="width:36px;height:36px;border-radius:9px;background:#FFF7ED;color:#EA580C;
                        display:flex;align-items:center;justify-content:center;font-size:1rem">
                <i class="bi bi-person-lines-fill"></i>
            </div>
            <h3>Informations personnelles</h3>
        </div>
        <div class="profil-section-body">
            @if(session('success_info'))
            <div class="alert-profil" style="background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D">
                <i class="bi bi-check-circle-fill"></i> {{ session('success_info') }}
            </div>
            @endif

            <form method="POST" action="{{ route('profil.update-info') }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="profil-field" style="grid-column:1/-1">
                        <label>Nom complet</label>
                        <input type="text" name="name" value="{{ old('name', $u->name) }}"
                               class="profil-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               placeholder="Nom et prénoms" required>
                        @error('name')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="profil-field" style="grid-column:1/-1">
                        <label>Adresse e-mail</label>
                        <input type="email" name="email" value="{{ old('email', $u->email) }}"
                               class="profil-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               placeholder="votre@email.com" required>
                        @error('email')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:6px">
                    <button type="submit" class="btn-save">
                        <i class="bi bi-floppy"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 2. Photo de profil ────────────────────────────────────────────── --}}
    <div class="profil-section">
        <div class="profil-section-header">
            <div style="width:36px;height:36px;border-radius:9px;background:#EFF6FF;color:#2563EB;
                        display:flex;align-items:center;justify-content:center;font-size:1rem">
                <i class="bi bi-camera-fill"></i>
            </div>
            <h3>Photo de profil</h3>
        </div>
        <div class="profil-section-body">
            @if(session('success_avatar'))
            <div class="alert-profil" style="background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D">
                <i class="bi bi-check-circle-fill"></i> {{ session('success_avatar') }}
            </div>
            @endif

            <form method="POST" action="{{ route('profil.update-avatar') }}" enctype="multipart/form-data">
                @csrf
                <div style="display:flex;align-items:center;gap:20px;margin-bottom:20px">
                    @if($u->avatar)
                    <img src="{{ asset('storage/'.$u->avatar) }}" alt="Avatar" class="avatar-ring" id="avatarPreview">
                    @else
                    <div class="avatar-initials" id="avatarInitials">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <img id="avatarPreview" style="display:none" class="avatar-ring" alt="Prévisualisation">
                    @endif
                    <div>
                        <div style="font-size:.82rem;font-weight:600;color:#374151;margin-bottom:6px">Formats acceptés</div>
                        <div style="font-size:.76rem;color:#9CA3AF">JPEG, PNG, GIF, WebP — max 2 Mo</div>
                        <div style="font-size:.76rem;color:#9CA3AF;margin-top:2px">Taille recommandée : 200×200 px</div>
                    </div>
                </div>

                <label class="upload-zone" for="avatarInput">
                    <i class="bi bi-cloud-arrow-up" style="font-size:1.8rem;color:#EA580C;display:block;margin-bottom:8px"></i>
                    <div style="font-size:.84rem;font-weight:600;color:#374151">Cliquez pour choisir une photo</div>
                    <div style="font-size:.74rem;color:#9CA3AF;margin-top:4px" id="avatarFileName">ou glissez-déposez ici</div>
                </label>
                <input type="file" id="avatarInput" name="avatar" accept="image/*"
                       style="display:none" onchange="previewAvatar(this)">
                @error('avatar')<div class="field-error" style="margin-top:6px">{{ $message }}</div>@enderror

                <div style="display:flex;justify-content:flex-end;margin-top:16px">
                    <button type="submit" class="btn-save" id="btnUpload" disabled
                            style="opacity:.5;cursor:not-allowed" id="btnUpload">
                        <i class="bi bi-upload"></i> Mettre à jour la photo
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 3. Sécurité — Mot de passe ────────────────────────────────────── --}}
    <div class="profil-section">
        <div class="profil-section-header">
            <div style="width:36px;height:36px;border-radius:9px;background:#FFF1F2;color:#DC2626;
                        display:flex;align-items:center;justify-content:center;font-size:1rem">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h3>Sécurité — Changer le mot de passe</h3>
        </div>
        <div class="profil-section-body">
            @if(session('success_pwd'))
            <div class="alert-profil" style="background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D">
                <i class="bi bi-check-circle-fill"></i> {{ session('success_pwd') }}
            </div>
            @endif

            <form method="POST" action="{{ route('profil.update-password') }}">
                @csrf
                <div class="profil-field">
                    <label>Mot de passe actuel</label>
                    <div style="position:relative">
                        <input type="password" name="current_password" id="curPwd"
                               class="profil-input {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePwd('curPwd','eyeCur')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                       background:none;border:none;color:#9CA3AF;cursor:pointer;padding:0">
                            <i class="bi bi-eye" id="eyeCur"></i>
                        </button>
                    </div>
                    @error('current_password')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="profil-field">
                        <label>Nouveau mot de passe</label>
                        <div style="position:relative">
                            <input type="password" name="password" id="newPwd"
                                   class="profil-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                   placeholder="Min. 8 caractères"
                                   oninput="checkStrength(this.value)">
                            <button type="button" onclick="togglePwd('newPwd','eyeNew')"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                           background:none;border:none;color:#9CA3AF;cursor:pointer;padding:0">
                                <i class="bi bi-eye" id="eyeNew"></i>
                            </button>
                        </div>
                        <div class="strength-bar" id="strengthBar">
                            <div id="s1"></div><div id="s2"></div><div id="s3"></div><div id="s4"></div>
                        </div>
                        <div id="strengthLabel" style="font-size:.7rem;color:#9CA3AF;margin-top:3px"></div>
                        @error('password')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="profil-field">
                        <label>Confirmer le mot de passe</label>
                        <div style="position:relative">
                            <input type="password" name="password_confirmation" id="confPwd"
                                   class="profil-input"
                                   placeholder="Répéter le mot de passe">
                            <button type="button" onclick="togglePwd('confPwd','eyeConf')"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                           background:none;border:none;color:#9CA3AF;cursor:pointer;padding:0">
                                <i class="bi bi-eye" id="eyeConf"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;margin-top:6px">
                    <button type="submit" class="btn-save" style="background:linear-gradient(135deg,#DC2626,#EF4444)">
                        <i class="bi bi-shield-check"></i> Changer le mot de passe
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('avatarFileName').textContent = file.name;
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById('avatarPreview');
        const init = document.getElementById('avatarInitials');
        if (prev)  { prev.src = e.target.result; prev.style.display = 'block'; }
        if (init)  { init.style.display = 'none'; }
    };
    reader.readAsDataURL(file);
    const btn = document.getElementById('btnUpload');
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor  = 'pointer';
}

function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

function checkStrength(val) {
    const bars   = [document.getElementById('s1'),document.getElementById('s2'),
                    document.getElementById('s3'),document.getElementById('s4')];
    const label  = document.getElementById('strengthLabel');
    let score = 0;
    if (val.length >= 8)                 score++;
    if (/[A-Z]/.test(val))              score++;
    if (/[0-9]/.test(val))              score++;
    if (/[^A-Za-z0-9]/.test(val))       score++;
    const colors = ['#DC2626','#F97316','#EAB308','#16A34A'];
    const labels = ['Trop faible','Faible','Moyen','Fort'];
    bars.forEach((b, i) => b.style.background = i < score ? colors[score - 1] : '#E5E7EB');
    label.textContent = val.length > 0 ? labels[score - 1] ?? '' : '';
    label.style.color = score > 0 ? colors[score - 1] : '#9CA3AF';
}
</script>
@endpush
