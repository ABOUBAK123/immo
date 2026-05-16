<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfilConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilConfigController extends Controller
{
    private function checkAdmin(): void
    {
        abort_if(Auth::user()->role !== 'admin', 403);
    }

    public function index(string $role = 'proprietaire')
    {
        $this->checkAdmin();

        $roles = array_keys(ProfilConfig::ROLE_LABELS);
        abort_if(!in_array($role, $roles), 404);

        $configs = ProfilConfig::where('role', $role)->orderBy('ordre')->get();

        return view('admin.profils.index', compact('role', 'configs'));
    }

    public function update(Request $request, string $role)
    {
        $this->checkAdmin();

        $roles = array_keys(ProfilConfig::ROLE_LABELS);
        abort_if(!in_array($role, $roles), 404);

        $actifs = $request->input('modules', []);

        // Mettre à jour chaque module non verrouillé
        ProfilConfig::where('role', $role)
            ->where('verrouillee', false)
            ->each(function ($config) use ($actifs) {
                $config->update(['actif' => in_array($config->module, $actifs)]);
            });

        ProfilConfig::viderCache($role);

        return back()->with('success', 'Configuration du profil ' . ProfilConfig::ROLE_LABELS[$role]['label'] . ' sauvegardée.');
    }

    public function toggleAll(Request $request, string $role)
    {
        $this->checkAdmin();

        $actif = $request->boolean('actif', true);

        ProfilConfig::where('role', $role)
            ->where('verrouillee', false)
            ->update(['actif' => $actif]);

        ProfilConfig::viderCache($role);

        return back()->with('success', $actif ? 'Tous les modules activés.' : 'Tous les modules désactivés.');
    }
}
