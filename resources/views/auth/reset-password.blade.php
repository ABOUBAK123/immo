<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouveau mot de passe — ImmoGest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        *,*::before,*::after { box-sizing:border-box; margin:0; padding:0 }
        body {
            font-family:'Inter',sans-serif;
            background:linear-gradient(135deg,#FFF7ED 0%,#FFEDD5 100%);
            min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
        }
        .wrap {
            width:100%;max-width:460px;background:#fff;border-radius:20px;
            box-shadow:0 20px 60px rgba(194,65,12,.15);overflow:hidden;
        }
        .header {
            background:linear-gradient(160deg,#FFEDD5 0%,#FED7AA 55%,#FDBA74 100%);
            padding:32px 40px 28px;border-bottom:1px solid #FDBA74;
        }
        .brand {
            display:flex;align-items:center;gap:10px;
            font-size:1rem;font-weight:800;color:#7C2D12;margin-bottom:20px;
        }
        .brand-icon {
            width:34px;height:34px;background:#EA580C;border-radius:9px;
            display:flex;align-items:center;justify-content:center;
            font-size:1.1rem;color:#fff;flex-shrink:0;
        }
        .header-icon {
            width:56px;height:56px;border-radius:16px;
            background:rgba(255,255,255,.7);border:1px solid rgba(234,88,12,.2);
            display:flex;align-items:center;justify-content:center;
            font-size:1.5rem;color:#EA580C;margin-bottom:14px;
        }
        .header h1 { font-size:1.15rem;font-weight:800;color:#431407;margin-bottom:6px; }
        .header p  { font-size:.82rem;color:#92400E;line-height:1.6; }
        .body { padding:32px 40px; }
        label.lbl {
            display:block;font-size:.78rem;font-weight:600;
            color:#374151;margin-bottom:5px;letter-spacing:.02em;
        }
        .input-wrap { position:relative; }
        input.inp {
            width:100%;padding:11px 40px 11px 14px;
            border:1.5px solid #E5E7EB;border-radius:10px;
            font-size:.86rem;font-family:inherit;
            outline:none;transition:border-color .15s,box-shadow .15s;background:#fff;
        }
        input.inp:focus { border-color:#EA580C;box-shadow:0 0 0 3px #FFEDD5; }
        input.inp.err   { border-color:#DC2626; }
        .eye-btn {
            position:absolute;right:12px;top:50%;transform:translateY(-50%);
            background:none;border:none;color:#9CA3AF;cursor:pointer;padding:0;
            font-size:.95rem;transition:color .15s;
        }
        .eye-btn:hover { color:#EA580C; }
        .err-msg { color:#DC2626;font-size:.72rem;margin-top:4px; }
        .mb { margin-bottom:16px; }
        .strength-bar { display:flex;gap:4px;margin-top:8px; }
        .strength-bar div { height:4px;border-radius:2px;flex:1;background:#E5E7EB;transition:.3s; }
        .strength-label { font-size:.7rem;color:#9CA3AF;margin-top:3px; }
        .btn {
            width:100%;padding:12px;border:none;border-radius:10px;
            font-size:.88rem;font-weight:700;cursor:pointer;font-family:inherit;
            background:linear-gradient(135deg,#EA580C,#F97316);color:#fff;
            display:flex;align-items:center;justify-content:center;gap:8px;
            transition:opacity .15s,transform .15s;margin-top:8px;
        }
        .btn:hover { opacity:.9;transform:translateY(-1px); }
        .alert-err {
            background:#FFF1F2;border:1px solid #FECDD3;border-radius:10px;
            padding:12px 16px;margin-bottom:20px;
            font-size:.78rem;color:#DC2626;
            display:flex;align-items:center;gap:8px;
        }
        .rules {
            background:#F9FAFB;border:1px solid #E5E7EB;border-radius:10px;
            padding:12px 16px;margin-bottom:20px;
        }
        .rule {
            display:flex;align-items:center;gap:8px;
            font-size:.76rem;color:#9CA3AF;padding:3px 0;
        }
        .rule i { font-size:.8rem; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="brand">
            <div class="brand-icon"><i class="bi bi-buildings-fill"></i></div>
            ImmoGest
        </div>
        <div class="header-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <h1>Créer un nouveau mot de passe</h1>
        <p>Choisissez un mot de passe sécurisé pour protéger votre compte.</p>
    </div>
    <div class="body">

        @if($errors->any())
        <div class="alert-err">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <div class="rules">
            <div class="rule"><i class="bi bi-check-circle text-success" style="color:#16A34A"></i> Au moins 8 caractères</div>
            <div class="rule"><i class="bi bi-check-circle" style="color:#D1D5DB"></i> Majuscules et minuscules recommandées</div>
            <div class="rule"><i class="bi bi-check-circle" style="color:#D1D5DB"></i> Chiffres et caractères spéciaux conseillés</div>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb">
                <label class="lbl" for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email"
                       class="inp {{ $errors->has('email') ? 'err' : '' }}"
                       value="{{ old('email', $email ?? '') }}"
                       placeholder="votre@email.com" required>
                @error('email')<div class="err-msg">{{ $message }}</div>@enderror
            </div>

            <div class="mb">
                <label class="lbl" for="password">Nouveau mot de passe</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password"
                           class="inp {{ $errors->has('password') ? 'err' : '' }}"
                           placeholder="Min. 8 caractères" required
                           oninput="checkStrength(this.value)">
                    <button type="button" class="eye-btn" onclick="togglePwd('password','eyeNew')">
                        <i class="bi bi-eye" id="eyeNew"></i>
                    </button>
                </div>
                <div class="strength-bar" id="strengthBar">
                    <div id="s1"></div><div id="s2"></div><div id="s3"></div><div id="s4"></div>
                </div>
                <div class="strength-label" id="strengthLabel"></div>
                @error('password')<div class="err-msg">{{ $message }}</div>@enderror
            </div>

            <div class="mb">
                <label class="lbl" for="password_confirmation">Confirmer le mot de passe</label>
                <div class="input-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="inp" placeholder="Répéter le mot de passe" required>
                    <button type="button" class="eye-btn" onclick="togglePwd('password_confirmation','eyeConf')">
                        <i class="bi bi-eye" id="eyeConf"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn">
                <i class="bi bi-shield-check"></i> Enregistrer le nouveau mot de passe
            </button>
        </form>
    </div>
</div>

<script>
function togglePwd(id, iconId) {
    const inp  = document.getElementById(id);
    const icon = document.getElementById(iconId);
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.replace('bi-eye','bi-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.replace('bi-eye-slash','bi-eye');
    }
}

function checkStrength(val) {
    const bars  = ['s1','s2','s3','s4'].map(id => document.getElementById(id));
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (val.length >= 8)              score++;
    if (/[A-Z]/.test(val))           score++;
    if (/[0-9]/.test(val))           score++;
    if (/[^A-Za-z0-9]/.test(val))    score++;
    const colors = ['#DC2626','#F97316','#EAB308','#16A34A'];
    const labels = ['Trop faible','Faible','Moyen','Fort'];
    bars.forEach((b, i) => b.style.background = i < score ? colors[score - 1] : '#E5E7EB');
    label.textContent  = val.length > 0 ? (labels[score - 1] ?? '') : '';
    label.style.color  = score > 0 ? colors[score - 1] : '#9CA3AF';
}
</script>
</body>
</html>
