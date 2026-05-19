<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Bien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublicationController extends Controller
{
    private function checkAgent(): void
    {
        $user = Auth::user();
        abort_if(!in_array($user->role, ['agent', 'admin', 'proprietaire']), 403);
        abort_if(!$user->isActif(), 403, 'Votre compte est désactivé.');
    }

    public function mesAnnonces(Request $request)
    {
        $this->checkAgent();
        $user = Auth::user();

        $query = Annonce::with('bien')
            ->where('agent_id', $user->id)
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->when($request->filled('type'),   fn($q) => $q->where('type', $request->type));

        $annonces = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total'    => Annonce::where('agent_id', $user->id)->count(),
            'actives'  => Annonce::where('agent_id', $user->id)->where('statut', 'active')->count(),
            'vues'     => Annonce::where('agent_id', $user->id)->sum('vues'),
            'vendus'   => Annonce::where('agent_id', $user->id)->whereIn('statut', ['vendu', 'loue'])->count(),
        ];

        return view('agent.mes-annonces', compact('annonces', 'stats'));
    }

    public function create()
    {
        $this->checkAgent();
        return view('agent.publier');
    }

    public function store(Request $request)
    {
        $this->checkAgent();

        $request->validate([
            // Bien
            'bien_titre'       => 'required|string|max:255',
            'bien_type'        => 'required|in:appartement,maison,villa,studio,bureau,commerce,terrain',
            'bien_adresse'     => 'required|string|max:255',
            'bien_ville'       => 'required|string|max:100',
            'bien_code_postal' => 'nullable|string|max:10',
            'bien_pays'        => 'nullable|string|max:100',
            'bien_surface'     => 'nullable|numeric|min:1',
            'bien_nb_pieces'   => 'nullable|integer|min:0',
            'bien_nb_chambres' => 'nullable|integer|min:0',
            'bien_nb_sdb'      => 'nullable|integer|min:0',
            'bien_description' => 'nullable|string',
            'bien_meuble'      => 'boolean',
            // Annonce
            'type'             => 'required|in:location,vente',
            'type_tarif'       => 'nullable|in:jour,mois',
            'prix'             => 'required|numeric|min:0',
            'titre'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'date_dispo'       => 'nullable|date',
            'prix_negociable'  => 'boolean',
            'photos.*'         => 'nullable|image|max:5120',
        ]);

        DB::transaction(function () use ($request) {
            $bien = Bien::create([
                'proprietaire_id'  => null,
                'agent_id'         => Auth::id(),
                'titre'            => $request->bien_titre,
                'type'             => $request->bien_type,
                'adresse'          => $request->bien_adresse,
                'ville'            => $request->bien_ville,
                'code_postal'      => $request->bien_code_postal,
                'pays'             => $request->bien_pays ?? 'Côte d\'Ivoire',
                'surface'          => $request->bien_surface,
                'nb_pieces'        => $request->bien_nb_pieces,
                'nb_chambres'      => $request->bien_nb_chambres,
                'nb_sdb'           => $request->bien_nb_sdb,
                'description'      => $request->bien_description,
                'meuble'           => $request->boolean('bien_meuble'),
                'statut'           => 'disponible',
            ]);

            $photos = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $p) {
                    $photos[] = $p->store('annonces/photos', 'public');
                }
            }

            $annonce = Annonce::create([
                'bien_id'           => $bien->id,
                'agent_id'          => Auth::id(),
                'type'              => $request->type,
                'type_tarif'        => $request->type === 'vente' ? 'mois' : ($request->type_tarif ?? 'mois'),
                'prix'              => $request->prix,
                'titre'             => $request->titre,
                'description'       => $request->description,
                'date_disponibilite' => $request->date_dispo,
                'prix_negociable'   => $request->boolean('prix_negociable'),
                'statut'            => 'active',
                'photos'            => $photos,
            ]);

            $this->annonceId = $annonce->id;
        });

        return redirect()->route('agent.mes-annonces')
            ->with('success', 'Bien publié avec succès ! Votre annonce est en ligne.');
    }

    public function toggleStatut(Annonce $annonce)
    {
        $this->checkAgent();
        $user = Auth::user();
        abort_if(!$user->isAdmin() && $annonce->agent_id !== $user->id, 403);

        $annonce->update([
            'statut' => $annonce->statut === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Statut de l\'annonce mis à jour.');
    }
}
