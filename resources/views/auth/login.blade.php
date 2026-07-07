<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — ImmoGest</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        *,*::before,*::after { box-sizing:border-box; margin:0; padding:0 }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-wrap {
            width: 100%; max-width: 820px;
            display: grid; grid-template-columns: 1fr 1fr;
            box-shadow: 0 20px 60px rgba(194, 65, 12, .15);
            border-radius: 20px; overflow: hidden;
        }
        @media(max-width:640px) {
            .login-wrap { grid-template-columns:1fr }
            .login-side  { display:none }
        }

        /* ── Panneau gauche ── */
        .login-side {
            background: linear-gradient(160deg, #FFEDD5 0%, #FED7AA 55%, #FDBA74 100%);
            border-right: 1px solid #FDBA74;
            padding: 40px 36px;
            display: flex; flex-direction: column; justify-content: space-between;
            color: #7C2D12;
        }
        .side-brand {
            display: flex; align-items: center; gap: 10px;
            font-size: 1.1rem; font-weight: 800; color: #7C2D12; margin-bottom: 40px;
        }
        .side-brand-icon {
            width: 38px; height: 38px;
            background: #EA580C; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: #fff; flex-shrink: 0;
        }
        .side-title {
            font-size: 1.5rem; font-weight: 800; line-height: 1.3;
            color: #431407; margin-bottom: 12px;
        }
        .side-subtitle {
            font-size: .85rem; color: #92400E; line-height: 1.6; margin-bottom: 32px;
        }
        .stat-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: auto;
        }
        .stat-box {
            background: rgba(255,255,255,.55);
            border: 1px solid rgba(234,88,12,.2);
            border-radius: 12px; padding: 14px; text-align: center;
        }
        .stat-num { font-size: 1.3rem; font-weight: 800; color: #C2410C; }
        .stat-lbl { font-size: .68rem; color: #92400E; margin-top: 2px; }

        /* ── Panneau droit ── */
        .login-form-col {
            background: #fff; padding: 48px 40px;
            display: flex; flex-direction: column; justify-content: center;
        }
        .form-title    { font-size: 1.2rem; font-weight: 800; color: #1C0A00; margin-bottom: 4px; }
        .form-subtitle { font-size: .8rem; color: #9CA3AF; margin-bottom: 28px; }

        label.lbl {
            display: block; font-size: .78rem; font-weight: 600;
            color: #374151; margin-bottom: 5px;
        }
        input.inp {
            width: 100%; padding: 10px 13px;
            border: 1.5px solid #E5E7EB; border-radius: 9px;
            font-size: .85rem; font-family: inherit;
            outline: none; transition: border-color .15s; background: #fff;
        }
        input.inp:focus {
            border-color: #EA580C;
            box-shadow: 0 0 0 3px #FFEDD5;
        }
        input.inp.err { border-color: #DC2626; }
        .err-msg { color: #DC2626; font-size: .72rem; margin-top: 3px; }
        .mb { margin-bottom: 16px; }

        .checkbox-row {
            display: flex; align-items: center; gap: 8px;
            font-size: .8rem; color: #6B7280; margin-bottom: 20px;
        }
        .checkbox-row input { width:16px; height:16px; accent-color: #EA580C; }

        .btn-primary {
            background: #EA580C; color: #fff; border: none;
            border-radius: 9px; padding: 12px 20px;
            font-size: .88rem; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            gap: 8px; transition: background .15s; width: 100%;
            font-family: inherit;
        }
        .btn-primary:hover { background: #C2410C; }

    </style>
</head>
<body>

<div class="login-wrap">
    {{-- Panneau gauche ─────────────────────────────────────────────────────── --}}
    <div class="login-side">
        <div>
            <div class="side-brand">
                <div class="side-brand-icon"><i class="bi bi-buildings-fill"></i></div>
                ImmoGest
            </div>
            <div class="side-title">Bienvenue sur votre espace de gestion</div>
            <div class="side-subtitle">Gérez votre patrimoine, suivez vos loyers et publiez vos annonces depuis un seul endroit.</div>
        </div>
        <div class="stat-row">
            <div class="stat-box">
                <div class="stat-num">15K+</div>
                <div class="stat-lbl">Propriétaires</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">48K+</div>
                <div class="stat-lbl">Biens gérés</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">2.1M</div>
                <div class="stat-lbl">Loyers/mois</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">4.9★</div>
                <div class="stat-lbl">Note moyenne</div>
            </div>
        </div>

        {{-- Support Contact Info --}}
        <div style="background: rgba(255,255,255,.35); border: 1px solid rgba(234,88,12,.3); border-radius: 12px; padding: 14px; margin-top: 20px;">
            <p style="font-size: .75rem; color: #92400E; font-weight: 600; margin-bottom: 8px;">Besoin d'aide?</p>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <a href="tel:010142004609" style="font-size: .8rem; color: #C2410C; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-telephone-fill"></i> 01 01 42 00 46 09
                </a>
                <a href="https://wa.me/22510142004609" target="_blank" style="font-size: .8rem; color: #C2410C; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-whatsapp"></i> +225 01 01 42 00 46 09
                </a>
            </div>
        </div>
    </div>

    {{-- Panneau droit ──────────────────────────────────────────────────────── --}}
    <div class="login-form-col">
        <div class="form-title">Connexion</div>
        <div class="form-subtitle">Accédez à votre espace de gestion immobilière</div>

        @if(session('status'))
        <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:.78rem;color:#15803D;display:flex;align-items:center;gap:8px">
            <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background:#FFF1F2;border:1px solid #FCA5A5;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:.78rem;color:#DC2626">
            <i class="bi bi-exclamation-triangle me-1"></i>
            @foreach($errors->all() as $e){{ $e }}@endforeach
        </div>
        @endif

        @if(session('warning'))
        <div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:.78rem;color:#92400E">
            <i class="bi bi-shield-exclamation me-1"></i> {{ session('warning') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb">
                <label class="lbl" for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" class="inp @error('email') err @enderror"
                       value="{{ old('email') }}" placeholder="vous@exemple.com" required autofocus>
            </div>
            <div class="mb">
                <label class="lbl" for="password" style="display:flex;justify-content:space-between;align-items:center">
                    Mot de passe
                    <a href="{{ route('password.request') }}"
                       style="font-size:.74rem;font-weight:600;color:#EA580C;text-decoration:none">
                        Mot de passe oublié ?
                    </a>
                </label>
                <input type="password" id="password" name="password" class="inp" placeholder="••••••••" required>
            </div>
            <div class="checkbox-row">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Se souvenir de moi</label>
            </div>
            <button type="submit" class="btn-primary">
                <i class="bi bi-box-arrow-in-right"></i> Se connecter
            </button>
        </form>

        <p style="text-align:center;font-size:.78rem;color:#9CA3AF;margin-top:24px">
            Pas encore de compte ?
            <a href="{{ route('register') }}" style="color:#EA580C;font-weight:600;text-decoration:none">
                Créer un compte gratuit →
            </a>
        </p>
    </div>
</div>

<script></script>
</body>
</html>
