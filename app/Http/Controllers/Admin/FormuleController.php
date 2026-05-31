<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormuleAbonnement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FormuleController extends Controller
{
    public function index()
    {
        abort_if(Auth::user()->role !== 'admin', 403);

        $formules = FormuleAbonnement::withCount([
            'abonnements as abonnes_total',
            'abonnements as abonnes_actifs' => fn($q) => $q->where('statut', 'actif')->where('date_fin', '>=', now()),
        ])->orderBy('ordre')->get();

        return view('admin.formules.index', compact('formules'));
    }

    public function create()
    {
        abort_if(Auth::user()->role !== 'admin', 403);
        return view('admin.formules.form', ['formule' => new FormuleAbonnement()]);
    }

    public function store(Request $request)
    {
        abort_if(Auth::user()->role !== 'admin', 403);

        $data = $this->valider($request);
        $data['slug'] = Str::slug($data['nom']);
        FormuleAbonnement::create($data);

        return redirect()->route('admin.formules')->with('success', 'Formule créée avec succès.');
    }

    public function edit(FormuleAbonnement $formule)
    {
        abort_if(Auth::user()->role !== 'admin', 403);
        return view('admin.formules.form', compact('formule'));
    }

    public function update(Request $request, FormuleAbonnement $formule)
    {
        abort_if(Auth::user()->role !== 'admin', 403);

        $data = $this->valider($request);
        $data['slug'] = Str::slug($data['nom']);
        $formule->update($data);

        return redirect()->route('admin.formules')->with('success', 'Formule mise à jour.');
    }

    public function toggleActif(FormuleAbonnement $formule)
    {
        abort_if(Auth::user()->role !== 'admin', 403);
        $formule->update(['is_active' => !$formule->is_active]);
        return back()->with('success', 'Statut de la formule mis à jour.');
    }

    private function valider(Request $request): array
    {
        return $request->validate([
            'nom'                   => 'required|string|max:80',
            'description'           => 'nullable|string|max:300',
            'couleur'               => 'required|string|max:20',
            'icone'                 => 'required|string|max:50',
            'populaire'             => 'boolean',
            'prix_mensuel'          => 'required|integer|min:0',
            'prix_annuel'           => 'required|integer|min:0',
            'devise'                => 'required|string|max:10',
            'duree_jours'           => 'required|integer|min:1',
            'max_biens'             => 'required|integer|min:-1',
            'max_locataires'        => 'required|integer|min:-1',
            'max_agents'            => 'required|integer|min:-1',
            'max_annonces'          => 'required|integer|min:-1',
            'has_interventions'     => 'boolean',
            'has_annonces'          => 'boolean',
            'has_depenses'          => 'boolean',
            'has_ia'                => 'boolean',
            'has_agents'            => 'boolean',
            'has_documents'         => 'boolean',
            'has_export_pdf'        => 'boolean',
            'has_notifications_sms' => 'boolean',
            'has_api_access'        => 'boolean',
            'support_prioritaire'   => 'boolean',
            'is_active'             => 'boolean',
            'ordre'                 => 'integer|min:0',
        ]);
    }
}
