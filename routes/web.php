<?php

use App\Http\Controllers\Admin\GestionController;
use App\Http\Controllers\Admin\ParametreController;
use App\Http\Controllers\Admin\ProfilConfigController;
use App\Http\Controllers\Agent\PublicationController;
use App\Http\Controllers\AgentIAController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\Mobile\MobileController;
use App\Http\Controllers\Mobile\ReservationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BienController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\LocataireController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\PaiementController;
use Illuminate\Support\Facades\Route;

// ─── Application mobile (PWA publique) ───────────────────────────────────────
Route::prefix('m')->name('mobile.')->group(function () {
    Route::get('/',                                [MobileController::class,     'index'])->name('index');
    Route::get('/annonces',                        [MobileController::class,     'listings'])->name('listings');
    Route::get('/annonces/{annonce}',              [MobileController::class,     'detail'])->name('detail');
    Route::get('/annonces/{annonce}/dispo',        [MobileController::class,     'disponibilites'])->name('dispo');
    Route::get('/reserver/{annonce}',              [ReservationController::class,'create'])->name('reserver');
    Route::post('/reserver',                       [ReservationController::class,'store'])->name('reserver.store');
    Route::get('/paiement/{token}',                [ReservationController::class,'paiement'])->name('paiement');
    Route::post('/paiement/{token}/initier',       [ReservationController::class,'initier'])->name('paiement.initier');
    Route::get('/paiement/retour',                 [ReservationController::class,'retour'])->name('paiement.retour');
    Route::post('/paiement/webhook',               [ReservationController::class,'webhook'])->name('paiement.webhook')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::get('/confirmation/{token}',            [ReservationController::class,'confirmation'])->name('confirmation');
    Route::get('/mes-reservations',                [ReservationController::class,'mesReservations'])->name('mes-reservations');
});

// ─── Webhooks opérateurs mobiles (public, sans CSRF, sans auth) ──────────────
Route::post('/webhooks/orange-money', [AbonnementController::class, 'webhookOrangeMoney'])
    ->name('webhooks.orange-money')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/webhooks/mtn-momo', [AbonnementController::class, 'webhookMtnMomo'])
    ->name('webhooks.mtn-momo')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/webhooks/wave', [AbonnementController::class, 'webhookWave'])
    ->name('webhooks.wave')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ─── Landing & pages publiques ───────────────────────────────────────────────
Route::get('/', fn() => view('landing'))->name('landing');
Route::get('/marketplace', [AnnonceController::class, 'index'])->name('home');
Route::get('/annonces/{annonce}', [AnnonceController::class, 'show'])->name('annonces.show')->whereNumber('annonce');

