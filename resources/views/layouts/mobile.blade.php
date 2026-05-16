<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#F97316">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ImmoGest">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 180 180'><rect width='180' height='180' rx='36' fill='%23F97316'/><path d='M90 35L135 70v75h-35v-40H80v40H45V70z' fill='white'/></svg>">
    <title>@yield('title', 'ImmoGest') — Immobilier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary:    #F97316;
            --primary-dk: #EA580C;
            --primary-lt: #FFF7ED;
            --success:    #16A34A;
            --warning:    #D97706;
            --danger:     #DC2626;
            --text-main:  #111827;
            --text-muted: #6B7280;
            --border:     #E5E7EB;
            --bg:         #F9FAFB;
            --card-bg:    #ffffff;
            --nav-h:      64px;
            --top-h:      56px;
            --safe-b:     env(safe-area-inset-bottom, 0px);
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text-main);
            margin: 0;
            padding-bottom: calc(var(--nav-h) + var(--safe-b));
            -webkit-tap-highlight-color: transparent;
            overscroll-behavior: contain;
        }

        /* ── Top header ─────────────────────────────────────── */
        .mob-header {
            position: sticky; top: 0; z-index: 100;
            background: #fff; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
            padding: 0 16px; height: var(--top-h);
        }
        .mob-logo { display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .mob-logo-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: var(--primary); display: flex; align-items: center;
            justify-content: center; color: #fff; font-size: 1rem; flex-shrink: 0;
        }
        .mob-logo-name { font-weight: 800; font-size: .9rem; color: var(--text-main); }
        .mob-header-title { flex: 1; font-size: .95rem; font-weight: 700; color: var(--text-main); }

        /* ── Bottom navigation ──────────────────────────────── */
        .mob-nav {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;
            background: #fff; border-top: 1px solid var(--border);
            display: flex; align-items: stretch;
            padding-bottom: var(--safe-b);
            height: calc(var(--nav-h) + var(--safe-b));
        }
        .mob-nav-item {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 3px; text-decoration: none; padding: 8px 4px;
            color: var(--text-muted); font-size: .65rem; font-weight: 600;
            transition: color .15s; position: relative; min-width: 0;
        }
        .mob-nav-item i { font-size: 1.3rem; line-height: 1; }
        .mob-nav-item.active { color: var(--primary); }
        .mob-nav-item .nav-badge {
            position: absolute; top: 6px; right: calc(50% - 16px);
            background: var(--danger); color: #fff; border-radius: 10px;
            font-size: .55rem; padding: 1px 5px; font-weight: 700;
        }

        /* ── Cards ──────────────────────────────────────────── */
        .card-mob {
            background: var(--card-bg); border-radius: 16px;
            overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.07);
        }
        .card-mob-img { position: relative; overflow: hidden; }
        .card-mob-img img { width: 100%; height: 200px; object-fit: cover; display: block; }
        .card-mob-img .placeholder-img {
            width: 100%; height: 200px; background: linear-gradient(135deg,#FED7AA,#FEF3C7);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; color: var(--primary);
        }
        .card-mob-badge {
            position: absolute; top: 10px; left: 10px;
            padding: 3px 8px; border-radius: 8px; font-size: .67rem; font-weight: 700;
        }
        .badge-location { background: var(--primary); color: #fff; }
        .badge-vente    { background: #1E40AF; color: #fff; }
        .badge-court-terme { background: #7C3AED; color: #fff; }
        .card-mob-fav {
            position: absolute; top: 10px; right: 10px;
            width: 32px; height: 32px; border-radius: 50%;
            background: rgba(255,255,255,.9); display: flex; align-items: center;
            justify-content: center; cursor: pointer; border: none;
            font-size: 1rem; color: var(--text-muted); transition: all .15s;
        }
        .card-mob-fav.fav { color: #EF4444; }
        .card-mob-body { padding: 12px 14px 14px; }
        .card-mob-price {
            font-size: 1.1rem; font-weight: 800; color: var(--primary);
            display: flex; align-items: baseline; gap: 3px;
        }
        .card-mob-price .price-unit { font-size: .7rem; font-weight: 500; color: var(--text-muted); }
        .card-mob-title { font-size: .85rem; font-weight: 600; color: var(--text-main); margin: 4px 0; line-height: 1.35; }
        .card-mob-loc { font-size: .75rem; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
        .card-mob-meta { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .card-mob-meta span { font-size: .72rem; color: var(--text-muted); display: flex; align-items: center; gap: 3px; }

        /* ── Buttons ─────────────────────────────────────────── */
        .btn-mob-primary {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            background: var(--primary); color: #fff; border: none;
            padding: 14px 20px; border-radius: 14px; font-size: .9rem;
            font-weight: 700; width: 100%; cursor: pointer; text-decoration: none;
            transition: background .15s; font-family: inherit;
        }
        .btn-mob-primary:hover { background: var(--primary-dk); color: #fff; }
        .btn-mob-primary:disabled { background: #D1D5DB; cursor: not-allowed; }
        .btn-mob-outline {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            background: transparent; color: var(--primary);
            border: 2px solid var(--primary); padding: 13px 20px;
            border-radius: 14px; font-size: .9rem; font-weight: 600;
            width: 100%; cursor: pointer; text-decoration: none;
            transition: all .15s; font-family: inherit;
        }
        .btn-mob-outline:hover { background: var(--primary-lt); color: var(--primary); }
        .btn-mob-ghost {
            display: inline-flex; align-items: center; gap: 6px;
            background: none; border: none; color: var(--text-muted);
            font-size: .82rem; cursor: pointer; padding: 6px 4px;
            font-family: inherit; text-decoration: none;
        }

        /* ── Input ───────────────────────────────────────────── */
        .input-mob {
            width: 100%; padding: 13px 16px; border: 1.5px solid var(--border);
            border-radius: 12px; font-size: .88rem; font-family: inherit;
            background: #fff; color: var(--text-main); outline: none;
            transition: border-color .15s;
        }
        .input-mob:focus { border-color: var(--primary); }
        .input-mob-label { font-size: .78rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px; display: block; }

        /* ── Chips / filtres ─────────────────────────────────── */
        .chip {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 6px 14px; border-radius: 20px; font-size: .78rem; font-weight: 600;
            background: #fff; border: 1.5px solid var(--border); color: var(--text-muted);
            cursor: pointer; white-space: nowrap; text-decoration: none; transition: all .15s;
        }
        .chip.active, .chip:hover { background: var(--primary-lt); border-color: var(--primary); color: var(--primary); }
        .chip-scroll { display: flex; gap: 8px; overflow-x: auto; padding: 4px 0;
            scrollbar-width: none; -ms-overflow-style: none; }
        .chip-scroll::-webkit-scrollbar { display: none; }

        /* ── Section heading ─────────────────────────────────── */
        .section-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .section-head h3 { font-size: .88rem; font-weight: 800; margin: 0; }
        .section-head a { font-size: .75rem; color: var(--primary); text-decoration: none; font-weight: 600; }

        /* ── Search bar ─────────────────────────────────────── */
        .search-bar {
            position: relative; display: flex; align-items: center;
        }
        .search-bar input {
            width: 100%; padding: 13px 16px 13px 44px;
            border: 1.5px solid var(--border); border-radius: 14px;
            font-size: .88rem; font-family: inherit; background: #fff;
            outline: none; transition: border-color .15s;
        }
        .search-bar input:focus { border-color: var(--primary); }
        .search-bar .search-icon {
            position: absolute; left: 14px; color: var(--text-muted); font-size: 1.1rem;
            pointer-events: none;
        }
        .search-bar .search-btn {
            position: absolute; right: 6px; background: var(--primary);
            color: #fff; border: none; border-radius: 10px; padding: 6px 12px;
            cursor: pointer; font-size: .78rem; font-weight: 600;
        }

        /* ── Alert ───────────────────────────────────────────── */
        .alert-mob {
            padding: 12px 16px; border-radius: 12px; font-size: .82rem;
            display: flex; gap: 10px; align-items: flex-start; margin-bottom: 12px;
        }
        .alert-mob-success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .alert-mob-danger  { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
        .alert-mob-info    { background: var(--primary-lt); color: #C2410C; border: 1px solid #FED7AA; }

        /* ── Skeleton loader ─────────────────────────────────── */
        @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
        .skeleton {
            background: linear-gradient(90deg,#F3F4F6 25%,#E5E7EB 50%,#F3F4F6 75%);
            background-size: 200% 100%; animation: shimmer 1.4s infinite;
            border-radius: 8px;
        }

        /* ── Page padding ─────────────────────────────────────── */
        .mob-page { padding: 16px; }
        .mob-page-xl { padding: 20px; }
    </style>
    @stack('styles')
</head>
<body>

{{-- ── TOP HEADER ─────────────────────────────────────────────────────────── --}}
<header class="mob-header">
    @hasSection('header-left')
        @yield('header-left')
    @else
        <a href="{{ route('mobile.index') }}" class="mob-logo">
            <div class="mob-logo-icon"><i class="bi bi-house-door-fill"></i></div>
            <span class="mob-logo-name">ImmoGest</span>
        </a>
    @endif
    <span class="mob-header-title">@yield('page-title', '')</span>
    @yield('header-right')
</header>

{{-- ── FLASH MESSAGES ───────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="alert-mob alert-mob-success mx-3 mt-3">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="alert-mob alert-mob-danger mx-3 mt-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    {{ $errors->first() }}
</div>
@endif

{{-- ── CONTENT ─────────────────────────────────────────────────────────────── --}}
@yield('content')

{{-- ── BOTTOM NAV ──────────────────────────────────────────────────────────── --}}
<nav class="mob-nav">
    <a href="{{ route('mobile.index') }}"
       class="mob-nav-item {{ request()->routeIs('mobile.index') ? 'active' : '' }}">
        <i class="bi bi-house{{ request()->routeIs('mobile.index') ? '-fill' : '' }}"></i>
        <span>Accueil</span>
    </a>
    <a href="{{ route('mobile.listings') }}"
       class="mob-nav-item {{ request()->routeIs('mobile.listings') ? 'active' : '' }}">
        <i class="bi bi-search"></i>
        <span>Recherche</span>
    </a>
    <a href="{{ route('mobile.listings', ['mode'=>'court_terme']) }}"
       class="mob-nav-item {{ (request()->routeIs('mobile.listings') && request()->mode === 'court_terme') ? 'active' : '' }}">
        <i class="bi bi-calendar3{{ (request()->routeIs('mobile.listings') && request()->mode === 'court_terme') ? '-fill' : '' }}"></i>
        <span>Séjours</span>
    </a>
    <a href="{{ route('mobile.mes-reservations') }}"
       class="mob-nav-item {{ request()->routeIs('mobile.mes-reservations') ? 'active' : '' }}">
        <i class="bi bi-bag{{ request()->routeIs('mobile.mes-reservations') ? '-fill' : '' }}"></i>
        <span>Réservations</span>
    </a>
</nav>

<script>
// ── PWA Service Worker ──
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/immo/public/sw.js').catch(() => {});
    });
}

// ── Favoris (localStorage) ──
function toggleFav(annonceId, btn) {
    const favs = JSON.parse(localStorage.getItem('immo_favs') || '[]');
    const idx = favs.indexOf(annonceId);
    if (idx === -1) {
        favs.push(annonceId);
        btn.classList.add('fav');
        btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
    } else {
        favs.splice(idx, 1);
        btn.classList.remove('fav');
        btn.innerHTML = '<i class="bi bi-heart"></i>';
    }
    localStorage.setItem('immo_favs', JSON.stringify(favs));
}

function initFavs() {
    const favs = JSON.parse(localStorage.getItem('immo_favs') || '[]');
    document.querySelectorAll('[data-fav-id]').forEach(btn => {
        const id = parseInt(btn.dataset.favId);
        if (favs.includes(id)) {
            btn.classList.add('fav');
            btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
        }
    });
}
document.addEventListener('DOMContentLoaded', initFavs);
</script>
@stack('scripts')
</body>
</html>
