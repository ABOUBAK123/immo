<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mot de passe oublié — ImmoGest</title>
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
            width:100%;max-width:460px;
            background:#fff;border-radius:20px;
            box-shadow:0 20px 60px rgba(194,65,12,.15);
            overflow:hidden;
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
        input.inp {
            width:100%;padding:11px 14px;
            border:1.5px solid #E5E7EB;border-radius:10px;
            font-size:.86rem;font-family:inherit;
            outline:none;transition:border-color .15s,box-shadow .15s;background:#fff;
        }
        input.inp:focus { border-color:#EA580C;box-shadow:0 0 0 3px #FFEDD5; }
        input.inp.err   { border-color:#DC2626; }
        .err-msg { color:#DC2626;font-size:.72rem;margin-top:4px; }
        .btn {
            width:100%;padding:12px;border:none;border-radius:10px;
            font-size:.88rem;font-weight:700;cursor:pointer;font-family:inherit;
            background:linear-gradient(135deg,#EA580C,#F97316);color:#fff;
            display:flex;align-items:center;justify-content:center;gap:8px;
            transition:opacity .15s,transform .15s;margin-top:20px;
        }
        .btn:hover { opacity:.9;transform:translateY(-1px); }
        .back-link {
            display:flex;align-items:center;justify-content:center;gap:6px;
            margin-top:20px;font-size:.8rem;color:#9CA3AF;text-decoration:none;transition:.15s;
        }
        .back-link:hover { color:#EA580C; }
        .alert-success {
            background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;
            padding:14px 16px;margin-bottom:20px;
            display:flex;align-items:flex-start;gap:10px;
        }
        .alert-success-icon {
            width:32px;height:32px;border-radius:8px;background:#16A34A;
            display:flex;align-items:center;justify-content:center;
            color:#fff;font-size:.9rem;flex-shrink:0;
        }
        .steps {
            background:#FFF7ED;border:1px solid #FED7AA;border-radius:10px;
            padding:14px 16px;margin-bottom:24px;
        }
        .step {
            display:flex;align-items:center;gap:10px;
            font-size:.78rem;color:#92400E;padding:4px 0;
        }
        .step-num {
            width:20px;height:20px;border-radius:50%;background:#EA580C;
            color:#fff;font-size:.65rem;font-weight:800;
            display:flex;align-items:center;justify-content:center;flex-shrink:0;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="brand">
            <div class="brand-icon"><i class="bi bi-buildings-fill"></i></div>
            ImmoGest
        </div>
        <div class="header-icon"><i class="bi bi-lock-fill"></i></div>
        <h1>Mot de passe oublié ?</h1>
        <p>Saisissez votre adresse e-mail et nous vous enverrons un lien pour créer un nouveau mot de passe.</p>
    </div>
    <div class="body">

        @if(session('status'))
        <div class="alert-success">
            <div class="alert-success-icon"><i class="bi bi-check-lg"></i></div>
            <div>
                <div style="font-size:.83rem;font-weight:700;color:#15803D;margin-bottom:3px">E-mail envoyé !</div>
                <div style="font-size:.78rem;color:#166534">{{ session('status') }}</div>
                <div style="font-size:.74rem;color:#9CA3AF;margin-top:6px">Pensez à vérifier votre dossier spam si vous ne trouvez pas l'e-mail.</div>
            </div>
        </div>
        @else
        <div class="steps">
            <div class="step"><div class="step-num">1</div> Saisissez votre adresse e-mail</div>
            <div class="step"><div class="step-num">2</div> Cliquez sur le lien reçu par e-mail</div>
            <div class="step"><div class="step-num">3</div> Créez votre nouveau mot de passe</div>
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div style="margin-bottom:4px">
                <label class="lbl" for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email"
                       class="inp {{ $errors->has('email') ? 'err' : '' }}"
                       value="{{ old('email') }}"
                       placeholder="votre@email.com" required autofocus>
                @error('email')
                <div class="err-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn">
                <i class="bi bi-send-fill"></i> Envoyer le lien de réinitialisation
            </button>
        </form>

        <a href="{{ route('login') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Retour à la connexion
        </a>
    </div>
</div>
</body>
</html>
