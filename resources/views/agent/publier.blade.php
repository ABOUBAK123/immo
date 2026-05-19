@extends('layouts.app')
@section('title', 'Publier un bien')
@section('page-title', 'Publier un bien')

@section('topbar-actions')
<a href="{{ route('agent.mes-annonces') }}" class="btn-ghost">
    <i class="bi bi-arrow-left"></i> Mes annonces
</a>
@endsection

@push('styles')
<style>
    .step-tab { display:flex;align-items:center;gap:8px;padding:10px 20px;border-bottom:3px solid transparent;font-size:.83rem;font-weight:600;color:#9CA3AF;cursor:pointer;background:none;border-left:none;border-right:none;border-top:none; }
    .step-tab.active { color:#EA580C;border-bottom-color:#EA580C; }
    .step-tab .step-num { width:22px;height:22px;border-radius:50%;background:#E5E7EB;color:#6B7280;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0; }
    .step-tab.active .step-num { background:#EA580C;color:#fff; }
    .step-tab.done .step-num { background:#16A34A;color:#fff; }
    .step-tab.done { color:#16A34A; }
    .section-bloc { background:#F9FAFB;border-radius:10px;padding:20px;margin-bottom:16px; }
    .field-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
    @media(max-width:640px){ .field-grid{ grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

@if($errors->any())
<div class="d-flex flex-wrap gap-2 mb-4 p-3 rounded-3" style="background:#FFF1F2;border:1px solid #FECDD3;font-size:.8rem;color:#9F1239">
    <i class="bi bi-exclamation-circle-fill"></i>
    @foreach($errors->all() as $e) <span>{{ $e }}</span> @endforeach
</div>
@endif

<form method="POST" action="{{ route('agent.publier.store') }}" enctype="multipart/form-data" id="pubForm">
@csrf

{{-- Onglets étapes --}}
<div class="card-immo" style="margin-bottom:20px">
    <div style="display:flex;border-bottom:1px solid #F3F4F6;overflow-x:auto">
        <button type="button" class="step-tab active" id="tab1" onclick="goStep(1)">
            <span class="step-num" id="num1">1</span> Informations du bien
        </button>
        <button type="button" class="step-tab" id="tab2" onclick="goStep(2)">
            <span class="step-num" id="num2">2</span> Détails de l'annonce
        </button>
        <button type="button" class="step-tab" id="tab3" onclick="goStep(3)">
            <span class="step-num" id="num3">3</span> Photos & Publication
        </button>
    </div>
</div>

{{-- ÉTAPE 1 : Bien ─────────────────────────────────────────────────────── --}}
<div id="step1">
<div class="card-immo" style="padding:24px">
    <div style="font-size:.88rem;font-weight:700;margin-bottom:18px;display:flex;align-items:center;gap:8px">
        <i class="bi bi-house-door" style="color:#EA580C"></i> Caractéristiques du bien
    </div>

    <div class="section-bloc">
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;margin-bottom:12px">Identification</div>
        <div class="field-grid">
            <div style="grid-column:1/-1">
                <label class="form-label-immo">Titre du bien <span style="color:#DC2626">*</span></label>
                <input type="text" name="bien_titre" class="form-control-immo" value="{{ old('bien_titre') }}"
                       placeholder="Ex: Appartement F3 lumineux centre-ville" required>
            </div>
            <div>
                <label class="form-label-immo">Type de bien <span style="color:#DC2626">*</span></label>
                <select name="bien_type" class="form-select-immo" required>
                    @foreach(['appartement'=>'Appartement','maison'=>'Maison','villa'=>'Villa','studio'=>'Studio','bureau'=>'Bureau','commerce'=>'Commerce','terrain'=>'Terrain'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('bien_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label-immo">Surface (m²)</label>
                <input type="number" name="bien_surface" class="form-control-immo" value="{{ old('bien_surface') }}" min="1" placeholder="Ex: 75">
            </div>
        </div>
    </div>

    <div class="section-bloc">
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;margin-bottom:12px">Composition</div>
        <div class="field-grid">
            <div>
                <label class="form-label-immo">Nombre de pièces</label>
                <input type="number" name="bien_nb_pieces" class="form-control-immo" value="{{ old('bien_nb_pieces') }}" min="0" placeholder="3">
            </div>
            <div>
                <label class="form-label-immo">Chambres</label>
                <input type="number" name="bien_nb_chambres" class="form-control-immo" value="{{ old('bien_nb_chambres') }}" min="0" placeholder="2">
            </div>
            <div>
                <label class="form-label-immo">Salle(s) de bain</label>
                <input type="number" name="bien_nb_sdb" class="form-control-immo" value="{{ old('bien_nb_sdb') }}" min="0" placeholder="1">
            </div>
            <div style="display:flex;align-items:center;gap:10px;padding-top:22px">
                <input type="hidden" name="bien_meuble" value="0">
                <input type="checkbox" name="bien_meuble" id="meuble" value="1" {{ old('bien_meuble') ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:#EA580C">
                <label for="meuble" style="font-size:.83rem;font-weight:500;cursor:pointer">Bien meublé</label>
            </div>
        </div>
    </div>

    <div class="section-bloc">
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;margin-bottom:12px">Localisation</div>
        <div class="field-grid">
            <div style="grid-column:1/-1">
                <label class="form-label-immo">Adresse <span style="color:#DC2626">*</span></label>
                <input type="text" name="bien_adresse" class="form-control-immo" value="{{ old('bien_adresse') }}"
                       placeholder="Rue, quartier, numéro…" required>
            </div>
            <div>
                <label class="form-label-immo">Ville <span style="color:#DC2626">*</span></label>
                <input type="text" name="bien_ville" class="form-control-immo" value="{{ old('bien_ville') }}" placeholder="Abidjan" required>
            </div>
            <div>
                <label class="form-label-immo">Code postal</label>
                <input type="text" name="bien_code_postal" class="form-control-immo" value="{{ old('bien_code_postal') }}" placeholder="00225">
            </div>
            <div>
                <label class="form-label-immo">Pays</label>
                <input type="text" name="bien_pays" class="form-control-immo" value="{{ old('bien_pays', 'Côte d\'Ivoire') }}" placeholder="Côte d'Ivoire">
            </div>
        </div>
    </div>

    <div>
        <label class="form-label-immo">Description du bien</label>
        <textarea name="bien_description" class="form-control-immo" rows="3"
                  placeholder="Décrivez le bien : état général, points forts, environnement…">{{ old('bien_description') }}</textarea>
    </div>
</div>

<div style="display:flex;justify-content:flex-end;margin-top:16px">
    <button type="button" onclick="goStep(2)" class="btn-primary-immo">
        Étape suivante <i class="bi bi-arrow-right ms-1"></i>
    </button>
</div>
</div>

{{-- ÉTAPE 2 : Annonce ──────────────────────────────────────────────────── --}}
<div id="step2" style="display:none">
<div class="card-immo" style="padding:24px">
    <div style="font-size:.88rem;font-weight:700;margin-bottom:18px;display:flex;align-items:center;gap:8px">
        <i class="bi bi-megaphone" style="color:#EA580C"></i> Paramètres de l'annonce
    </div>

    <div class="section-bloc">
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;margin-bottom:12px">Type & Prix</div>
        <div class="field-grid">
            <div>
                <label class="form-label-immo">Type d'annonce <span style="color:#DC2626">*</span></label>
                <div style="display:flex;gap:10px;margin-top:4px">
                    @foreach(['location'=>'Location','vente'=>'Vente'] as $v=>$l)
                    <label style="flex:1;display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid {{ old('type') === $v ? '#EA580C' : '#E5E7EB' }};
                                  border-radius:8px;cursor:pointer;font-size:.83rem;font-weight:500;transition:.15s"
                           onclick="this.parentElement.querySelectorAll('label').forEach(x=>x.style.borderColor='#E5E7EB');this.style.borderColor='#EA580C';onTypeChange()">
                        <input type="radio" name="type" value="{{ $v }}" {{ old('type', 'location') === $v ? 'checked' : '' }}
                               style="accent-color:#EA580C"> {{ $l }}
                    </label>
                    @endforeach
                </div>
            </div>
            {{-- Tarification (visible uniquement pour Location) --}}
            <div id="typeTarifWrap">
                <label class="form-label-immo">Tarification</label>
                <div style="display:flex;gap:10px;margin-top:4px">
                    @foreach(['mois'=>'Par mois','jour'=>'Par jour'] as $v=>$l)
                    <label id="tarif-label-{{ $v }}"
                           style="flex:1;display:flex;align-items:center;gap:8px;padding:10px 14px;
                                  border:2px solid {{ old('type_tarif','mois') === $v ? '#EA580C' : '#E5E7EB' }};
                                  border-radius:8px;cursor:pointer;font-size:.83rem;font-weight:500;transition:.15s"
                           onclick="selectTarif('{{ $v }}')">
                        <input type="radio" name="type_tarif" value="{{ $v }}"
                               {{ old('type_tarif','mois') === $v ? 'checked' : '' }}
                               style="accent-color:#EA580C"> {{ $l }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="form-label-immo">Prix <span style="color:#DC2626">*</span></label>
                <div style="position:relative">
                    <input type="number" name="prix" id="prixInput" class="form-control-immo" value="{{ old('prix') }}" min="0" step="1000"
                           placeholder="Ex: 250000" required style="padding-right:70px">
                    <span id="prixUnit" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:.78rem;color:#9CA3AF;font-weight:600">/mois</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;padding-top:22px">
                <input type="hidden" name="prix_negociable" value="0">
                <input type="checkbox" name="prix_negociable" id="nego" value="1" {{ old('prix_negociable') ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:#EA580C">
                <label for="nego" style="font-size:.83rem;font-weight:500;cursor:pointer">Prix négociable</label>
            </div>
            <div>
                <label class="form-label-immo">Disponible à partir du</label>
                <input type="date" name="date_dispo" class="form-control-immo" value="{{ old('date_dispo', date('Y-m-d')) }}">
            </div>
        </div>
    </div>

    <div class="section-bloc">
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;margin-bottom:12px">Titre & Description de l'annonce</div>
        <div style="margin-bottom:14px">
            <label class="form-label-immo">Titre de l'annonce <span style="color:#DC2626">*</span></label>
            <input type="text" name="titre" class="form-control-immo" value="{{ old('titre') }}"
                   placeholder="Ex: Appartement moderne 3 pièces, vue dégagée" required>
        </div>
        <div>
            <label class="form-label-immo">Description détaillée</label>
            <textarea name="description" class="form-control-immo" rows="4"
                      placeholder="Décrivez votre bien pour les acheteurs/locataires potentiels…">{{ old('description') }}</textarea>
        </div>
    </div>
</div>

<div style="display:flex;justify-content:space-between;margin-top:16px">
    <button type="button" onclick="goStep(1)" class="btn-ghost">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </button>
    <button type="button" onclick="goStep(3)" class="btn-primary-immo">
        Étape suivante <i class="bi bi-arrow-right ms-1"></i>
    </button>
</div>
</div>

{{-- ÉTAPE 3 : Photos & Publication ────────────────────────────────────── --}}
<div id="step3" style="display:none">
<div class="card-immo" style="padding:24px">
    <div style="font-size:.88rem;font-weight:700;margin-bottom:18px;display:flex;align-items:center;gap:8px">
        <i class="bi bi-images" style="color:#EA580C"></i> Photos du bien
    </div>

    <div style="border:2px dashed #FED7AA;border-radius:12px;padding:32px;text-align:center;margin-bottom:20px;cursor:pointer;transition:.2s"
         onclick="document.getElementById('photoInput').click()"
         onmouseover="this.style.borderColor='#EA580C';this.style.background='#FFF7ED'"
         onmouseout="this.style.borderColor='#FED7AA';this.style.background='transparent'">
        <i class="bi bi-cloud-upload" style="font-size:2.5rem;color:#FDBA74;display:block;margin-bottom:10px"></i>
        <div style="font-size:.88rem;font-weight:600;color:#7C2D12">Cliquez pour ajouter des photos</div>
        <div style="font-size:.75rem;color:#9CA3AF;margin-top:4px">JPG, PNG — Max 5 Mo par photo</div>
        <input type="file" id="photoInput" name="photos[]" multiple accept="image/*" style="display:none" onchange="previewPhotos(this)">
    </div>

    <div id="photoPreview" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px"></div>

    {{-- Récap --}}
    <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:10px;padding:16px;margin-bottom:20px">
        <div style="font-size:.8rem;font-weight:700;color:#7C2D12;margin-bottom:10px">
            <i class="bi bi-list-check me-1"></i> Récapitulatif avant publication
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:.78rem">
            <div><span style="color:#9CA3AF">Titre du bien :</span> <strong id="recap_btit">—</strong></div>
            <div><span style="color:#9CA3AF">Type :</span> <strong id="recap_btype">—</strong></div>
            <div><span style="color:#9CA3AF">Ville :</span> <strong id="recap_ville">—</strong></div>
            <div><span style="color:#9CA3AF">Surface :</span> <strong id="recap_surf">—</strong></div>
            <div><span style="color:#9CA3AF">Titre annonce :</span> <strong id="recap_atit">—</strong></div>
            <div><span style="color:#9CA3AF">Prix :</span> <strong id="recap_prix">—</strong></div>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:10px;padding:14px;background:#F0FDF4;border-radius:8px;font-size:.82rem;color:#16A34A">
        <i class="bi bi-info-circle-fill"></i>
        Votre annonce sera publiée immédiatement et visible sur la marketplace.
    </div>
</div>

<div style="display:flex;justify-content:space-between;margin-top:16px">
    <button type="button" onclick="goStep(2)" class="btn-ghost">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </button>
    <button type="submit" class="btn-primary-immo" style="padding:10px 28px;font-size:.9rem">
        <i class="bi bi-rocket-takeoff me-1"></i> Publier l'annonce
    </button>
</div>
</div>

</form>
@endsection

@push('scripts')
<script>
function selectTarif(val) {
    document.querySelectorAll('[name="type_tarif"]').forEach(r => {
        r.closest('label').style.borderColor = r.value === val ? '#EA580C' : '#E5E7EB';
        r.checked = (r.value === val);
    });
    document.getElementById('prixUnit').textContent = '/' + val;
}

function onTypeChange() {
    const type = document.querySelector('[name="type"]:checked')?.value;
    const wrap = document.getElementById('typeTarifWrap');
    if (wrap) wrap.style.display = (type === 'vente') ? 'none' : '';
}

// Init on load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[name="type"]').forEach(r => r.addEventListener('change', onTypeChange));
    onTypeChange();
    // Sync unit on load
    const t = document.querySelector('[name="type_tarif"]:checked')?.value ?? 'mois';
    document.getElementById('prixUnit').textContent = '/' + t;
});

function goStep(n) {
    [1,2,3].forEach(i => {
        document.getElementById('step'+i).style.display = i === n ? '' : 'none';
        const tab = document.getElementById('tab'+i);
        tab.classList.toggle('active', i === n);
        if (i < n) tab.classList.add('done'); else tab.classList.remove('done');
    });
    if (n === 3) updateRecap();
    window.scrollTo({top: 0, behavior:'smooth'});
}

function updateRecap() {
    const g = id => document.querySelector('[name="'+id+'"]')?.value ?? '—';
    document.getElementById('recap_btit').textContent  = g('bien_titre') || '—';
    document.getElementById('recap_btype').textContent = g('bien_type') || '—';
    document.getElementById('recap_ville').textContent = g('bien_ville') || '—';
    document.getElementById('recap_surf').textContent  = (g('bien_surface') ? g('bien_surface')+' m²' : '—');
    document.getElementById('recap_atit').textContent  = g('titre') || '—';
    const typeSel    = document.querySelector('[name="type"]:checked')?.value;
    const tarifSel   = document.querySelector('[name="type_tarif"]:checked')?.value ?? 'mois';
    const prixVal    = g('prix') ? parseInt(g('prix')).toLocaleString('fr-FR') : '—';
    const prixUnit   = typeSel === 'location' ? (tarifSel === 'jour' ? '/jour' : '/mois') : '';
    document.getElementById('recap_prix').textContent  = prixVal + (prixUnit ? ' ' + prixUnit : '');
}

function previewPhotos(input) {
    const container = document.getElementById('photoPreview');
    container.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.style.cssText = 'position:relative;width:100px;height:80px;border-radius:8px;overflow:hidden;border:2px solid #FED7AA';
            div.innerHTML = '<img src="'+e.target.result+'" style="width:100%;height:100%;object-fit:cover">';
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endpush
