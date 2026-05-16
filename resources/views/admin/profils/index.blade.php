@extends('layouts.app')
@section('title', 'Gestion des profils')
@section('page-title', 'Gestion des profils utilisateurs')

@push('styles')
<style>
    /* ── Toggle switch ── */
    .toggle-wrap { display:flex; align-items:center; justify-content:space-between; }
    .toggle { position:relative; width:44px; height:24px; flex-shrink:0; }
    .toggle input { opacity:0; width:0; height:0; }
    .toggle-slider {
        position:absolute; inset:0; border-radius:24px;
        background:#E5E7EB; cursor:pointer; transition:.25s;
    }
    .toggle-slider:before {
        content:''; position:absolute;
        width:18px; height:18px; border-radius:50%;
        left:3px; bottom:3px; background:#fff;
        transition:.25s; box-shadow:0 1px 3px rgba(0,0,0,.2);
    }
    .toggle input:checked + .toggle-slider { background:#EA580C; }
    .toggle input:checked + .toggle-slider:before { transform:translateX(20px); }
    .toggle input:disabled + .toggle-slider { opacity:.5; cursor:not-allowed; }

    /* ── Module card ── */
    .module-card {
        background:#fff; border:2px solid #E5E7EB; border-radius:12px;
        padding:16px 18px; display:flex; align-items:center; gap:14px;
        transition:border-color .2s, box-shadow .2s;
    }
    .module-card.is-active  { border-color:#FED7AA; background:#FFFAF5; }
    .module-card.is-locked  { border-color:#DBEAFE; background:#F0F9FF; }
    .module-card:hover:not(.is-locked) { border-color:#FDBA74; box-shadow:0 2px 8px rgba(234,88,12,.1); }

    /* ── Role tabs ── */
    .role-tab {
        display:flex; align-items:center; gap:8px; padding:10px 18px;
        border-radius:10px; text-decoration:none; font-size:.83rem;
        font-weight:600; color:#78716C; transition:.15s; border:2px solid transparent;
    }
    .role-tab:hover { background:#FFF7ED; color:#C2410C; }
    .role-tab.active { border-color:#EA580C; background:#FFF7ED; color:#C2410C; }
    .role-tab .role-count {
        font-size:.68rem; padding:2px 7px; border-radius:20px;
        background:#F3F4F6; color:#6B7280; font-weight:700;
    }
    .role-tab.active .role-count { background:#FDBA74; color:#7C2D12; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="d-flex align-items-center gap-2 mb-4 p-3 rounded-3"
     style="background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D;font-size:.83rem">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

<div style="display:grid;grid-template-columns:220px 1fr;gap:20px;align-items:start">

    {{-- ── Barre rôles ───────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:0">

        <div class="card-immo" style="padding:16px">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9CA3AF;margin-bottom:12px">
                Types d'utilisateurs
            </div>
            @foreach(\App\Models\ProfilConfig::ROLE_LABELS as $r => $info)
            @php
                $nb = \App\Models\ProfilConfig::where('role',$r)->count();
                $nbActifs = \App\Models\ProfilConfig::where('role',$r)->where('actif',true)->count();
            @endphp
            <a href="{{ route('admin.profils', $r) }}"
               class="role-tab {{ $role === $r ? 'active' : '' }}"
               style="margin-bottom:4px;display:flex">
                <div style="width:30px;height:30px;border-radius:8px;background:{{ $info['bg'] }};color:{{ $info['color'] }};
                            display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0">
                    <i class="bi bi-{{ $info['icone'] }}"></i>
                </div>
                <div style="flex:1;min-width:0;margin-left:8px">
                    <div style="font-size:.8rem;font-weight:600;line-height:1.2">{{ $info['label'] }}</div>
                    <div style="font-size:.68rem;color:#9CA3AF">{{ $nbActifs }}/{{ $nb }} modules actifs</div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Légende --}}
        <div class="card-immo" style="padding:14px 16px;margin-top:12px">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9CA3AF;margin-bottom:10px">Légende</div>
            <div style="display:flex;flex-direction:column;gap:8px;font-size:.76rem">
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:10px;height:10px;border-radius:2px;background:#FED7AA;border:2px solid #EA580C"></div>
                    <span style="color:#6B7280">Module actif</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:10px;height:10px;border-radius:2px;background:#E5E7EB;border:2px solid #D1D5DB"></div>
                    <span style="color:#6B7280">Module désactivé</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:10px;height:10px;border-radius:2px;background:#DBEAFE;border:2px solid #93C5FD"></div>
                    <span style="color:#6B7280">Obligatoire (verrouillé)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Panneau configuration ─────────────────────────────────────────── --}}
    <div>
        @php $roleInfo = \App\Models\ProfilConfig::ROLE_LABELS[$role]; @endphp

        {{-- En-tête du rôle --}}
        <div class="card-immo" style="padding:20px 24px;margin-bottom:16px">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                <div style="display:flex;align-items:center;gap:14px">
                    <div style="width:48px;height:48px;border-radius:12px;background:{{ $roleInfo['bg'] }};color:{{ $roleInfo['color'] }};
                                display:flex;align-items:center;justify-content:center;font-size:1.3rem">
                        <i class="bi bi-{{ $roleInfo['icone'] }}"></i>
                    </div>
                    <div>
                        <h3 style="font-size:1rem;font-weight:700;margin:0">{{ $roleInfo['label'] }}</h3>
                        <p style="font-size:.78rem;color:#6B7280;margin:2px 0 0">
                            Choisissez les fonctionnalités visibles pour ce type d'utilisateur
                        </p>
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <form method="POST" action="{{ route('admin.profils.toggleAll', $role) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="actif" value="1">
                        <button type="submit" class="btn-ghost" style="font-size:.78rem;padding:6px 12px">
                            <i class="bi bi-check-all"></i> Tout activer
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.profils.toggleAll', $role) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="actif" value="0">
                        <button type="submit" class="btn-ghost" style="font-size:.78rem;padding:6px 12px;color:#DC2626;border-color:#FECDD3">
                            <i class="bi bi-x-circle"></i> Tout désactiver
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Grille des modules --}}
        <form method="POST" action="{{ route('admin.profils.update', $role) }}">
            @csrf @method('PUT')

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                @foreach($configs as $config)
                <div class="module-card {{ $config->actif ? 'is-active' : '' }} {{ $config->verrouillee ? 'is-locked' : '' }}">
                    {{-- Icône module --}}
                    <div style="width:44px;height:44px;border-radius:10px;flex-shrink:0;
                                background:{{ $config->actif ? $roleInfo['bg'] : '#F3F4F6' }};
                                color:{{ $config->actif ? $roleInfo['color'] : '#9CA3AF' }};
                                display:flex;align-items:center;justify-content:center;font-size:1.1rem;transition:.2s">
                        <i class="bi bi-{{ $config->icone }}"></i>
                    </div>

                    {{-- Infos --}}
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
                            <span style="font-size:.85rem;font-weight:700;color:#1C0A00">{{ $config->label }}</span>
                            @if($config->verrouillee)
                            <span style="font-size:.63rem;padding:1px 6px;border-radius:10px;background:#DBEAFE;color:#1D4ED8;font-weight:600">
                                <i class="bi bi-lock-fill"></i> Obligatoire
                            </span>
                            @endif
                        </div>
                        <div style="font-size:.73rem;color:#9CA3AF;line-height:1.3">{{ $config->description }}</div>
                    </div>

                    {{-- Toggle --}}
                    <label class="toggle" title="{{ $config->verrouillee ? 'Module obligatoire, non désactivable' : '' }}">
                        <input type="checkbox"
                               name="modules[]"
                               value="{{ $config->module }}"
                               {{ $config->actif ? 'checked' : '' }}
                               {{ $config->verrouillee ? 'disabled checked' : '' }}
                               onchange="updateCard(this)">
                        <span class="toggle-slider"></span>
                    </label>
                    @if($config->verrouillee)
                    <input type="hidden" name="modules[]" value="{{ $config->module }}">
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Bouton sauvegarder --}}
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:16px 20px;
                        background:#F9FAFB;border-radius:10px;border:1px solid #E5E7EB">
                <div style="font-size:.78rem;color:#6B7280">
                    <i class="bi bi-info-circle me-1"></i>
                    Les modifications s'appliquent immédiatement à la prochaine connexion.
                </div>
                <button type="submit" class="btn-primary-immo" style="padding:9px 24px">
                    <i class="bi bi-floppy2-fill me-1"></i> Sauvegarder
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
function updateCard(checkbox) {
    const card = checkbox.closest('.module-card');
    const icon = card.querySelector('[style*="width:44px"]');
    const isOn = checkbox.checked;

    card.classList.toggle('is-active', isOn);

    // Couleur icône mise à jour dynamiquement via inline style ne peut être changée sans recalcul
    // Simple feedback visuel
    if (isOn) {
        card.style.borderColor = '#FED7AA';
        card.style.background  = '#FFFAF5';
    } else {
        card.style.borderColor = '#E5E7EB';
        card.style.background  = '#fff';
    }
}
</script>
@endpush

@endsection
