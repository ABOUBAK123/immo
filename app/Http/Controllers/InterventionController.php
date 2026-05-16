<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\Intervention;
use App\Models\User;
use App\Notifications\InterventionCreeeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterventionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $interventions = match ($user->role) {
            'admin'        => Intervention::with('bien', 'locataire', 'prestataire')->latest()->paginate(15),
            'proprietaire' => Intervention::whereHas('bien', fn($q) => $q->where('proprietaire_id', $user->id))
                ->with('bien', 'locataire')->latest()->paginate(15),
            'locataire'    => Intervention::where('locataire_id', $user->id)->with('bien')->latest()->paginate(15),
            default        => collect(),
        };

        return view('interventions.index', compact('interventions'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $biens = $user->isLocataire()
            ? Bien::whereHas('locationActive', fn($q) => $q->where('locataire_id', $user->id))->get()
            : ($user->isAdmin() ? Bien::all() : $user->biens()->get());
        $prestataires = User::where('role', 'agent')->get();
        return view('interventions.create', compact('biens', 'prestataires'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bien_id'     => 'required|exists:biens,id',
            'titre'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|in:urgence,normal,preventif',
            'priorite'    => 'required|in:basse,moyenne,haute,urgente',
            'photos.*'    => 'nullable|image|max:5120',
        ]);

        $data['locataire_id']  = Auth::user()->isLocataire() ? Auth::id() : $request->locataire_id;
        $data['date_demande']  = today();

        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $p) {
                $photos[] = $p->store('interventions/photos', 'public');
            }
            $data['photos'] = $photos;
        }

        $intervention = Intervention::create($data);

        // Notifier le propriétaire du bien si c'est un locataire qui crée la demande
        if (Auth::user()->isLocataire()) {
            try {
                $proprietaire = $intervention->bien->proprietaire ?? null;
                if ($proprietaire) {
                    $proprietaire->notify(new InterventionCreeeNotification($intervention));
                }
            } catch (\Exception) {
                // Ne pas bloquer si la notification échoue
            }
        }

        return redirect()->route('interventions.show', $intervention)->with('success', 'Demande d\'intervention créée.');
    }

    public function show(Intervention $intervention)
    {
        $this->authoriser($intervention);
        $intervention->load('bien', 'locataire', 'prestataire', 'documents');
        return view('interventions.show', compact('intervention'));
    }

    public function update(Request $request, Intervention $intervention)
    {
        $this->authoriser($intervention);
        $data = $request->validate([
            'statut'            => 'required|in:en_attente,en_cours,termine,annule',
            'prestataire_id'    => 'nullable|exists:users,id',
            'date_intervention' => 'nullable|date',
            'cout'              => 'nullable|numeric|min:0',
            'note_resolution'   => 'nullable|string',
        ]);

        $intervention->update($data);
        return back()->with('success', 'Intervention mise à jour.');
    }

    private function authoriser(Intervention $intervention): void
    {
        $user = Auth::user();
        if ($user->isAdmin()) return;
        if ($user->isProprietaire() && $intervention->bien->proprietaire_id === $user->id) return;
        if ($user->isLocataire() && $intervention->locataire_id === $user->id) return;
        abort(403);
    }
}
