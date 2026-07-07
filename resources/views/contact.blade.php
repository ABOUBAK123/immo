@extends('layouts.app')

@section('title', 'Nous Contacter - ImmoGest')

@section('content')
<div style="max-width: 800px; margin: 3rem auto; padding: 0 1rem;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2rem; font-weight: 700; color: #1a1a2e; margin-bottom: 0.5rem;">Nous Contacter</h1>
        <p style="color: #666; font-size: 1rem;">L'équipe ImmoGest est à votre écoute pour toute question ou demande d'assistance.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
        <!-- Contact Info -->
        <div style="background: #f9f9f9; border-radius: 12px; padding: 2rem;">
            <h2 style="font-size: 1.3rem; font-weight: 700; color: #1a1a2e; margin-bottom: 1.5rem;">Coordonnées</h2>

            <div style="margin-bottom: 1.5rem;">
                <p style="color: #999; font-size: 0.85rem; margin-bottom: 0.3rem;">Téléphone</p>
                <a href="tel:010142004609" style="color: #EA580C; text-decoration: none; font-weight: 600; font-size: 1.05rem;">
                    01 01 42 00 46 09
                </a>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <p style="color: #999; font-size: 0.85rem; margin-bottom: 0.3rem;">WhatsApp</p>
                <a href="https://wa.me/22510142004609" target="_blank" style="color: #EA580C; text-decoration: none; font-weight: 600; font-size: 1.05rem;">
                    +225 01 01 42 00 46 09
                </a>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <p style="color: #999; font-size: 0.85rem; margin-bottom: 0.3rem;">Email</p>
                <a href="mailto:support@immo.ci" style="color: #EA580C; text-decoration: none; font-weight: 600; font-size: 1.05rem;">
                    support@immo.ci
                </a>
            </div>

            <div style="border-top: 1px solid #ddd; padding-top: 1.5rem; margin-top: 1.5rem;">
                <h3 style="font-size: 0.95rem; font-weight: 600; color: #1a1a2e; margin-bottom: 0.5rem;">Horaires d'ouverture</h3>
                <p style="color: #666; font-size: 0.9rem; margin: 0.25rem 0;">Lundi – Vendredi : 8h – 18h</p>
                <p style="color: #666; font-size: 0.9rem; margin: 0.25rem 0;">Samedi : 9h – 14h</p>
                <p style="color: #999; font-size: 0.8rem; margin-top: 0.5rem;">Heure de Côte d'Ivoire (GMT+0)</p>
            </div>
        </div>

        <!-- Contact Form -->
        <div style="background: #f9f9f9; border-radius: 12px; padding: 2rem;">
            <h2 style="font-size: 1.3rem; font-weight: 700; color: #1a1a2e; margin-bottom: 1.5rem;">Envoyez un message</h2>

            <form method="POST" action="#" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf

                <div>
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #1a1a2e; margin-bottom: 0.5rem;">Nom complet *</label>
                    <input type="text" name="name" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95rem; font-family: inherit;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #1a1a2e; margin-bottom: 0.5rem;">Email *</label>
                    <input type="email" name="email" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95rem; font-family: inherit;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #1a1a2e; margin-bottom: 0.5rem;">Sujet *</label>
                    <select name="subject" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95rem; font-family: inherit;">
                        <option value="">Sélectionner un sujet</option>
                        <option value="technical">Problème technique</option>
                        <option value="billing">Question facturation</option>
                        <option value="feature">Demande de fonctionnalité</option>
                        <option value="other">Autre</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #1a1a2e; margin-bottom: 0.5rem;">Message *</label>
                    <textarea name="message" rows="4" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95rem; font-family: inherit; resize: none;"></textarea>
                </div>

                <button type="submit" style="background: #EA580C; color: white; padding: 0.875rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: background 0.2s;">
                    Envoyer le message
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
