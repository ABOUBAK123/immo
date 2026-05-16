<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilController extends Controller
{
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
