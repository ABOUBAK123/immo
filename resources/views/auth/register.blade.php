<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription — Immo Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#FFF7ED 0%,#FFEDD5 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .register-wrap{width:100%;max-width:820px;display:grid;grid-template-columns:1fr 1fr;box-shadow:0 20px 60px rgba(194,65,12,.15);border-radius:20px;overflow:hidden}
        @media(max-width:640px){.register-wrap{grid-template-columns:1fr}.register-side{display:none}}
        /* Colonne gauche */
        .register-side{background:linear-gradient(160deg,#FFEDD5 0%,#FED7AA 55%,#FDBA74 100%);border-right:1px solid #FDBA74;padding:40px 36px;display:flex;flex-direction:column;justify-content:space-between;color:#7C2D12}
        .side-brand{display:flex;align-items:center;gap:10px;font-size:1.1rem;font-weight:800;color:#7C2D12;margin-bottom:40px}
        .side-brand-icon{width:38px;height:38px;background:#EA580C;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff;flex-shrink:0}
        .side-title{font-size:1.5rem;font-weight:800;line-height:1.3;color:#431407;margin-bottom:12px}
        .side-subtitle{font-size:.85rem;color:#92400E;line-height:1.6;margin-bottom:32px}
        .side-feature{display:flex;align-items:center;gap:12px;margin-bottom:16px;font-size:.83rem;color:#7C2D12}
        .side-feature-icon{width:30px;height:30px;background:rgba(255,255,255,.55);border:1px solid rgba(234,88,12,.2);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#C2410C}
        .side-testimonial{background:rgba(255,255,255,.55);border:1px solid rgba(234,88,12,.2);border-radius:12px;padding:16px;margin-top:auto;font-size:.8rem;line-height:1.5;color:#92400E}
        /* Colonne droite */
        .register-form-col{background:#fff;padding:36px 40px}
        .form-header{margin-bottom:28px}
        .form-title{font-size:1.2rem;font-weight:800;color:#1C0A00;margin-bottom:4px}
        .form-subtitle{font-size:.8rem;color:#9CA3AF}
        /* Stepper */
        .stepper{display:flex;align-items:center;gap:0;margin-bottom:28px}
        .step{display:flex;align-items:center;flex:1}
        .step-circle{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;transition:all .2s}
        .step-circle.done{background:#EA580C;color:#fff}
        .step-circle.active{background:#EA580C;color:#fff;box-shadow:0 0 0 4px #FFEDD5}
        .step-circle.pending{background:#F3F4F6;color:#9CA3AF}
        .step-label{font-size:.68rem;font-weight:600;margin-left:8px;white-space:nowrap}
        .step-label.active{color:#EA580C}
        .step-label.pending{color:#9CA3AF}
        .step-line{flex:1;height:2px;background:#F3F4F6;margin:0 8px}
        .step-line.done{background:#EA580C}
        /* Rôle cards */
        .role-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
        .role-card{border:2px solid #E5E7EB;border-radius:10px;padding:14px;cursor:pointer;transition:all .15s;display:flex;flex-direction:column;align-items:center;text-align:center;gap:6px}
        .role-card:hover{border-color:#EA580C;background:#FFF7ED}
        .role-card input{display:none}
        .role-card.selected{border-color:#EA580C;background:#FFF7ED}
        .role-card-icon{font-size:1.5rem}
        .role-card-label{font-size:.78rem;font-weight:600}
        .role-card-desc{font-size:.68rem;color:#9CA3AF;line-height:1.4}
        /* Inputs */
        label.lbl{display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px}
        input.inp,select.inp{width:100%;padding:10px 13px;border:1.5px solid #E5E7EB;border-radius:9px;font-size:.85rem;font-family:inherit;outline:none;transition:border-color .15s;background:#fff}
        input.inp:focus,select.inp:focus{border-color:#EA580C;box-shadow:0 0 0 3px #FFEDD5}
        input.inp.err,select.inp.err{border-color:#DC2626}
        .err-msg{color:#DC2626;font-size:.72rem;margin-top:3px}
        .mb{margin-bottom:16px}
        .row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        /* Buttons */
        .btn-primary{background:#EA580C;color:#fff;border:none;border-radius:9px;padding:11px 20px;font-size:.85rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s;width:100%;font-family:inherit}
        .btn-primary:hover{background:#C2410C}
        .btn-ghost{background:#fff;color:#374151;border:1.5px solid #E5E7EB;border-radius:9px;padding:10px 20px;font-size:.85rem;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .15s;width:100%;font-family:inherit}
        .btn-ghost:hover{border-color:#EA580C;color:#EA580C}
        .btn-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:20px}
        .btn-row.single{grid-template-columns:1fr}
        /* Panel */
        .panel{display:none}.panel.active{display:block}
        /* Password strength */
        .pw-bar{height:4px;border-radius:2px;background:#E5E7EB;margin-top:6px;overflow:hidden}
        .pw-bar-fill{height:100%;border-radius:2px;transition:all .3s;width:0}
        .pw-hint{font-size:.68rem;color:#9CA3AF;margin-top:3px}
    </style>
</head>
<body>

<div class="register-wrap">

    {{-- Colonne gauche --}}
    <div class="register-side">
        <div>
            <div class="side-brand">
                <div class="side-brand-icon"><i class="bi bi-buildings-fill"></i></div>
                Immo Manager
            </div>
            <div class="side-title">Gérez vos biens en toute sérénité</div>
            <div class="side-subtitle">Rejoignez +15 000 propriétaires qui font confiance à notre plateforme pour gérer leur patrimoine immobilier.</div>
            <div class="side-feature">
                <div class="side-feature-icon"><i class="bi bi-house-check"></i></div>
                <span>Gestion complète de votre patrimoine</span>
            </div>
            <div class="side-feature">
                <div class="side-feature-icon"><i class="bi bi-wallet2"></i></div>
                <span>Suivi des loyers & quittances automatiques</span>
            </div>
            <div class="side-feature">
                <div class="side-feature-icon"><i class="bi bi-megaphone"></i></div>
                <span>Publication d'annonces en 1 clic</span>
            </div>
        </div>
        <div class="side-testimonial">
            <div style="margin-bottom:8px;font-style:italic;">"En 3 ans, j'ai géré 12 biens sans aucun comptable. La génération des quittances est un gain de temps énorme."</div>
            <div style="font-weight:700;font-size:.75rem;color:#7C2D12">— Marie L., Propriétaire à Lyon</div>
        </div>
    </div>

    {{-- Colonne droite --}}
    <div class="register-form-col">
        <div class="form-header">
            <div class="form-title">Créer votre compte</div>
            <div class="form-subtitle">C'est gratuit, sans engagement.</div>
        </div>

        {{-- Stepper --}}
        <div class="stepper" id="stepper">
            <div class="step">
                <div class="step-circle active" id="sc1">1</div>
                <span class="step-label active" id="sl1">Profil</span>
            </div>
            <div class="step-line" id="line1"></div>
            <div class="step">
                <div class="step-circle pending" id="sc2">2</div>
                <span class="step-label pending" id="sl2">Rôle</span>
            </div>
            <div class="step-line" id="line2"></div>
            <div class="step">
                <div class="step-circle pending" id="sc3">3</div>
                <span class="step-label pending" id="sl3">Sécurité</span>
            </div>
        </div>

        @if($errors->any())
        <div style="background:#FFF1F2;border:1px solid #FCA5A5;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:.78rem;color:#DC2626">
            <strong><i class="bi bi-exclamation-triangle me-1"></i>Veuillez corriger les erreurs :</strong>
            <ul style="margin:6px 0 0;padding-left:16px">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm" onsubmit="syncName()">
            @csrf

            {{-- Étape 1 : Infos personnelles --}}
            <div class="panel active" id="panel1">
                <div class="mb row2">
                    <div>
                        <label class="lbl">Prénom <span style="color:#DC2626">*</span></label>
                        <input type="text" name="prenom" class="inp" value="{{ old('prenom') }}" placeholder="Jean" required>
                    </div>
                    <div>
                        <label class="lbl">Nom <span style="color:#DC2626">*</span></label>
                        <input type="text" name="nom" class="inp" value="{{ old('nom') }}" placeholder="Dupont" required>
                    </div>
                </div>
                <div class="mb">
                    <label class="lbl">Adresse e-mail <span style="color:#DC2626">*</span></label>
                    <input type="email" name="email" class="inp @error('email') err @enderror"
                           value="{{ old('email') }}" placeholder="jean@exemple.com" required>
                    @error('email')<div class="err-msg">{{ $message }}</div>@enderror
                </div>
                <div class="mb">
                    <label class="lbl">Téléphone</label>
                    <input type="tel" name="phone" class="inp" value="{{ old('phone') }}" placeholder="06 12 34 56 78">
                </div>
                <div class="btn-row single">
                    <button type="button" class="btn-primary" onclick="goStep(2)">
                        Continuer <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
                <p style="text-align:center;font-size:.78rem;color:#9CA3AF;margin-top:16px">
                    Déjà un compte ? <a href="{{ route('login') }}" style="color:#EA580C;font-weight:600">Se connecter</a>
                </p>
            </div>

            {{-- Étape 2 : Rôle --}}
            <div class="panel" id="panel2">
                <div style="font-size:.83rem;font-weight:600;margin-bottom:14px;color:#374151">Je suis…</div>
                <div class="role-grid">
                    <label class="role-card" id="rc_proprietaire">
                        <input type="radio" name="role" value="proprietaire" {{ old('role') === 'proprietaire' ? 'checked' : '' }}>
                        <div class="role-card-icon" style="color:#D97706">🏠</div>
                        <div class="role-card-label">Propriétaire bailleur</div>
                        <div class="role-card-desc">Je gère des biens en location</div>
                    </label>
                    <label class="role-card" id="rc_agent">
                        <input type="radio" name="role" value="agent" {{ old('role') === 'agent' ? 'checked' : '' }}>
                        <div class="role-card-icon" style="color:#EA580C">💼</div>
                        <div class="role-card-label">Agent immobilier</div>
                        <div class="role-card-desc">Je publie des annonces</div>
                    </label>
                    <label class="role-card" id="rc_locataire">
                        <input type="radio" name="role" value="locataire" {{ old('role') === 'locataire' ? 'checked' : '' }}>
                        <div class="role-card-icon" style="color:#7C3AED">🔑</div>
                        <div class="role-card-label">Locataire</div>
                        <div class="role-card-desc">Je suis locataire d'un bien</div>
                    </label>
                    <label class="role-card" id="rc_acheteur">
                        <input type="radio" name="role" value="acheteur" {{ old('role') === 'acheteur' ? 'checked' : '' }}>
                        <div class="role-card-icon" style="color:#16A34A">🔍</div>
                        <div class="role-card-label">Acheteur / Chercheur</div>
                        <div class="role-card-desc">Je cherche un bien</div>
                    </label>
                </div>
                @error('role')<div class="err-msg">{{ $message }}</div>@enderror
                <div class="btn-row">
                    <button type="button" class="btn-ghost" onclick="goStep(1)">
                        <i class="bi bi-arrow-left"></i> Retour
                    </button>
                    <button type="button" class="btn-primary" onclick="goStep(3)">
                        Continuer <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- Étape 3 : Mot de passe --}}
            <div class="panel" id="panel3">
                <div class="mb">
                    <label class="lbl">Mot de passe <span style="color:#DC2626">*</span></label>
                    <input type="password" name="password" id="pwInput" class="inp @error('password') err @enderror"
                           placeholder="Min. 8 caractères" required oninput="updatePwStrength(this.value)">
                    <div class="pw-bar"><div class="pw-bar-fill" id="pwBar"></div></div>
                    <div class="pw-hint" id="pwHint">Saisissez votre mot de passe</div>
                    @error('password')<div class="err-msg">{{ $message }}</div>@enderror
                </div>
                <div class="mb">
                    <label class="lbl">Confirmer le mot de passe <span style="color:#DC2626">*</span></label>
                    <input type="password" name="password_confirmation" class="inp" placeholder="Même mot de passe" required>
                </div>
                <div style="background:#F0FDF4;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:.75rem;color:#15803D">
                    <i class="bi bi-shield-check me-1"></i>
                    En créant un compte, vous acceptez nos <a href="#" style="color:#15803D;font-weight:600">Conditions d'utilisation</a>
                    et notre <a href="#" style="color:#15803D;font-weight:600">Politique de confidentialité</a>.
                </div>
                <div class="btn-row">
                    <button type="button" class="btn-ghost" onclick="goStep(2)">
                        <i class="bi bi-arrow-left"></i> Retour
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-person-check"></i> Créer mon compte
                    </button>
                </div>
            </div>

            {{-- Hidden combined name (server expects 'name') --}}
            <input type="hidden" name="name" id="hiddenName">
        </form>
    </div>
</div>

<script>
let currentStep = {{ $errors->any() ? 3 : 1 }};

function goStep(n) {
    const prenom = document.querySelector('[name=prenom]')?.value?.trim() || '';
    const nom    = document.querySelector('[name=nom]')?.value?.trim() || '';
    document.getElementById('hiddenName').value = (prenom + ' ' + nom).trim();

    document.getElementById('panel' + currentStep).classList.remove('active');
    document.getElementById('panel' + n).classList.add('active');
    currentStep = n;
    updateStepper();
}

function updateStepper() {
    for (let i = 1; i <= 3; i++) {
        const circ = document.getElementById('sc' + i);
        const lbl  = document.getElementById('sl' + i);
        if (i < currentStep) {
            circ.className = 'step-circle done';
            circ.innerHTML = '<i class="bi bi-check-lg"></i>';
            lbl.className  = 'step-label active';
        } else if (i === currentStep) {
            circ.className = 'step-circle active';
            circ.innerHTML = i;
            lbl.className  = 'step-label active';
        } else {
            circ.className = 'step-circle pending';
            circ.innerHTML = i;
            lbl.className  = 'step-label pending';
        }
        if (i < 3) {
            document.getElementById('line' + i).style.background = i < currentStep ? '#EA580C' : '#F3F4F6';
        }
    }
}

document.querySelectorAll('.role-card input[type=radio]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
        radio.closest('.role-card').classList.add('selected');
    });
    if (radio.checked) radio.closest('.role-card').classList.add('selected');
});

function syncName() {
    const prenom = document.querySelector('[name=prenom]')?.value?.trim() || '';
    const nom    = document.querySelector('[name=nom]')?.value?.trim() || '';
    document.getElementById('hiddenName').value = (prenom + ' ' + nom).trim();
}

function updatePwStrength(val) {
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const bar   = document.getElementById('pwBar');
    const hint  = document.getElementById('pwHint');
    const pcts  = ['0%', '30%', '55%', '80%', '100%'];
    const colors = ['#E5E7EB', '#DC2626', '#F59E0B', '#16A34A', '#EA580C'];
    const labels = ['', 'Trop court', 'Faible', 'Moyen', 'Fort'];

    bar.style.width      = pcts[score];
    bar.style.background = colors[score];
    hint.textContent     = labels[score] || 'Saisissez votre mot de passe';
    hint.style.color     = colors[score];
}

if ({{ $errors->any() ? 'true' : 'false' }}) {
    updateStepper();
}
</script>
</body>
</html>
