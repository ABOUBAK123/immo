<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ImmoGest') — Gestion locative simplifiée</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary:     #F97316;
            --primary-dk:  #EA580C;
            --primary-lt:  #FFF7ED;
            --success:     #16A34A;
            --warning:     #D97706;
            --danger:      #DC2626;
            --sidebar-w:   260px;
            --topbar-h:    60px;
            --sidebar-bg:  #EA580C;
            --sidebar-border: rgba(255,255,255,.15);
            --body-bg:     #FFF7ED;
            --text-main:   #1C0A00;
            --text-muted:  #78716C;
            --card-radius: 12px;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--body-bg);
            color: var(--text-main);
            margin: 0;
        }

        /* ── Sidebar ─────────────────────────────────────── */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w); height: 100vh;
            background: linear-gradient(180deg, #FFEDD5 0%, #FED7AA 100%);
            border-right: 1px solid #FDBA74;
            display: flex; flex-direction: column;
            z-index: 1050; overflow-y: auto;
            transition: transform .25s ease;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 18px 20px; border-bottom: 1px solid #FDBA74;
        }
        .sidebar-brand .logo-icon {
            width: 36px; height: 36px; border-radius: 9px;
            background: #EA580C; display: flex; align-items: center;
            justify-content: center; color: #fff; font-size: 18px; flex-shrink: 0;
        }
        .sidebar-brand .brand-name { font-weight: 700; font-size: .95rem; color: #7C2D12; }
        .sidebar-brand .brand-sub  { font-size: .7rem; color: #C2410C; }

        .sidebar-section {
            padding: 20px 16px 4px;
            font-size: .63rem; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: #C2410C;
        }
        .nav-item-custom {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; margin: 2px 8px;
            border-radius: 8px; text-decoration: none;
            color: #92400E; font-size: .83rem; font-weight: 500;
            transition: all .15s;
        }
        .nav-item-custom i { font-size: 1rem; width: 20px; text-align: center; flex-shrink: 0; }
        .nav-item-custom:hover { background: rgba(234,88,12,.12); color: #C2410C; }
        .nav-item-custom.active {
            background: rgba(234,88,12,.18); color: #EA580C; font-weight: 700;
            box-shadow: inset 3px 0 0 #EA580C;
        }
        .nav-item-custom .badge-nav {
            margin-left: auto; background: #EA580C;
            color: #fff; border-radius: 20px; font-size: .65rem;
            padding: 2px 7px; font-weight: 700;
        }
        .sidebar-footer {
            margin-top: auto; padding: 16px; border-top: 1px solid #FDBA74;
        }
        .user-card {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 10px; border-radius: 10px; background: rgba(234,88,12,.1);
        }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: #EA580C; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: .8rem; font-weight: 700; flex-shrink: 0;
        }
        .user-name  { font-size: .8rem; font-weight: 600; line-height: 1.2; color: #7C2D12; }
        .user-role  { font-size: .68rem; color: #C2410C; }

        /* ── Main content ────────────────────────────────── */
        .main-wrapper { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }

        /* ── Topbar ──────────────────────────────────────── */
        .topbar {
            height: var(--topbar-h); background: #fff;
            border-bottom: 1px solid #E5E7EB;
            display: flex; align-items: center;
            padding: 0 24px; gap: 12px;
            position: sticky; top: 0; z-index: 1000;
        }
        .topbar .page-title { font-size: .95rem; font-weight: 600; flex: 1; }
        .topbar .btn-topbar {
            width: 36px; height: 36px; border-radius: 8px;
            border: 1px solid var(--sidebar-border); background: #fff;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted); cursor: pointer; font-size: 1rem;
            text-decoration: none; transition: all .15s;
        }
        .topbar .btn-topbar:hover { background: var(--primary-lt); color: var(--primary); border-color: var(--primary); }

        /* ── Page content ────────────────────────────────── */
        .page-content { padding: 24px; flex: 1; }

        /* ── Cards ───────────────────────────────────────── */
        .card-immo {
            background: #fff; border-radius: var(--card-radius);
            border: 1px solid #E5E7EB;
            box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden;
        }
        .stat-card {
            padding: 20px; display: flex; align-items: center; gap: 16px;
        }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; flex-shrink: 0;
        }
        .stat-val   { font-size: 1.5rem; font-weight: 700; line-height: 1; }
        .stat-label { font-size: .75rem; color: var(--text-muted); margin-top: 4px; }
        .stat-delta { font-size: .7rem; margin-top: 6px; }

        /* ── Badges ──────────────────────────────────────── */
        .badge-pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600;
        }
        .badge-success { background: #DCFCE7; color: #15803D; }
        .badge-warning { background: #FEF3C7; color: #B45309; }
        .badge-danger  { background: #FEE2E2; color: #B91C1C; }
        .badge-info    { background: #FFEDD5; color: #C2410C; }
        .badge-gray    { background: #F5F5F4; color: #57534E; }

        /* ── Tables ──────────────────────────────────────── */
        .table-immo { border-collapse: collapse; width: 100%; font-size: .82rem; }
        .table-immo thead th {
            padding: 10px 16px; background: #F9FAFB;
            border-bottom: 1px solid #E5E7EB;
            font-size: .7rem; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: .05em;
        }
        .table-immo tbody td { padding: 12px 16px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
        .table-immo tbody tr:last-child td { border-bottom: none; }
        .table-immo tbody tr:hover td { background: #FFF7ED; }

        /* ── Buttons ─────────────────────────────────────── */
        .btn-primary-immo {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--primary); color: #fff; border: none;
            padding: 8px 18px; border-radius: 8px; font-size: .83rem;
            font-weight: 600; cursor: pointer; text-decoration: none;
            transition: background .15s;
        }
        .btn-primary-immo:hover { background: var(--primary-dk); color: #fff; }
        .btn-ghost {
            display: inline-flex; align-items: center; gap: 6px;
            background: transparent; color: var(--text-muted);
            border: 1px solid #E5E7EB; padding: 7px 16px;
            border-radius: 8px; font-size: .83rem; font-weight: 500;
            cursor: pointer; text-decoration: none; transition: all .15s;
        }
        .btn-ghost:hover { background: var(--body-bg); color: var(--text-main); }

        /* ── Forms ───────────────────────────────────────── */
        .form-label-immo { font-size: .8rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px; display: block; }
        .form-control-immo {
            width: 100%; padding: 9px 12px; border: 1px solid #D1D5DB;
            border-radius: 8px; font-size: .83rem; color: var(--text-main);
            background: #fff; transition: border-color .15s, box-shadow .15s;
            font-family: inherit;
        }
        .form-control-immo:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(249,115,22,.15); }
        .form-select-immo {
            width: 100%; padding: 9px 12px; border: 1px solid #D1D5DB;
            border-radius: 8px; font-size: .83rem; color: var(--text-main);
            background: #fff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") no-repeat right 10px center/14px 10px;
            appearance: none; cursor: pointer; font-family: inherit;
        }
        .form-select-immo:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(249,115,22,.15); }

        /* ── Section header ──────────────────────────────── */
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .section-title  { font-size: 1rem; font-weight: 700; }
        .section-sub    { font-size: .78rem; color: var(--text-muted); margin-top: 2px; }

        /* ── Property card ───────────────────────────────── */
        .property-card { cursor: pointer; transition: box-shadow .2s, transform .2s; }
        .property-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.10); transform: translateY(-2px); }
        .property-img { height: 160px; width: 100%; object-fit: cover; }
        .property-img-placeholder {
            height: 160px; display: flex; align-items: center; justify-content: center;
            background: #F3F4F6; color: #D1D5DB; font-size: 3rem;
        }

        /* ── Alert box ───────────────────────────────────── */
        .alert-immo {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 12px 16px; border-radius: 10px; font-size: .82rem; margin-bottom: 8px;
        }
        .alert-immo-warning { background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; }
        .alert-immo-danger  { background: #FFF1F2; border: 1px solid #FECDD3; color: #9F1239; }
        .alert-immo-info    { background: #FFF7ED; border: 1px solid #FED7AA; color: #C2410C; }

        /* ── Progress bar ────────────────────────────────── */
        .progress-immo { height: 6px; border-radius: 3px; background: #E5E7EB; overflow: hidden; }
        .progress-fill  { height: 100%; border-radius: 3px; background: var(--primary); transition: width .5s ease; }

        /* ── Mobile ──────────────────────────────────────── */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .sidebar-overlay { display: block !important; }
        }
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.4); z-index: 1040;
        }

        /* ── Cloche notifications ────────────────────────────────── */
        .bell-item {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 10px 16px; border-bottom: 1px solid #F9FAFB;
            cursor: pointer; transition: background .12s; text-decoration: none;
        }
        .bell-item:hover { background: #FFF7ED; }
        .bell-item.unread { background: #FFFBF5; }
        .bell-item-icon {
            width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: .9rem;
        }
        .bell-item-title { font-size: .78rem; font-weight: 600; color: #1C0A00; line-height: 1.3; }
        .bell-item-msg   { font-size: .72rem; color: #78716C; line-height: 1.4; margin-top: 2px; }
        .bell-item-time  { font-size: .65rem; color: #A8A29E; margin-top: 3px; }
        .bell-empty      { padding: 28px 16px; text-align: center; color: #A8A29E; font-size: .78rem; }
    </style>
    @stack('styles')
</head>
<body>

@auth
{{-- Overlay mobile --}}
<div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>

{{-- ── SIDEBAR ──────────────────────────────────────────────────────────── --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-icon"><i class="bi bi-house-door-fill"></i></div>
        <div>
            <div class="brand-name">ImmoGest</div>
            <div class="brand-sub">Gestion locative</div>
        </div>
    </div>

    <nav style="padding: 8px 0; flex:1">
        @php
            $navRole = auth()->user()->role;
            $mods    = $navRole !== 'admin' ? \App\Models\ProfilConfig::moduleActifs($navRole) : [];
            $m       = fn(string $key) => $navRole === 'admin' || ($mods[$key] ?? true);
        @endphp

        <div class="sidebar-section">Principal</div>
        <a href="{{ route('dashboard') }}"
           class="nav-item-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Mon Bureau
        </a>

        {{-- ── Abonnement (propriétaire uniquement) ─────────────────── --}}
        @if($navRole === 'proprietaire')
        @php $aboActif = auth()->user()->abonnementActif(); @endphp
        <a href="{{ route('abonnements.index') }}"
           class="nav-item-custom {{ request()->routeIs('abonnements.*') ? 'active' : '' }}"
           style="{{ !$aboActif ? 'background:#FFF1F2;border-left:3px solid #DC2626' : '' }}">
            <i class="bi bi-{{ $aboActif ? 'shield-check-fill' : 'shield-x-fill' }}"
               style="color:{{ $aboActif ? '#16A34A' : '#DC2626' }}"></i>
            <span>Mon abonnement</span>
            @if(!$aboActif)
            <span style="margin-left:auto;font-size:.65rem;background:#DC2626;color:#fff;
                         padding:1px 7px;border-radius:8px;font-weight:700">Requis</span>
            @elseif($aboActif->joursRestants() <= 5)
            <span style="margin-left:auto;font-size:.65rem;background:#D97706;color:#fff;
                         padding:1px 7px;border-radius:8px;font-weight:700">{{ $aboActif->joursRestants() }}j</span>
            @endif
        </a>
        @endif

        {{-- ── PROPRIÉTAIRE ──────────────────────────────────────────── --}}
        @if(in_array($navRole, ['admin','proprietaire']))
        @php $hasPat = $m('biens') || $m('locataires') || $m('locations'); @endphp
        @if($hasPat)
        <div class="sidebar-section">Patrimoine</div>
        @if($m('biens'))
        <a href="{{ route('biens.index') }}"
           class="nav-item-custom {{ request()->routeIs('biens.*') ? 'active' : '' }}">
            <i class="bi bi-buildings"></i> Mes biens
        </a>
        @endif
        @if($m('locataires'))
        <a href="{{ route('locataires.index') }}"
           class="nav-item-custom {{ request()->routeIs('locataires.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Locataires
        </a>
        @endif
        @if($m('locations'))
        <a href="{{ route('locations.index') }}"
           class="nav-item-custom {{ request()->routeIs('locations.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Locations / Baux
        </a>
        @endif
        @endif

        @php $hasFin = $m('paiements') || $m('interventions'); @endphp
        @if($hasFin)
        <div class="sidebar-section">Finance</div>
        @if($m('paiements'))
        <a href="{{ route('paiements.index') }}"
           class="nav-item-custom {{ request()->routeIs('paiements.*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i> Paiements
        </a>
        @endif
        @if($m('interventions'))
        <a href="{{ route('interventions.index') }}"
           class="nav-item-custom {{ request()->routeIs('interventions.*') ? 'active' : '' }}">
            <i class="bi bi-tools"></i> Interventions
        </a>
        @endif
        @endif

        @php $hasCom = $m('notifications') || $m('agent_ia'); @endphp
        @if($hasCom)
        <div class="sidebar-section">Communication</div>
        @if($m('notifications'))
        <a href="{{ route('notifications.index') }}"
           class="nav-item-custom {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> Notifications
            @php
                $nbRetards = \App\Models\Paiement::whereHas('location.bien', fn($q) => $q->where('proprietaire_id', auth()->id()))
                    ->where('statut','en_attente')->where('date_echeance','<',now())->count();
            @endphp
            @if($nbRetards > 0)<span class="badge-nav">{{ $nbRetards }}</span>@endif
        </a>
        @endif
        @if($m('agent_ia'))
        <a href="{{ route('agent-ia.index') }}"
           class="nav-item-custom {{ request()->routeIs('agent-ia.*') ? 'active' : '' }}">
            <i class="bi bi-robot"></i> Agent IA
        </a>
        @endif
        @endif
        @endif

        {{-- ── LOCATAIRE ─────────────────────────────────────────────── --}}
        @if($navRole === 'locataire')
        <div class="sidebar-section">Mon espace</div>
        @if($m('location'))
        <a href="{{ route('locations.index') }}"
           class="nav-item-custom {{ request()->routeIs('locations.*') ? 'active' : '' }}">
            <i class="bi bi-house-check"></i> Mon bail
        </a>
        @endif
        @if($m('mes_reglements'))
        <a href="{{ route('locataire.reglements') }}"
           class="nav-item-custom {{ request()->routeIs('locataire.reglements') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Mes règlements
        </a>
        @endif
        @if($m('interventions'))
        <a href="{{ route('interventions.create') }}"
           class="nav-item-custom {{ request()->routeIs('interventions.create') ? 'active' : '' }}">
            <i class="bi bi-tools"></i> Déclarer travaux
        </a>
        @endif
        @if($m('notifications'))
        <a href="{{ route('notifications.index') }}"
           class="nav-item-custom {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> Notifications
        </a>
        @endif
        @endif

        {{-- ── AGENT IMMOBILIER ─────────────────────────────────────── --}}
        @if($navRole === 'agent')
        <div class="sidebar-section">Mes Publications</div>
        @if($m('mes_annonces'))
        <a href="{{ route('agent.mes-annonces') }}"
           class="nav-item-custom {{ request()->routeIs('agent.mes-annonces') ? 'active' : '' }}">
            <i class="bi bi-grid-3x3-gap"></i> Mes annonces
            @php $nbAnnAgent = auth()->user()->annonces()->where('statut','active')->count(); @endphp
            @if($nbAnnAgent > 0)<span class="badge-nav">{{ $nbAnnAgent }}</span>@endif
        </a>
        @endif
        @if($m('publier'))
        <a href="{{ route('agent.publier') }}"
           class="nav-item-custom {{ request()->routeIs('agent.publier') ? 'active' : '' }}">
            <i class="bi bi-plus-square"></i> Publier un bien
        </a>
        @endif
        @if($m('notifications'))
        <a href="{{ route('notifications.index') }}"
           class="nav-item-custom {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> Notifications
        </a>
        @endif
        @if($m('agent_ia'))
        <a href="{{ route('agent-ia.index') }}"
           class="nav-item-custom {{ request()->routeIs('agent-ia.*') ? 'active' : '' }}">
            <i class="bi bi-robot"></i> Agent IA
        </a>
        @endif
        @endif

        {{-- ── MARKETPLACE ──────────────────────────────────────────── --}}
        @php $showMarket = $navRole === 'admin' || $navRole === 'acheteur' ? $m('marketplace') : ($m('annonces') || $navRole === 'agent'); @endphp
        @if($showMarket || $navRole === 'admin')
        <div class="sidebar-section">Marketplace</div>
        <a href="{{ route('home') }}"
           class="nav-item-custom {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="bi bi-search"></i> Annonces
        </a>
        @if($navRole === 'proprietaire' && $m('annonces'))
        <a href="{{ route('annonces.create') }}"
           class="nav-item-custom {{ request()->routeIs('annonces.create') ? 'active' : '' }}">
            <i class="bi bi-megaphone"></i> Publier un bien
        </a>
        @endif
        @endif

        @if(auth()->user()->role === 'admin')
        <div class="sidebar-section">Administration</div>
        <a href="{{ route('admin.proprietaires') }}"
           class="nav-item-custom {{ request()->routeIs('admin.proprietaires*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Propriétaires
            @php $nbProp = \App\Models\User::where('role','proprietaire')->count(); @endphp
            @if($nbProp > 0)
            <span class="badge-nav">{{ $nbProp }}</span>
            @endif
        </a>
        <a href="{{ route('admin.locataires') }}"
           class="nav-item-custom {{ request()->routeIs('admin.locataires*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Locataires
            @php $nbLoc = \App\Models\User::where('role','locataire')->count(); @endphp
            @if($nbLoc > 0)
            <span class="badge-nav">{{ $nbLoc }}</span>
            @endif
        </a>
        <a href="{{ route('admin.agences') }}"
           class="nav-item-custom {{ request()->routeIs('admin.agences*') ? 'active' : '' }}">
            <i class="bi bi-buildings"></i> Agences immobilières
            @php $nbAgents = \App\Models\User::where('role','agent')->count(); @endphp
            @if($nbAgents > 0)
            <span class="badge-nav">{{ $nbAgents }}</span>
            @endif
        </a>
        <a href="{{ route('admin.abonnements') }}"
           class="nav-item-custom {{ request()->routeIs('admin.abonnements*') ? 'active' : '' }}">
            <i class="bi bi-credit-card-2-front"></i> Abonnements
            @php $nbSansAbo = \App\Models\User::where('role','proprietaire')
                ->whereDoesntHave('abonnements', fn($q) => $q->where('statut','actif')->where('date_fin','>=',now()))
                ->count(); @endphp
            @if($nbSansAbo > 0)
            <span class="badge-nav" style="background:#DC2626">{{ $nbSansAbo }}</span>
            @endif
        </a>
        <a href="{{ route('admin.profils') }}"
           class="nav-item-custom {{ request()->routeIs('admin.profils*') ? 'active' : '' }}">
            <i class="bi bi-sliders"></i> Gestion des profils
        </a>
        <a href="{{ route('admin.parametres') }}"
           class="nav-item-custom {{ request()->routeIs('admin.parametres*') ? 'active' : '' }}">
            <i class="bi bi-gear-wide-connected"></i> Config. APIs
            @php
                $smsOk = \App\Models\Parametre::groupeConfigured('sms',['sms_provider','sms_api_key','sms_from']);
                $waOk  = \App\Models\Parametre::groupeConfigured('whatsapp',['wa_provider','wa_api_key','wa_from']);
                $iaOk  = \App\Models\Parametre::groupeConfigured('ia',['ia_api_key']);
                $nbNonConfig = ($smsOk ? 0 : 1) + ($waOk ? 0 : 1) + ($iaOk ? 0 : 1);
            @endphp
            @if($nbNonConfig > 0)
            <span class="badge-nav" style="background:#D97706">{{ $nbNonConfig }}</span>
            @endif
        </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <a href="{{ route('profil.edit') }}" class="user-card" style="text-decoration:none;display:flex;align-items:center;gap:10px;padding:12px 16px;transition:background .15s"
           onmouseover="this.style.background='rgba(234,88,12,.08)'" onmouseout="this.style.background='transparent'">
            @if(auth()->user()->avatar)
            <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="avatar"
                 style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #FDBA74;flex-shrink:0">
            @else
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            @endif
            <div style="flex:1; min-width:0">
                <div class="user-name text-truncate">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
            <i class="bi bi-pencil-square" style="color:#EA580C;font-size:.8rem;flex-shrink:0" title="Modifier le profil"></i>
        </a>
    </div>
</aside>

{{-- ── MAIN ─────────────────────────────────────────────────────────────── --}}
<div class="main-wrapper">
    <header class="topbar">
        <button class="btn-topbar d-lg-none" onclick="openSidebar()" style="border:none">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="page-title">@yield('page-title', 'Mon Bureau')</span>

        @yield('topbar-actions')

        <a href="{{ route('interventions.create') }}" class="btn-topbar" title="Déclarer une intervention">
            <i class="bi bi-tools"></i>
        </a>
        {{-- ── Cloche notifications in-app ─────────────────────────────── --}}
        <div style="position:relative" id="bellWrap">
            <button id="bellBtn" onclick="toggleBellDropdown()" title="Notifications"
                    style="width:36px;height:36px;border-radius:8px;border:1px solid #E5E7EB;background:#fff;
                           display:flex;align-items:center;justify-content:center;color:#78716C;
                           cursor:pointer;font-size:1rem;transition:all .15s;position:relative"
                    onmouseover="this.style.background='#FFF7ED';this.style.color='#F97316';this.style.borderColor='#F97316'"
                    onmouseout="this.style.background='#fff';this.style.color='#78716C';this.style.borderColor='#E5E7EB'">
                <i class="bi bi-bell"></i>
                <span id="bellBadge" style="display:none;position:absolute;top:3px;right:3px;
                      min-width:16px;height:16px;background:#DC2626;color:#fff;border-radius:8px;
                      font-size:.58rem;font-weight:700;line-height:16px;text-align:center;padding:0 3px;
                      border:2px solid #fff"></span>
            </button>

            {{-- Dropdown --}}
            <div id="bellDropdown" style="display:none;position:absolute;top:44px;right:0;width:340px;
                 background:#fff;border:1px solid #E5E7EB;border-radius:12px;
                 box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:2000;overflow:hidden">
                <div style="display:flex;align-items:center;justify-content:space-between;
                             padding:12px 16px;border-bottom:1px solid #F3F4F6">
                    <span style="font-size:.82rem;font-weight:700;color:#1C0A00">Notifications</span>
                    <button onclick="lireTout()" id="btnLireTout"
                            style="font-size:.72rem;color:#EA580C;background:none;border:none;cursor:pointer;font-weight:600;padding:0">
                        Tout marquer lu
                    </button>
                </div>
                <div id="bellList" style="max-height:340px;overflow-y:auto"></div>
                <div style="padding:10px 16px;border-top:1px solid #F3F4F6;text-align:center">
                    <a href="{{ route('notifications.index') }}"
                       style="font-size:.75rem;color:#EA580C;font-weight:600;text-decoration:none">
                        Voir toutes les notifications →
                    </a>
                </div>
            </div>
        </div>
        {{-- ── Menu profil (engrenage) ──────────────────────────────────── --}}
        <div style="position:relative" id="gearWrap">
            <button id="gearBtn" onclick="toggleGearDropdown()" title="Paramètres du profil"
                    style="width:36px;height:36px;border-radius:8px;border:1px solid #E5E7EB;background:#fff;
                           display:flex;align-items:center;justify-content:center;color:#78716C;
                           cursor:pointer;font-size:1rem;transition:all .15s"
                    onmouseover="this.style.background='#FFF7ED';this.style.color='#F97316';this.style.borderColor='#F97316'"
                    onmouseout="this.style.background='#fff';this.style.color='#78716C';this.style.borderColor='#E5E7EB'">
                <i class="bi bi-gear"></i>
            </button>
            <div id="gearDropdown" style="display:none;position:absolute;top:44px;right:0;width:220px;
                 background:#fff;border:1px solid #E5E7EB;border-radius:12px;
                 box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:2000;overflow:hidden">
                {{-- En-tête user --}}
                <div style="padding:14px 16px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;gap:10px">
                    @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="avatar"
                         style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #FDBA74">
                    @else
                    <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#EA580C,#F97316);
                                display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:800;color:#fff;flex-shrink:0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    @endif
                    <div style="min-width:0">
                        <div style="font-size:.82rem;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth()->user()->name }}</div>
                        <div style="font-size:.7rem;color:#9CA3AF;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                {{-- Lien Mon profil --}}
                <a href="{{ route('profil.edit') }}"
                   style="display:flex;align-items:center;gap:10px;padding:12px 16px;
                          font-size:.82rem;font-weight:600;color:#374151;text-decoration:none;
                          transition:background .12s"
                   onmouseover="this.style.background='#FFF7ED';this.style.color='#EA580C'"
                   onmouseout="this.style.background='transparent';this.style.color='#374151'">
                    <i class="bi bi-person-circle" style="font-size:1rem;color:#EA580C"></i>
                    Mon profil
                </a>
                <div style="height:1px;background:#F3F4F6;margin:0 12px"></div>
                {{-- Déconnexion --}}
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit"
                            style="width:100%;display:flex;align-items:center;gap:10px;padding:12px 16px;
                                   font-size:.82rem;font-weight:600;color:#DC2626;background:none;border:none;
                                   cursor:pointer;text-align:left;font-family:inherit;transition:background .12s"
                            onmouseover="this.style.background='#FFF1F2'"
                            onmouseout="this.style.background='transparent'">
                        <i class="bi bi-box-arrow-right" style="font-size:1rem"></i>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="page-content">
        @if(session('success'))
        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3"
             style="background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D;font-size:.83rem">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:inherit;cursor:pointer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        @endif
        @if(session('error'))
        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3"
             style="background:#FFF1F2;border:1px solid #FECDD3;color:#9F1239;font-size:.83rem">
            <i class="bi bi-exclamation-circle-fill fs-5"></i>
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:inherit;cursor:pointer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
function openSidebar()  { document.getElementById('sidebar').classList.add('open'); document.getElementById('overlay').style.display='block'; }
function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('overlay').style.display='none'; }

// ── Dropdown engrenage (profil) ──────────────────────────────────────────────
function toggleGearDropdown() {
    const dd = document.getElementById('gearDropdown');
    dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('gearWrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('gearDropdown').style.display = 'none';
    }
});

// ── Cloche notifications ─────────────────────────────────────────────────────
@auth
(function () {
    const BELL_URL        = '{{ route("notifications.bell") }}';
    const LIRE_TOUT_URL   = '{{ route("notifications.lire-tout") }}';
    const NOTIF_BASE_URL  = '{{ url("/notifications") }}';
    const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let dropdownOpen = false;

    function rafraichir() {
        fetch(BELL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data) return;
                const badge = document.getElementById('bellBadge');
                const list  = document.getElementById('bellList');
                if (!badge || !list) return;

                // Badge
                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }

                // Liste
                if (data.items.length === 0) {
                    list.innerHTML = '<div class="bell-empty"><i class="bi bi-bell-slash" style="font-size:1.5rem;display:block;margin-bottom:8px;opacity:.4"></i>Aucune nouvelle notification</div>';
                } else {
                    list.innerHTML = data.items.map(n => `
                        <a href="${n.url}" class="bell-item unread" data-id="${n.id}" onclick="marquerLue(event,'${n.id}')">
                            <div class="bell-item-icon" style="background:${n.couleur}18;color:${n.couleur}">
                                <i class="bi ${n.icone}"></i>
                            </div>
                            <div style="flex:1;min-width:0">
                                <div class="bell-item-title">${n.titre}</div>
                                <div class="bell-item-msg">${n.message}</div>
                                <div class="bell-item-time">${n.temps}</div>
                            </div>
                        </a>`).join('');
                }
            })
            .catch(() => {});
    }

    window.toggleBellDropdown = function () {
        const dd = document.getElementById('bellDropdown');
        if (!dd) return;
        dropdownOpen = !dropdownOpen;
        dd.style.display = dropdownOpen ? 'block' : 'none';
        if (dropdownOpen) rafraichir();
    };

    window.marquerLue = function (e, id) {
        fetch(NOTIF_BASE_URL + '/' + id + '/lire', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
        }).then(() => rafraichir()).catch(() => {});
    };

    window.lireTout = function () {
        fetch(LIRE_TOUT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
        }).then(() => rafraichir()).catch(() => {});
    };

    // Fermer le dropdown en cliquant ailleurs
    document.addEventListener('click', function (e) {
        if (dropdownOpen && !document.getElementById('bellWrap')?.contains(e.target)) {
            document.getElementById('bellDropdown').style.display = 'none';
            dropdownOpen = false;
        }
    });

    // Premier chargement et polling toutes les 30s
    rafraichir();
    setInterval(rafraichir, 30000);
})();
@endauth
</script>

@else
{{-- Pages publiques (marketplace, annonces, landing) --}}
<nav style="position:sticky;top:0;z-index:100;background:#fff;border-bottom:1px solid #FDBA74;
            display:flex;align-items:center;justify-content:space-between;padding:0 5%;height:60px">
    <a href="{{ route('landing') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none">
        <div style="width:34px;height:34px;background:#EA580C;border-radius:9px;display:flex;
                    align-items:center;justify-content:center;color:#fff;font-size:17px;flex-shrink:0">
            <i class="bi bi-house-door-fill"></i>
        </div>
        <span style="font-weight:700;font-size:1rem;color:#7C2D12">ImmoGest</span>
    </a>
    <div style="display:flex;align-items:center;gap:20px">
        <a href="{{ route('home') }}" style="color:#92400E;font-size:.875rem;font-weight:500;text-decoration:none">Annonces</a>
        <a href="{{ route('login') }}"
           style="padding:7px 16px;border-radius:8px;color:#374151;font-size:.875rem;font-weight:500;
                  border:1px solid #FDBA74;background:#fff;text-decoration:none;transition:all .15s">
            Connexion
        </a>
        <a href="{{ route('register') }}"
           style="padding:7px 18px;border-radius:8px;background:#EA580C;color:#fff;font-size:.875rem;
                  font-weight:600;text-decoration:none">
            Inscription gratuite
        </a>
    </div>
</nav>
@yield('content')
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
