<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            if (!$user->isActif()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Votre compte a été désactivé. Contactez l\'administrateur.'])->onlyInput('email');
            }
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:proprietaire,locataire,agent,acheteur',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'role'     => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        // Essai gratuit automatique à l'inscription d'un propriétaire
        if ($data['role'] === 'proprietaire') {
            $joursEssai = (int) Parametre::get('abonnement_essai', 0);
            if ($joursEssai > 0) {
                Abonnement::create([
                    'user_id'        => $user->id,
                    'montant'        => 0,
                    'devise'         => Parametre::get('abonnement_devise', 'XOF'),
                    'date_debut'     => now()->toDateString(),
                    'date_fin'       => now()->addDays($joursEssai)->toDateString(),
                    'statut'         => 'actif',
                    'methode_paiement' => 'essai_admin',
                    'invoice_number' => Abonnement::genererNumeroFacture(),
                    'essai'          => true,
                ]);
            }
        }

        Auth::login($user);
        return redirect()->route('dashboard')->with('success', 'Bienvenue sur Immo Manager !');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
