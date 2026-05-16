<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BienController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $biens = $user->isAdmin()
            ? Bien::with('proprietaire')->latest()->paginate(12)
            : $user->biens()->latest()->paginate(12);

        return view('biens.index', compact('biens'));
    }

    public function create()
    {
        return view('biens.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'              => 'required|string|max:255',
            'type'               => 'required|in:appartement,maison,villa,studio,bureau,commerce,terrain',
            'nom_residence'      => 'nullable|string|max:255',
            'surface'            => 'nullable|numeric|min:1',
            'nb_pieces'          => 'nullable|integer|min:0',
            'nb_chambres'        => 'nullable|integer|min:0',
            'nb_sdb'             => 'nullable|integer|min:0',
            'etage'              => 'nullable|integer|min:0',
            'adresse'            => 'required|string|max:255',
            'ville'              => 'required|string|max:100',
            'code_postal'        => 'nullable|string|max:10',
            'pays'               => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'statut'             => 'required|in:disponible,loue,vendu,en_travaux',
            'meuble'             => 'boolean',
            'prix_achat'         => 'nullable|numeric|min:0',
            'valeur_estimee'     => 'nullable|numeric|min:0',
            'annee_construction' => 'nullable|integer|min:1800|max:' . date('Y'),
            'dpe'                => 'nullable|in:A,B,C,D,E,F,G',
            'photos.*'           => 'nullable|image|max:5120',
        ]);

        $data['proprietaire_id'] = Auth::id();
        $data['meuble'] = $request->boolean('meuble');

        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('biens/photos', 'public');
            }
            $data['photos'] = $photos;
        }

        $bien = Bien::create($data);
        return redirect()->route('biens.show', $bien)->with('success', 'Bien ajouté avec succès.');
    }

    public function show(Bien $bien)
    {
        $this->authoriser($bien);
        $bien->load('proprietaire', 'locations.locataire', 'annonces', 'interventions', 'documents');
        return view('biens.show', compact('bien'));
    }

    public function edit(Bien $bien)
    {
        $this->authoriser($bien);
        return view('biens.edit', compact('bien'));
    }

    public function update(Request $request, Bien $bien)
    {
        $this->authoriser($bien);

        $data = $request->validate([
            'titre'              => 'required|string|max:255',
            'type'               => 'required|in:appartement,maison,villa,studio,bureau,commerce,terrain',
            'nom_residence'      => 'nullable|string|max:255',
            'surface'            => 'nullable|numeric|min:1',
            'nb_pieces'          => 'nullable|integer|min:0',
            'nb_chambres'        => 'nullable|integer|min:0',
            'nb_sdb'             => 'nullable|integer|min:0',
            'etage'              => 'nullable|integer|min:0',
            'adresse'            => 'required|string|max:255',
            'ville'              => 'required|string|max:100',
            'code_postal'        => 'nullable|string|max:10',
            'description'        => 'nullable|string',
            'statut'             => 'required|in:disponible,loue,vendu,en_travaux',
            'meuble'             => 'boolean',
            'prix_achat'         => 'nullable|numeric|min:0',
            'valeur_estimee'     => 'nullable|numeric|min:0',
            'annee_construction' => 'nullable|integer|min:1800|max:' . date('Y'),
            'dpe'                => 'nullable|in:A,B,C,D,E,F,G',
            'photos.*'           => 'nullable|image|max:5120',
        ]);

        $data['meuble'] = $request->boolean('meuble');

        if ($request->hasFile('photos')) {
            if ($bien->photos) {
                foreach ($bien->photos as $old) Storage::disk('public')->delete($old);
            }
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('biens/photos', 'public');
            }
            $data['photos'] = $photos;
        }

        $bien->update($data);
        return redirect()->route('biens.show', $bien)->with('success', 'Bien mis à jour.');
    }

    public function destroy(Bien $bien)
    {
        $this->authoriser($bien);
        $bien->delete();
        return redirect()->route('biens.index')->with('success', 'Bien supprimé.');
    }

    private function authoriser(Bien $bien): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $bien->proprietaire_id !== $user->id) {
            abort(403);
        }
    }
}
