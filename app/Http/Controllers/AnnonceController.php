<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\Bien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnonceController extends Controller
{
    public function index(Request $request)
    {
        $query = Annonce::actives()->with('bien');

        if ($request->filled('type'))     $query->where('type', $request->type);
        if ($request->filled('prix_max')) $query->where('prix', '<=', $request->prix_max);

        $ville      = $request->filled('ville')       ? $request->ville       : null;
        $surfaceMin = $request->filled('surface_min') ? $request->surface_min : null;
        if ($ville || $surfaceMin) {
            $query->whereHas('bien', function ($q) use ($ville, $surfaceMin) {
                if ($ville)      $q->where('ville', $ville);
                if ($surfaceMin) $q->where('surface', '>=', $surfaceMin);
            });
        }

        $annonces = $query->latest()->paginate(12);
        $villes   = Bien::select('ville')->distinct()->orderBy('ville')->pluck('ville');

        return view('annonces.index', compact('annonces', 'villes'));
    }

    public function create()
    {
        $user = Auth::user();
        $biens = $user->isAdmin() ? Bien::all() : $user->biens()->where('statut', 'disponible')->get();
        return view('annonces.create', compact('biens'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bien_id'           => 'required|exists:biens,id',
            'type'              => 'required|in:location,vente',
            'prix'              => 'required|numeric|min:0',
            'prix_negociable'   => 'boolean',
            'titre'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'date_disponibilite' => 'nullable|date',
            'photos.*'          => 'nullable|image|max:5120',
        ]);

        $data['agent_id']       = Auth::id();
        $data['statut']         = 'active';
        $data['prix_negociable'] = $request->boolean('prix_negociable');

        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $p) {
                $photos[] = $p->store('annonces/photos', 'public');
            }
            $data['photos'] = $photos;
        }

        $annonce = Annonce::create($data);
        return redirect()->route('annonces.show', $annonce)->with('success', 'Annonce publiée.');
    }

    public function show(Annonce $annonce)
    {
        $annonce->incrementerVues();
        $annonce->load('bien.proprietaire', 'agent');
        $similaires = Annonce::actives()
            ->where('id', '!=', $annonce->id)
            ->whereHas('bien', fn($q) => $q->where('ville', $annonce->bien->ville))
            ->limit(3)->get();

        return view('annonces.show', compact('annonce', 'similaires'));
    }

    public function edit(Annonce $annonce)
    {
        $this->authoriser($annonce);
        return view('annonces.edit', compact('annonce'));
    }

    public function update(Request $request, Annonce $annonce)
    {
        $this->authoriser($annonce);
        $data = $request->validate([
            'prix'             => 'required|numeric|min:0',
            'titre'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'statut'           => 'required|in:active,inactive,vendu,loue,archive',
            'prix_negociable'  => 'boolean',
        ]);

        $data['prix_negociable'] = $request->boolean('prix_negociable');
        $annonce->update($data);
        return redirect()->route('annonces.show', $annonce)->with('success', 'Annonce mise à jour.');
    }

    public function destroy(Annonce $annonce)
    {
        $this->authoriser($annonce);
        $annonce->delete();
        return redirect()->route('annonces.index')->with('success', 'Annonce supprimée.');
    }

    private function authoriser(Annonce $annonce): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $annonce->agent_id !== $user->id && $annonce->bien->proprietaire_id !== $user->id) {
            abort(403);
        }
    }
}
