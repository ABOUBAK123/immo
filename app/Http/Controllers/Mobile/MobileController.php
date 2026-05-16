<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Bien;
use App\Models\Reservation;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function index()
    {
        $featured = Annonce::actives()
            ->with('bien')
            ->orderByDesc('vues')
            ->limit(6)
            ->get();

        $courtsTermes = Annonce::actives()
            ->where('mode_location', 'court_terme')
            ->whereNotNull('prix_nuit')
            ->with('bien')
            ->orderByDesc('vues')
            ->limit(8)
            ->get();

        $villes = Bien::select('ville')->distinct()->orderBy('ville')->pluck('ville');
        $stats  = [
            'biens'      => Annonce::actives()->count(),
            'locations'  => Annonce::actives()->where('type', 'location')->count(),
            'ventes'     => Annonce::actives()->where('type', 'vente')->count(),
            'meublees'   => Annonce::actives()->where('mode_location', 'court_terme')->count(),
        ];

        return view('mobile.index', compact('featured', 'courtsTermes', 'villes', 'stats'));
    }

    public function listings(Request $request)
    {
        $query = Annonce::actives()->with('bien');

        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('mode'))      $query->where('mode_location', $request->mode);
        if ($request->filled('ville'))     $query->whereHas('bien', fn($q) => $q->where('ville', $request->ville));
        if ($request->filled('bien_type')) $query->whereHas('bien', fn($q) => $q->where('type', $request->bien_type));
        if ($request->filled('prix_max'))  $query->where(function ($q) use ($request) {
            $q->where('prix', '<=', $request->prix_max)
              ->orWhere('prix_nuit', '<=', $request->prix_max);
        });
        if ($request->filled('voyageurs')) $query->where(function ($q) use ($request) {
            $q->whereNull('nb_max_voyageurs')
              ->orWhere('nb_max_voyageurs', '>=', $request->voyageurs);
        });
        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(fn($s) =>
                $s->where('titre', 'like', $q)
                  ->orWhere('description', 'like', $q)
                  ->orWhereHas('bien', fn($b) => $b->where('ville', 'like', $q)->orWhere('adresse', 'like', $q))
            );
        }

        // Filtrer les courts termes par disponibilité si dates fournies
        if ($request->filled('debut') && $request->filled('fin')) {
            $debut = $request->debut;
            $fin   = $request->fin;
            $query->whereDoesntHave('reservations', fn($r) =>
                $r->whereIn('statut', ['paiement_initie','payee','confirmee'])
                  ->where('date_debut', '<', $fin)
                  ->where('date_fin', '>', $debut)
            );
        }

        $annonces = $query->latest()->paginate(12)->withQueryString();
        $villes   = Bien::select('ville')->distinct()->orderBy('ville')->pluck('ville');

        return view('mobile.listings', compact('annonces', 'villes'));
    }

    public function detail(Annonce $annonce)
    {
        $annonce->incrementerVues();
        $annonce->load('bien.proprietaire');

        $datesOccupees = $annonce->estCourtTerme()
            ? Reservation::datesOccupees($annonce->id)
            : [];

        $similaires = Annonce::actives()
            ->where('id', '!=', $annonce->id)
            ->where('type', $annonce->type)
            ->whereHas('bien', fn($q) => $q->where('ville', $annonce->bien->ville))
            ->with('bien')
            ->limit(4)
            ->get();

        return view('mobile.detail', compact('annonce', 'datesOccupees', 'similaires'));
    }

    public function disponibilites(Annonce $annonce)
    {
        return response()->json(Reservation::datesOccupees($annonce->id));
    }
}
