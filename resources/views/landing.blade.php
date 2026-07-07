<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImmoGest — Logiciel gratuit de gestion locative</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary:#EA580C; --primary-dk:#C2410C; --primary-lt:#FFF7ED; }
        *  { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Inter',system-ui,sans-serif; color:#111827; background:#fff; }
        a   { text-decoration:none; }

        /* NAV */
        nav {
            position:sticky; top:0; z-index:100; background:#fff;
            border-bottom:1px solid #FDBA74;
            display:flex; align-items:center; justify-content:space-between;
            padding:0 6%; height:64px;
        }
        .nav-brand { display:flex; align-items:center; gap:10px; }
        .nav-logo  { width:36px; height:36px; background:var(--primary); border-radius:9px;
                     display:flex; align-items:center; justify-content:center; color:#fff; font-size:18px; }
        .nav-name  { font-weight:700; font-size:1.05rem; color:#7C2D12; }
        .nav-links a { color:#92400E; font-size:.875rem; font-weight:500; margin-left:28px; transition:color .15s; }
        .nav-links a:hover { color:#EA580C; }
        .nav-cta   { display:flex; align-items:center; gap:10px; }
        .btn-nav-login  { padding:7px 16px; border-radius:8px; color:#374151; font-size:.875rem; font-weight:500; border:1px solid #FDBA74; background:#fff; transition:all .15s; }
        .btn-nav-login:hover { border-color:#EA580C; color:#EA580C; }
        .btn-nav-cta { padding:7px 18px; border-radius:8px; background:var(--primary); color:#fff; font-size:.875rem; font-weight:600; transition:background .15s; }
        .btn-nav-cta:hover { background:var(--primary-dk); }

        /* HERO */
        .hero { padding:80px 6% 0; text-align:center; background:linear-gradient(180deg,#FFF7ED 0%,#fff 100%); }
        .hero-badge { display:inline-flex; align-items:center; gap:6px; background:var(--primary-lt); color:var(--primary);
                      padding:5px 14px; border-radius:20px; font-size:.78rem; font-weight:600; margin-bottom:24px;
                      border:1px solid #FED7AA; }
        .hero h1 { font-size:clamp(2rem,4vw,3rem); font-weight:800; line-height:1.15; color:#1C0A00; max-width:760px; margin:0 auto 20px; }
        .hero h1 span { color:var(--primary); }
        .hero p  { font-size:1.05rem; color:#6B7280; max-width:560px; margin:0 auto 36px; line-height:1.7; }
        .hero-btns { display:flex; justify-content:center; flex-wrap:wrap; gap:12px; margin-bottom:60px; }
        .btn-hero-primary {
            display:inline-flex; align-items:center; gap:8px;
            background:var(--primary); color:#fff; padding:13px 28px;
            border-radius:10px; font-size:.95rem; font-weight:700; transition:background .15s;
        }
        .btn-hero-primary:hover { background:var(--primary-dk); color:#fff; }
        .btn-hero-ghost {
            display:inline-flex; align-items:center; gap:8px;
            border:2px solid #FDBA74; color:#92400E; padding:11px 24px;
            border-radius:10px; font-size:.95rem; font-weight:600; transition:all .15s;
        }
        .btn-hero-ghost:hover { border-color:#EA580C; color:#EA580C; }
        .hero-social-proof { color:#9CA3AF; font-size:.8rem; margin-top:0; }
        .hero-social-proof strong { color:#374151; }

        /* DASHBOARD MOCKUP */
        .hero-mockup {
            max-width:900px; margin:0 auto;
            border-radius:16px 16px 0 0;
            border:1px solid #FDBA74; border-bottom:none;
            box-shadow:0 -8px 40px rgba(194,65,12,.15);
            overflow:hidden; background:#FFF7ED;
        }
        .mockup-bar {
            background:#7C2D12; padding:10px 16px;
            display:flex; align-items:center; gap:8px;
        }
        .mockup-dot { width:10px;height:10px;border-radius:50%; }
        .mockup-body { display:flex; height:340px; }
        .mockup-sidebar { width:200px; background:linear-gradient(180deg,#FFEDD5,#FED7AA); border-right:1px solid #FDBA74; padding:16px 0; flex-shrink:0; }
        .mockup-nav-item {
            padding:8px 16px; font-size:.72rem; color:#92400E;
            display:flex; align-items:center; gap:8px; margin:2px 8px; border-radius:6px;
        }
        .mockup-nav-item.active { background:rgba(234,88,12,.18); color:#EA580C; font-weight:700; box-shadow:inset 3px 0 0 #EA580C; }
        .mockup-nav-item i { font-size:.85rem; }
        .mockup-content { flex:1; padding:16px; overflow:hidden; background:#fff; }
        .mockup-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:12px; }
        .mockup-stat { background:#fff; border-radius:8px; padding:12px; border:1px solid #E5E7EB; }
        .mockup-stat .val { font-size:1.1rem; font-weight:700; }
        .mockup-stat .lbl { font-size:.65rem; color:#9CA3AF; margin-top:2px; }
        .mockup-table { background:#fff; border-radius:8px; border:1px solid #E5E7EB; overflow:hidden; }
        .mockup-th { background:#FFF7ED; padding:8px 12px; font-size:.65rem; color:#92400E; text-transform:uppercase; letter-spacing:.05em; display:flex; gap:24px; }
        .mockup-row { padding:9px 12px; display:flex; gap:24px; align-items:center; font-size:.72rem; border-top:1px solid #F3F4F6; }
        .mockup-badge { padding:2px 8px; border-radius:12px; font-size:.6rem; font-weight:600; }

        /* STATS */
        .stats-bar { background:linear-gradient(135deg,#EA580C 0%,#C2410C 100%); padding:40px 6%; }
        .stats-bar .row { display:flex; flex-wrap:wrap; justify-content:center; gap:40px; text-align:center; }
        .stats-bar .val { font-size:2rem; font-weight:800; color:#fff; }
        .stats-bar .lbl { font-size:.82rem; color:#FED7AA; margin-top:4px; }

        /* HOW IT WORKS */
        .section { padding:80px 6%; }
        .section-label { color:var(--primary); font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; margin-bottom:12px; }
        .section-title { font-size:clamp(1.5rem,2.5vw,2rem); font-weight:800; line-height:1.25; color:#1C0A00; }
        .section-sub   { color:#6B7280; font-size:.95rem; margin-top:12px; line-height:1.7; max-width:520px; }

        .steps { display:flex; gap:32px; margin-top:48px; flex-wrap:wrap; }
        .step  { flex:1; min-width:240px; }
        .step-num {
            width:44px; height:44px; background:var(--primary-lt); color:var(--primary);
            border-radius:12px; display:flex; align-items:center; justify-content:center;
            font-size:1.1rem; font-weight:800; margin-bottom:16px;
            border:1px solid #FED7AA;
        }
        .step h3 { font-size:.95rem; font-weight:700; margin-bottom:8px; color:#1C0A00; }
        .step p  { font-size:.83rem; color:#6B7280; line-height:1.6; }

        /* FEATURES */
        .features-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:24px; margin-top:48px; }
        .feature-card {
            border:1px solid #E5E7EB; border-radius:12px; padding:24px;
            transition:box-shadow .2s, border-color .2s;
        }
        .feature-card:hover { box-shadow:0 8px 24px rgba(194,65,12,.1); border-color:#FED7AA; }
        .feature-icon {
            width:48px; height:48px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.3rem; margin-bottom:16px;
        }
        .feature-card h3 { font-size:.9rem; font-weight:700; margin-bottom:8px; color:#1C0A00; }
        .feature-card p  { font-size:.8rem; color:#6B7280; line-height:1.6; }

        /* TESTIMONIALS */
        .testimonials { background:#FFF7ED; padding:80px 6%; }
        .testi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:20px; margin-top:48px; }
        .testi-card { background:#fff; border-radius:12px; padding:24px; border:1px solid #FDBA74; }
        .testi-stars { color:#F59E0B; font-size:.9rem; margin-bottom:12px; }
        .testi-text  { font-size:.83rem; color:#374151; line-height:1.7; margin-bottom:16px; font-style:italic; }
        .testi-author { display:flex; align-items:center; gap:10px; }
        .testi-avatar { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem; }
        .testi-name { font-size:.8rem; font-weight:600; }
        .testi-role { font-size:.72rem; color:#9CA3AF; }

        /* CTA BOTTOM */
        .cta-section { padding:80px 6%; text-align:center; background:linear-gradient(135deg,#EA580C 0%,#C2410C 100%); }
        .cta-section h2 { font-size:clamp(1.5rem,3vw,2.2rem); font-weight:800; color:#fff; margin-bottom:16px; }
        .cta-section p  { color:#FED7AA; font-size:.95rem; margin-bottom:36px; }
        .btn-cta-white  {
            display:inline-flex; align-items:center; gap:8px;
            background:#fff; color:var(--primary); padding:13px 32px;
            border-radius:10px; font-size:.95rem; font-weight:700; transition:all .15s;
        }
        .btn-cta-white:hover { background:#FFF7ED; color:var(--primary-dk); }

        /* FOOTER */
        footer { background:#431407; padding:48px 6% 28px; color:#C2410C; }
        .footer-grid { display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:40px; margin-bottom:40px; }
        footer h4 { color:#FED7AA; font-size:.85rem; font-weight:700; margin-bottom:12px; }
        footer ul { list-style:none; }
        footer ul li { margin-bottom:8px; }
        footer ul li a { color:#C2410C; font-size:.8rem; transition:color .15s; }
        footer ul li a:hover { color:#FED7AA; }
        .footer-brand { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
        .footer-desc  { font-size:.8rem; line-height:1.7; color:#92400E; }
        .footer-bottom { border-top:1px solid #7C2D12; padding-top:24px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px; font-size:.75rem; color:#92400E; }

        @media(max-width:768px) {
            .nav-links, .nav-cta .btn-nav-login { display:none; }
            .footer-grid { grid-template-columns:1fr 1fr; }
            .mockup-sidebar { display:none; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav>
    <div class="nav-brand">
        <div class="nav-logo"><i class="bi bi-house-door-fill"></i></div>
        <span class="nav-name">ImmoGest</span>
    </div>
    <div class="nav-links">
        <a href="#fonctionnalites">Fonctionnalités</a>
        <a href="#comment">Comment ça marche</a>
        <a href="#avis">Avis</a>
        <a href="{{ route('home') }}">Annonces</a>
        <a href="{{ route('abonnements.formules') }}">Tarifs</a>
    </div>
    <div class="nav-cta">
        <a href="{{ route('login') }}" class="btn-nav-login">Connexion</a>
        <a href="{{ route('register') }}" class="btn-nav-cta">Ouvrir un compte gratuit</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-badge"><i class="bi bi-stars"></i> Nouveau : Génération automatique de quittances</div>
    <h1>Gérez vos biens immobiliers avec le meilleur logiciel <span>gratuit</span> de gestion locative</h1>
    <p>Baux, loyers, quittances, interventions — tout en un seul endroit. Démarrez en 3 minutes, sans engagement.</p>
    <div class="hero-btns">
        <a href="{{ route('register') }}" class="btn-hero-primary">
            <i class="bi bi-rocket-takeoff"></i> Ouvrir un compte gratuit
        </a>
        <a href="#comment" class="btn-hero-ghost">
            <i class="bi bi-play-circle"></i> Voir comment ça marche
        </a>
    </div>
    <p class="hero-social-proof"><strong>2 847</strong> propriétaires inscrits ce mois · <strong>4.9/5</strong> de satisfaction</p>

    <!-- Dashboard mockup -->
    <div class="hero-mockup mt-5">
        <div class="mockup-bar">
            <div class="mockup-dot" style="background:#FF5F57"></div>
            <div class="mockup-dot" style="background:#FFBD2E"></div>
            <div class="mockup-dot" style="background:#28C840"></div>
            <span style="flex:1;text-align:center;color:#FED7AA;font-size:.7rem">ImmoGest — Mon Bureau</span>
        </div>
        <div class="mockup-body">
            <div class="mockup-sidebar">
                <div style="padding:12px 16px;margin-bottom:8px">
                    <div style="font-size:.65rem;color:#C2410C;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">Principal</div>
                    <div class="mockup-nav-item active"><i class="bi bi-speedometer2"></i> Mon Bureau</div>
                    <div style="font-size:.65rem;color:#C2410C;text-transform:uppercase;letter-spacing:.06em;margin:12px 0 8px">Patrimoine</div>
                    <div class="mockup-nav-item"><i class="bi bi-buildings"></i> Mes biens</div>
                    <div class="mockup-nav-item"><i class="bi bi-people"></i> Locataires</div>
                    <div class="mockup-nav-item"><i class="bi bi-file-earmark-text"></i> Locations</div>
                    <div style="font-size:.65rem;color:#C2410C;text-transform:uppercase;letter-spacing:.06em;margin:12px 0 8px">Finance</div>
                    <div class="mockup-nav-item"><i class="bi bi-wallet2"></i> Paiements</div>
                    <div class="mockup-nav-item"><i class="bi bi-tools"></i> Interventions</div>
                </div>
            </div>
            <div class="mockup-content">
                <div style="font-size:.8rem;font-weight:700;margin-bottom:10px;color:#1C0A00">Bonjour Koné 👋 — Mai 2026</div>
                <div class="mockup-stats">
                    <div class="mockup-stat">
                        <div class="val" style="color:#EA580C">2 100 000 FCFA</div>
                        <div class="lbl">Loyers du mois</div>
                    </div>
                    <div class="mockup-stat">
                        <div class="val" style="color:#16A34A">1 900 000 FCFA</div>
                        <div class="lbl">Encaissés</div>
                    </div>
                    <div class="mockup-stat">
                        <div class="val" style="color:#DC2626">200 000 FCFA</div>
                        <div class="lbl">En retard</div>
                    </div>
                </div>
                <div class="mockup-table">
                    <div class="mockup-th">
                        <span style="flex:2">Locataire</span>
                        <span style="flex:2">Bien</span>
                        <span style="flex:1">Montant</span>
                        <span style="flex:1">Statut</span>
                    </div>
                    <div class="mockup-row">
                        <span style="flex:2;font-weight:500">Mariam Traoré</span>
                        <span style="flex:2;color:#6B7280">Appt F3 Cocody</span>
                        <span style="flex:1;font-weight:600">425 000</span>
                        <span class="mockup-badge" style="background:#DCFCE7;color:#15803D;flex:1">Payé</span>
                    </div>
                    <div class="mockup-row">
                        <span style="flex:2;font-weight:500">Moussa Diallo</span>
                        <span style="flex:2;color:#6B7280">Studio Plateau</span>
                        <span style="flex:1;font-weight:600">325 000</span>
                        <span class="mockup-badge" style="background:#FEE2E2;color:#B91C1C;flex:1">Retard</span>
                    </div>
                    <div class="mockup-row">
                        <span style="flex:2;font-weight:500">Fatou Camara</span>
                        <span style="flex:2;color:#6B7280">Villa Marcory</span>
                        <span style="flex:1;font-weight:600">600 000</span>
                        <span class="mockup-badge" style="background:#DCFCE7;color:#15803D;flex:1">Payé</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<div class="stats-bar">
    <div class="row">
        <div><div class="val">15 000+</div><div class="lbl">Propriétaires actifs</div></div>
        <div><div class="val">48 000+</div><div class="lbl">Biens gérés</div></div>
        <div><div class="val">2,1 Mrd FCFA</div><div class="lbl">Loyers traités/mois</div></div>
        <div><div class="val">4.9/5</div><div class="lbl">Satisfaction client</div></div>
    </div>
</div>

<!-- HOW IT WORKS -->
<section class="section" id="comment">
    <div style="text-align:center;max-width:600px;margin:0 auto">
        <div class="section-label">Comment ça marche</div>
        <div class="section-title">Démarrez en 3 étapes simples</div>
        <div class="section-sub" style="margin:12px auto 0">Pas besoin de formation. Notre interface intuitive vous guide pas à pas.</div>
    </div>
    <div class="steps">
        <div class="step">
            <div class="step-num">1</div>
            <h3>Créez votre bien</h3>
            <p>Ajoutez votre appartement, maison, studio ou local commercial. Renseignez les caractéristiques, les photos et le DPE.</p>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <h3>Ajoutez vos locataires</h3>
            <p>Créez la fiche de chaque locataire avec ses coordonnées. Gérez les colocataires, garants et contacts d'urgence.</p>
        </div>
        <div class="step">
            <div class="step-num">3</div>
            <h3>Créez la location</h3>
            <p>Associez un bien à un locataire. Le bail est créé, les paiements générés automatiquement et les quittances émises dès réception.</p>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="section" id="fonctionnalites" style="background:#FFF7ED;padding-top:0">
    <div style="text-align:center;max-width:600px;margin:0 auto;padding-top:80px">
        <div class="section-label">Fonctionnalités</div>
        <div class="section-title">Tout ce dont vous avez besoin</div>
    </div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon" style="background:#FFF7ED;color:#EA580C"><i class="bi bi-buildings"></i></div>
            <h3>Gestion du patrimoine</h3>
            <p>Centralisez tous vos biens : appartements, maisons, commerces. Photos, DPE, historique complet.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#F0FDF4;color:#16A34A"><i class="bi bi-wallet2"></i></div>
            <h3>Suivi des loyers</h3>
            <p>Paiements mensuels générés automatiquement. Alertes retard, encaissement en 1 clic, historique complet.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#FFFBEB;color:#D97706"><i class="bi bi-receipt"></i></div>
            <h3>Quittances automatiques</h3>
            <p>Chaque paiement encaissé génère une quittance numérotée téléchargeable par le locataire.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#FFF1F2;color:#E11D48"><i class="bi bi-tools"></i></div>
            <h3>Gestion des travaux</h3>
            <p>Suivez toutes les interventions : urgences, entretiens, devis. Historique coût par bien.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#F5F3FF;color:#7C3AED"><i class="bi bi-megaphone"></i></div>
            <h3>Publication d'annonces</h3>
            <p>Publiez vos biens en location ou à la vente sur notre marketplace intégrée. Gérez les visites.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#ECFDF5;color:#059669"><i class="bi bi-shield-check"></i></div>
            <h3>Documents sécurisés</h3>
            <p>Stockage chiffré de vos baux, états des lieux, diagnostics. Accès sécurisé pour les locataires.</p>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="testimonials" id="avis">
    <div style="text-align:center;max-width:600px;margin:0 auto">
        <div class="section-label">Avis clients</div>
        <div class="section-title">Ce que disent nos utilisateurs</div>
    </div>
    <div class="testi-grid">
        <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p class="testi-text">"Tellement utile et pratique ! J'ai enfin arrêté les tablettes Excel. Tout est centralisé, les quittances se génèrent toutes seules."</p>
            <div class="testi-author">
                <div class="testi-avatar" style="background:#FFEDD5;color:#C2410C">KD</div>
                <div><div class="testi-name">Kouadio D.</div><div class="testi-role">Propriétaire · 6 biens · Abidjan</div></div>
            </div>
        </div>
        <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p class="testi-text">"Incontournable pour le bailleur. La relance automatique des loyers en retard m'a économisé beaucoup de stress."</p>
            <div class="testi-author">
                <div class="testi-avatar" style="background:#DCFCE7;color:#15803D">SF</div>
                <div><div class="testi-name">Sali F.</div><div class="testi-role">Propriétaire · 3 biens · Dakar</div></div>
            </div>
        </div>
        <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p class="testi-text">"Logiciel merveilleux ! Interface très claire, prise en main immédiate. Je recommande à tous les propriétaires bailleurs."</p>
            <div class="testi-author">
                <div class="testi-avatar" style="background:#FEF3C7;color:#B45309">AR</div>
                <div><div class="testi-name">Amadou R.</div><div class="testi-role">Gestionnaire · 12 biens · Bamako</div></div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <h2>Prêt à simplifier votre gestion locative ?</h2>
    <p>Rejoignez 15 000+ propriétaires qui font confiance à ImmoGest. Gratuit, sans engagement.</p>
    <a href="{{ route('register') }}" class="btn-cta-white">
        <i class="bi bi-rocket-takeoff"></i> Ouvrir mon compte gratuit
    </a>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-grid">
        <div>
            <div class="footer-brand">
                <div style="width:32px;height:32px;background:#EA580C;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px">
                    <i class="bi bi-house-door-fill"></i>
                </div>
                <span style="font-weight:700;color:#FED7AA;font-size:.95rem">ImmoGest</span>
            </div>
            <p class="footer-desc">Le logiciel de gestion locative gratuit pour les propriétaires bailleurs. Simple, complet, sécurisé.</p>
        </div>
        <div>
            <h4>Produit</h4>
            <ul>
                <li><a href="#fonctionnalites">Fonctionnalités</a></li>
                <li><a href="#comment">Comment ça marche</a></li>
                <li><a href="{{ route('home') }}">Marketplace</a></li>
                <li><a href="{{ route('register') }}">Inscription gratuite</a></li>
            </ul>
        </div>
        <div>
            <h4>Ressources</h4>
            <ul>
                <li><a href="#">Centre d'aide</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="tel:010142004609">Tél : 01 01 42 00 46 09</a></li>
                <li><a href="https://wa.me/22510142004609" target="_blank">WhatsApp : +225 01 01 42 00 46 09</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>
        <div>
            <h4>Légal</h4>
            <ul>
                <li><a href="#">Conditions d'utilisation</a></li>
                <li><a href="#">Politique de confidentialité</a></li>
                <li><a href="#">Mentions légales</a></li>
                <li><a href="#">RGPD</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© {{ date('Y') }} ImmoGest. Tous droits réservés.</span>
        <span>🔒 Données hébergées · SSL · RGPD</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
