<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function edit()
    {
        return view('profil.edit', ['user' => Auth::user()]);
    }

    public function updateInfo(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success_info', 'Informations personnelles mises à jour.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.'])->withInput();
        }

        $user->update(['password' => $request->password]);

        return back()->with('success_pwd', 'Mot de passe modifié avec succès.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return back()->with('success_avatar', 'Photo de profil mise à jour.');
    }

    public function updateDevise(Request $request)
    {
        $request->validate([
            'devise' => ['required', 'string', 'in:' . implode(',', array_keys(User::DEVISES))],
        ]);

        $user = Auth::user();
        abort_if(!in_array($user->role, ['proprietaire', 'admin']), 403);

        $user->update(['devise' => $request->devise]);

        return back()->with('success', 'Devise mise à jour : ' . $request->devise);
    }
}
