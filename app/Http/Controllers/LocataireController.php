<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class LocataireController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $locataires = User::where('role', 'locataire')
            ->when(!$user->isAdmin(), function ($q) use ($user) {
                $q->whereHas('locations.bien', fn($b) => $b->where('proprietaire_id', $user->id));
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = '%' . $request->q . '%';
                $q->where(fn($s) => $s->where('name', 'like', $search)->orWhere('email', 'like', $search)->orWhere('phone', 'like', $search));
            })
            ->withCount('locations')
            ->with(['locations' => fn($q) => $q->where('statut', 'actif')->with('bien')])
            ->paginate(15);

        return view('locataires.index', compact('locataires'));
    }

    public function create()
    {
        return view('locataires.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'phone'    => 'nullable|string|max:20',
            'password' => ['required', Password::min(8)],
        ]);

        $locataire = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'role'       => 'locataire',
            'password'   => Hash::make($data['password']),
            'created_by' => Auth::id(),
        ]);
        $locataire->assignRole('locataire');

        return redirect()->route('locataires.show', $locataire)
            ->with('success', 'Locataire créé avec succès.');
    }

    public function show(User $locataire)
    {
        abort_if($locataire->role !== 'locataire', 404);
        $locataire->load([
            'locations' => fn($q) => $q->with('bien', 'paiements'),
        ]);
        return view('locataires.show', compact('locataire'));
    }

    public function edit(User $locataire)
    {
        abort_if($locataire->role !== 'locataire', 404);
        return view('locataires.edit', compact('locataire'));
    }

    public function update(Request $request, User $locataire)
    {
        abort_if($locataire->role !== 'locataire', 404);
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        $locataire->update($data);
        return redirect()->route('locataires.show', $locataire)->with('success', 'Fiche mise à jour.');
    }

    public function destroy(User $locataire)
    {
        abort_if($locataire->role !== 'locataire', 404);
        $locataire->delete();
        return redirect()->route('locataires.index')->with('success', 'Locataire supprimé.');
    }
}
