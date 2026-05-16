<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agent IA : relances automatiques des loyers en retard — tous les jours à 9h
Schedule::command('paiements:relancer')
    ->dailyAt('09:00')
    ->timezone('Africa/Abidjan')
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('paiements:relancer — terminé avec succès.');
    });
