@extends('layouts.app')
@section('title', $formule->exists ? 'Modifier la formule' : 'Nouvelle formule')
@section('page-title', $formule->exists ? 'Modifier : ' . $formule->nom : 'Nouvelle formule')

@section('content')
@php
$route  = $formule->exists ? route('admin.formules.update', $formule) : route('admin.formules.store');
$method = $formule->exists ? 'PUT' : 'POST';
@endphp

<form method="POST" action="{{ $route }}" style="max-width:780px">
    @csrf
    @if($formule->exists) @method('PUT') @endif

    @if($errors->any())
    <div style="background:#FFF1F2;border:1px solid #FECDD3;border-radius:10px;padding:14px 18px;margin-bottom:20px">
        @foreach($errors->all() as $e)
        <div style="font-size:.82rem;color:#DC2626"><i class="bi bi-exclamation-circle me-2"></i>{{ $e }}</div>
        @endforeach
    </div>
    @endif

    {{-- Identité --}}
    <div class="card-immo" style="padding:24px;margin-bottom:20px">
        <div style="font-size:.85rem;font-weight:700;color:#374151;margin-bottom:18px">Identité de la formule</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:5px">Nom *</label>
                <input name="nom" value="{{ old('nom', $formule->nom) }}" required
                       style="width:100%;padding:9px 12px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;outline:none">
            </div>
            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:5px">Icône Bootstrap *</label>
                <input name="icone" value="{{ old('icone', $formule->icone ?? 'bi-star') }}" placeholder="bi-star" required
                       style="width:100%;padding:9px 12px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;outline:none">
            </div>
            <div style="grid-column:1/-1">
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:5px">Description</label>
                <textarea name="description" rows="2" placeholder="Courte description visible sur la page des formules"
                          style="width:100%;padding:9px 12px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;outline:none;resize:vertical">{{ old('description', $formule->description) }}</textarea>
            </div>
            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:5px">Couleur principale</label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="color" name="couleur" value="{{ old('couleur', $formule->couleur ?? '#EA580C') }}"
                           style="width:44px;height:36px;border:1.5px solid #E5E7EB;border-radius:6px;cursor:pointer;padding:2px">
                    <input type="text" id="couleurText" value="{{ old('couleur', $formule->couleur ?? '#EA580C') }}"
                           placeholder="#EA580C"
                           style="flex:1;padding:9px 12px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;outline:none;font-family:monospace"
                           oninput="document.querySelector('input[name=couleur]').value=this.value">
                    <script>
                    document.querySelector('input[name=couleur]').addEventListener('input', function() {
                        document.getElementById('couleurText').value = this.value;
                    });
                    </script>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;padding-top:20px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.84rem;font-weight:600;color:#374151">
                    <input type="hidden" name="populaire" value="0">
                    <input type="checkbox" name="populaire" value="1" {{ old('populaire', $formule->populaire) ? 'checked' : '' }}
                           style="width:16px;height:16px">
                    Marquer comme "Populaire"
                </label>
            </div>
        </div>
    </div>

    {{-- Tarification --}}
    <div class="card-immo" style="padding:24px;margin-bottom:20px">
        <div style="font-size:.85rem;font-weight:700;color:#374151;margin-bottom:18px">Tarification</div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px">
            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:5px">Prix mensuel (XOF) *</label>
                <input name="prix_mensuel" type="number" min="0" value="{{ old('prix_mensuel', $formule->prix_mensuel ?? 0) }}" required
                       style="width:100%;padding:9px 12px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;outline:none">
            </div>
            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:5px">Prix annuel (XOF) *</label>
                <input name="prix_annuel" type="number" min="0" value="{{ old('prix_annuel', $formule->prix_annuel ?? 0) }}" required
                       style="width:100%;padding:9px 12px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;outline:none">
            </div>
            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:5px">Devise</label>
                <select name="devise" style="width:100%;padding:9px 12px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;outline:none">
                    @foreach(\App\Models\User::DEVISES as $code => $info)
                    <option value="{{ $code }}" {{ old('devise', $formule->devise ?? 'XOF') === $code ? 'selected' : '' }}>
                        {{ $code }} — {{ $info['label'] }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:5px">Durée (jours) *</label>
                <input name="duree_jours" type="number" min="1" value="{{ old('duree_jours', $formule->duree_jours ?? 30) }}" required
                       style="width:100%;padding:9px 12px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;outline:none">
            </div>
        </div>
    </div>

    {{-- Limites --}}
    <div class="card-immo" style="padding:24px;margin-bottom:20px">
        <div style="font-size:.85rem;font-weight:700;color:#374151;margin-bottom:6px">Limites quantitatives</div>
        <div style="font-size:.75rem;color:#9CA3AF;margin-bottom:16px">Utilisez <strong>-1</strong> pour illimité.</div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
            @php
            $limites = [
                ['max_biens',      'Biens max',      'bi-buildings'],
                ['max_locataires', 'Locataires max', 'bi-people'],
                ['max_agents',     'Agents max',     'bi-person-badge'],
                ['max_annonces',   'Annonces max',   'bi-megaphone'],
            ];
            @endphp
            @foreach($limites as [$name, $label, $icon])
            <div>
                <label style="font-size:.75rem;font-weight:600;color:#374151;display:flex;align-items:center;gap:5px;margin-bottom:5px">
                    <i class="bi {{ $icon }}" style="color:#9CA3AF"></i>{{ $label }}
                </label>
                <input name="{{ $name }}" type="number" min="-1"
                       value="{{ old($name, $formule->$name ?? 0) }}"
                       style="width:100%;padding:9px 12px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;outline:none">
            </div>
            @endforeach
        </div>
    </div>

    {{-- Fonctionnalités --}}
    <div class="card-immo" style="padding:24px;margin-bottom:20px">
        <div style="font-size:.85rem;font-weight:700;color:#374151;margin-bottom:18px">Fonctionnalités activées</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px">
            @php
            $flags = [
                ['has_documents',         'Gestion des documents',    'bi-file-earmark-text'],
                ['has_export_pdf',         'Export PDF quittances',    'bi-filetype-pdf'],
                ['has_interventions',      'Gestion interventions',    'bi-tools'],
                ['has_annonces',           'Publication annonces',     'bi-megaphone'],
                ['has_depenses',           'Suivi des dépenses',       'bi-wallet2'],
                ['has_ia',                 'Agent IA',                 'bi-robot'],
                ['has_agents',             'Gestion des agents',       'bi-person-badge'],
                ['has_notifications_sms',  'Notifications SMS',        'bi-bell'],
                ['has_api_access',         'Accès API',                'bi-code-slash'],
                ['support_prioritaire',    'Support prioritaire',      'bi-headset'],
            ];
            @endphp
            @foreach($flags as [$name, $label, $icon])
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px 14px;
                           border-radius:10px;border:1.5px solid #E5E7EB;background:#fff;
                           transition:.15s" onmouseover="this.style.borderColor='#D1D5DB'" onmouseout="this.style.borderColor='#E5E7EB'">
                <input type="hidden" name="{{ $name }}" value="0">
                <input type="checkbox" name="{{ $name }}" value="1" {{ old($name, $formule->$name ?? false) ? 'checked' : '' }}
                       style="width:16px;height:16px;flex-shrink:0">
                <i class="bi {{ $icon }}" style="color:#9CA3AF;flex-shrink:0"></i>
                <span style="font-size:.82rem;font-weight:600;color:#374151">{{ $label }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Ordre & Statut --}}
    <div class="card-immo" style="padding:24px;margin-bottom:24px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:center">
            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:5px">Ordre d'affichage</label>
                <input name="ordre" type="number" min="0" value="{{ old('ordre', $formule->ordre ?? 0) }}"
                       style="width:100%;padding:9px 12px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;outline:none">
            </div>
            <div style="padding-top:18px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.84rem;font-weight:600;color:#374151">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $formule->is_active ?? true) ? 'checked' : '' }}
                           style="width:16px;height:16px">
                    Formule active (visible aux utilisateurs)
                </label>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px">
        <button type="submit"
                style="background:#111827;color:#fff;border:none;border-radius:10px;padding:12px 28px;
                       font-size:.9rem;font-weight:700;cursor:pointer">
            <i class="bi bi-check-lg me-2"></i>{{ $formule->exists ? 'Enregistrer les modifications' : 'Créer la formule' }}
        </button>
        <a href="{{ route('admin.formules') }}"
           style="background:#F3F4F6;color:#374151;border-radius:10px;padding:12px 22px;
                  font-size:.9rem;font-weight:600;text-decoration:none">
            Annuler
        </a>
    </div>
</form>

@endsection
