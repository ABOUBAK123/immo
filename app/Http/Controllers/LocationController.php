<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\Location;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $statut = $request->statut;

        $query = match ($user->role) {
            'admin'        => Location::with('bien', 'locataire'),
            'proprietaire' => Location::whereHas('bien', fn($q) => $q->where('proprietaire_id', $user->id))->with('bien', 'locataire'),
            'locataire'    => $user->locations()->with('bien'),
            default        => Location::whereRaw('1=0'),
        };

        $locations = $query
            ->when($statut, fn($q) => $q->where('statut', $statut))
            ->latest()->paginate(15)->withQueryString();

        return view('locations.index', compact('locations'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $biens = $user->biens()->where('statut', 'disponible')->get();
        $locataires = User::where('role', 'locataire')
            ->when(!$user->isAdmin(), fn($q) => $q->where('created_by', $user->id))
            ->get();
        $bien = $request->bien_id ? Bien::find($request->bien_id) : null;
        return view('locations.create', compact('biens', 'locataires', 'bien'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bien_id'                => 'required|exists:biens,id',
            'locataire_id'           => 'required|exists:users,id',
            'date_debut'             => 'required|date',
            'date_fin'               => 'nullable|date|after:date_debut',
            'loyer_mensuel'          => 'required|numeric|min:0',
            'charges'                => 'nullable|numeric|min:0',
            'frais_agence'           => 'nullable|numeric|min:0|max:100',
            'depot_garantie'         => 'nullable|numeric|min:0',
            'type_bail'              => 'required|in:meuble,vide,etudiant,mobilite',
            'conditions_particulieres' => 'nullable|string',
        ]);

        $data['charges']        = $data['charges']        ?? 0;
        $data['frais_agence']   = $data['frais_agence']   ?? 0;
        $data['depot_garantie'] = $data['depot_garantie'] ?? 0;
        $data['statut'] = 'actif';
        $location = Location::create($data);

        // Bien passe en statut loué
        Bien::find($data['bien_id'])->update(['statut' => 'loue']);

        // Génération automatique des paiements pour 12 mois
        $this->genererPaiements($location);

        return redirect()->route('locations.show', $location)->with('success', 'Location créée et paiements générés.');
    }

    public function show(Location $location)
    {
        $this->authoriser($location);
        $location->load('bien.proprietaire', 'locataire', 'paiements.quittance', 'documents');
        return view('locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        $this->authoriser($location);
        $user = Auth::user();
        $locataires = User::where('role', 'locataire')
            ->when(!$user->isAdmin(), fn($q) => $q->where('created_by', $user->id))
            ->get();
        return view('locations.edit', compact('location', 'locataires'));
    }

    public function update(Request $request, Location $location)
    {
        $this->authoriser($location);
        $data = $request->validate([
            'loyer_mensuel'  => 'required|numeric|min:0',
            'charges'        => 'nullable|numeric|min:0',
            'frais_agence'   => 'nullable|numeric|min:0|max:100',
            'statut'         => 'required|in:en_attente,actif,resilie,termine',
            'date_fin'       => 'nullable|date',
        ]);
        $data['charges']      = $data['charges']      ?? 0;
        $data['frais_agence'] = $data['frais_agence'] ?? 0;

        $location->update($data);
        return redirect()->route('locations.show', $location)->with('success', 'Location mise à jour.');
    }

    public function destroy(Location $location)
    {
        $this->authoriser($location);
        $location->bien->update(['statut' => 'disponible']);
        $location->update(['statut' => 'resilie']);
        return redirect()->route('locations.index')->with('success', 'Location résiliée.');
    }

    private function genererPaiements(Location $location): void
    {
        $debut   = \Carbon\Carbon::parse($location->date_debut)->startOfMonth();
        $finDate = $location->date_fin
            ? \Carbon\Carbon::parse($location->date_fin)->startOfMonth()
            : \Carbon\Carbon::now()->endOfYear()->startOfMonth();

        $current = $debut->copy();
        $count   = 0;
        while ($current->lte($finDate) && $count < 120) {
            Paiement::create([
                'location_id'   => $location->id,
                'montant'       => $location->montant_total,
                'date_echeance' => $current->copy(),
                'statut'        => 'en_attente',
                'type'          => 'loyer',
            ]);
            $current->addMonth();
            $count++;
        }
    }

    private function authoriser(Location $location): void
    {
        $user = Auth::user();
        if ($user->isAdmin()) return;
        if ($user->isProprietaire() && $location->bien->proprietaire_id === $user->id) return;
        if ($user->isLocataire() && $location->locataire_id === $user->id) return;
        abort(403);
    }
}