// ─── Authentification ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Réinitialisation du mot de passe
    Route::get('/mot-de-passe-oublie',          [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/mot-de-passe-oublie',         [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser/{token}',        [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe',  [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Zone authentifiée ────────────────────────────────────────────────────────
Route::middleware(['auth', 'check.abonnement'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil/edit',      [ProfilController::class, 'edit'])->name('profil.edit');
    Route::post('/profil/info',     [ProfilController::class, 'updateInfo'])->name('profil.update-info');
    Route::post('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.update-password');
    Route::post('/profil/avatar',   [ProfilController::class, 'updateAvatar'])->name('profil.update-avatar');
    Route::patch('/profil/devise',  [ProfilController::class, 'updateDevise'])->name('profil.devise');

    // ─── Abonnements propriétaire ────────────────────────────────────────────
    Route::get('/abonnements',                             [AbonnementController::class, 'index'])->name('abonnements.index');
    Route::post('/abonnements/initier',                    [AbonnementController::class, 'initier'])->name('abonnements.initier');
    Route::get('/abonnements/retour',                      [AbonnementController::class, 'retour'])->name('abonnements.retour');
    Route::get('/abonnements/statut/{reference}',          [AbonnementController::class, 'pollStatut'])->name('abonnements.poll-statut');

    // ─── Espace Agent immobilier ─────────────────────────────────────────────
    Route::prefix('agent')->name('agent.')->group(function () {
        Route::get('/mes-annonces',               [PublicationController::class, 'mesAnnonces'])->name('mes-annonces');
        Route::get('/publier',                    [PublicationController::class, 'create'])->name('publier');
        Route::post('/publier',                   [PublicationController::class, 'store'])->name('publier.store');
        Route::patch('/annonces/{annonce}/toggle',[PublicationController::class, 'toggleStatut'])->name('annonces.toggle');
    });

    // Biens immobiliers
    Route::resource('biens', BienController::class);

    // Locataires (module dédié — propriétaire)
    Route::resource('locataires', LocataireController::class);

    // Locations / baux
    Route::resource('locations', LocationController::class);

    // Paiements
    Route::get('/mes-reglements',                     [PaiementController::class, 'mesReglements'])->name('locataire.reglements');
    Route::get('/paiements',                          [PaiementController::class, 'index'])->name('paiements.index');
    Route::patch('/paiements/{paiement}/payer',       [PaiementController::class, 'marquerPaye'])->name('paiements.payer');
    Route::post('/paiements/{paiement}/relance',      [PaiementController::class, 'relance'])->name('paiements.relance');
    Route::post('/paiements/{paiement}/mobile',       [PaiementController::class, 'initierMobile'])->name('paiements.mobile');
    Route::get('/paiements/retour-mobile',            [PaiementController::class, 'retourMobile'])->name('paiements.retour-mobile');
    Route::post('/paiements/webhook-loyer',           [PaiementController::class, 'webhookLoyer'])->name('paiements.webhook-loyer')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/abonnements/webhook',              [AbonnementController::class, 'webhook'])->name('abonnements.webhook')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::get('/quittances/{quittance}',             [PaiementController::class, 'telechargerQuittance'])->name('quittances.pdf');
    Route::get('/quittances/{quittance}/telecharger', [PaiementController::class, 'downloadQuittancePdf'])->name('quittances.download');

    // Annonces (gestion)
    Route::resource('annonces', AnnonceController::class)->except(['show']);

    // Interventions / maintenance
    Route::get('/interventions',              [InterventionController::class, 'index'])->name('interventions.index');
    Route::get('/interventions/create',       [InterventionController::class, 'create'])->name('interventions.create');
    Route::post('/interventions',             [InterventionController::class, 'store'])->name('interventions.store');
    Route::get('/interventions/{intervention}',[InterventionController::class, 'show'])->name('interventions.show');
    Route::patch('/interventions/{intervention}',[InterventionController::class, 'update'])->name('interventions.update');

    // ─── Notifications ──────────────────────────────────────────────────────
    Route::get('/notifications',                           [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send',                     [NotificationController::class, 'send'])->name('notifications.send');
    Route::post('/notifications/retards',                  [NotificationController::class, 'sendBulkRetards'])->name('notifications.bulk-retards');
    // Cloche in-app
    Route::get('/notifications/bell',                      [NotificationController::class, 'bell'])->name('notifications.bell');
    Route::post('/notifications/{id}/lire',                [NotificationController::class, 'marquerLue'])->name('notifications.lire');
    Route::post('/notifications/lire-tout',                [NotificationController::class, 'lireTout'])->name('notifications.lire-tout');

    // ─── Agent IA ────────────────────────────────────────────────────────────
    Route::get('/agent-ia',            [AgentIAController::class, 'index'])->name('agent-ia.index');
    Route::post('/agent-ia/nouvelle',  [AgentIAController::class, 'nouvelles'])->name('agent-ia.nouvelle');
    Route::post('/agent-ia/chat',      [AgentIAController::class, 'chat'])->name('agent-ia.chat');
    Route::post('/agent-ia/envoi',     [AgentIAController::class, 'envoi'])->name('agent-ia.envoi');

    // ─── Administration (admin uniquement) ──────────────────────────────────
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/proprietaires',          [GestionController::class, 'proprietaires'])->name('proprietaires');
        Route::get('/proprietaires/{user}',   [GestionController::class, 'showProprietaire'])->name('proprietaires.show');
        Route::get('/locataires',             [GestionController::class, 'locataires'])->name('locataires');
        Route::get('/locataires/{user}',      [GestionController::class, 'showLocataire'])->name('locataires.show');
        Route::delete('/users/{user}',        [GestionController::class, 'destroyUser'])->name('users.destroy');
        Route::patch('/proprietaires/{user}/devise', [GestionController::class, 'updateDevise'])->name('proprietaires.devise');

        // Gestion des profils utilisateurs
        Route::get('/profils/{role?}',              [ProfilConfigController::class, 'index'])->name('profils');
        Route::put('/profils/{role}',               [ProfilConfigController::class, 'update'])->name('profils.update');
        Route::patch('/profils/{role}/toggle-all',  [ProfilConfigController::class, 'toggleAll'])->name('profils.toggleAll');

        // Abonnements
        Route::get('/abonnements',                             [AbonnementController::class, 'adminIndex'])->name('abonnements');
        Route::post('/abonnements/{user}/offrir',             [AbonnementController::class, 'offrirEssai'])->name('abonnements.offrir');

        // Agences immobilières
        Route::get('/agences',                      [GestionController::class, 'agences'])->name('agences');
        Route::get('/agences/{user}',               [GestionController::class, 'showAgent'])->name('agences.show');
        Route::patch('/agences/{user}/toggle',      [GestionController::class, 'toggleStatutAgent'])->name('agences.toggle');

        // Configuration APIs
        Route::get('/parametres',                    [ParametreController::class, 'index'])->name('parametres');
        Route::put('/parametres/{groupe}',           [ParametreController::class, 'update'])->name('parametres.update');
        Route::post('/parametres/{groupe}/test',     [ParametreController::class, 'test'])->name('parametres.test');
    });
});
