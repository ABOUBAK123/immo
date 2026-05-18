@extends('layouts.app')
@section('title', 'Dépenses')
@section('page-title', 'Dépenses de l\'agence')

@php
    $user      = auth()->user();
    $sym       = $user->deviseSymbole();
    $cats      = \App\Models\Depense::CATEGORIES;
    $totalMois = $depenses->sum('montant');
@endphp

@section('topbar-actions')
<a href="{{ route('depenses.point') }}"
   style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;
          background:linear-gradient(135deg,#7C3AED,#A855F7);color:#fff;
          border-radius:9px;font-size:.82rem;font-weight:700;text-decoration:none;
          box-shadow:0 2px 10px rgba(124,58,237,.3);transition:.15s"
   onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
    <i class="bi bi-calculator-fill"></i> Faire le point
</a>
@endsection

@section('content')

@if(session('success'))
<div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:12px 16px;
            margin-bottom:20px;font-size:.82rem;color:#15803D;display:flex;align-items:center;gap:8px">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

<div style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start">

    {{-- ── Formulaire ajout dépense ─────────────────────────────────────── --}}
    <div class="card-immo" style="padding:22px">
        <div style="font-size:.95rem;font-weight:800;color:#1F2937;margin-bottom:18px;
                    display:flex;align-items:center;gap:8px">
            <i class="bi bi-plus-circle-fill" style="color:#EA580C"></i>
            Nouvelle dépense
        </div>

        <form method="POST" action="{{ route('depenses.store') }}">
            @csrf

            <div style="margin-bottom:14px">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px">
                    Intitulé <span style="color:#DC2626">*</span>
                </label>
                <input type="text" name="titre"
                       value="{{ old('titre') }}"
                       placeholder="Ex : Loyer bureau mai 2026"
                       class="form-control-immo @error('titre') is-invalid @enderror"
                       required>
                @error('titre')<div style="color:#DC2626;font-size:.72rem;margin-top:3px">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px">
                        Montant ({{ $sym }}) <span style="color:#DC2626">*</span>
                    </label>
                    <input type="number" name="montant"
                           value="{{ old('montant') }}"
                           placeholder="0" min="0" step="0.01"
                           class="form-control-immo @error('montant') is-invalid @enderror"
                           required>
                    @error('montant')<div style="color:#DC2626;font-size:.72rem;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px">
                        Date <span style="color:#DC2626">*</span>
                    </label>
                    <input type="date" name="date_depense"
                           value="{{ old('date_depense', date('Y-m-d')) }}"
                           class="form-control-immo @error('date_depense') is-invalid @enderror"
                           required>
                </div>
            </div>

            <div style="margin-bottom:14px">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px">
                    Catégorie <span style="color:#DC2626">*</span>
                </label>
                <select name="categorie" class="form-select-immo @error('categorie') is-invalid @enderror" required>
                    @foreach($cats as $key => $cat)
                    <option value="{{ $key }}" {{ old('categorie') === $key ? 'selected' : '' }}>
                        {{ $cat['label'] }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:18px">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px">
                    Notes (optionnel)
                </label>
                <textarea name="notes" rows="2" class="form-control-immo"
                          placeholder="Détails supplémentaires…">{{ old('notes') }}</textarea>
            </div>

            <button type="submit"
                    style="width:100%;padding:11px;background:linear-gradient(135deg,#EA580C,#F97316);
                           color:#fff;border:none;border-radius:9px;font-size:.86rem;font-weight:700;
                           cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;
                           font-family:inherit;transition:.15s">
                <i class="bi bi-floppy-fill"></i> Enregistrer la dépense
            </button>
        </form>
    </div>

    {{-- ── Liste des dépenses ───────────────────────────────────────────── --}}
    <div>
        {{-- Filtre mois --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
            <div style="font-size:.9rem;font-weight:700;color:#1F2937">
                Dépenses du mois
            </div>
            <form method="GET" style="display:flex;align-items:center;gap:8px">
                <input type="month" name="mois" value="{{ $mois }}"
                       class="form-control-immo" style="width:auto"
                       onchange="this.form.submit()">
            </form>
        </div>

        {{-- KPI mois --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px">
            <div class="card-immo stat-card" style="padding:14px 16px">
                <div class="stat-icon" style="background:#FFF1F2;color:#DC2626">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <div>
                    <div class="stat-val" style="color:#DC2626;font-size:1.1rem">
                        {{ number_format($totalMois, 0, ',', ' ') }} {{ $sym }}
                    </div>
                    <div class="stat-label">Total dépenses</div>
                </div>
            </div>
            <div class="card-immo stat-card" style="padding:14px 16px">
                <div class="stat-icon" style="background:#EFF6FF;color:#2563EB">
                    <i class="bi bi-list-check"></i>
                </div>
                <div>
                    <div class="stat-val" style="color:#2563EB;font-size:1.1rem">{{ $depenses->count() }}</div>
                    <div class="stat-label">Opérations</div>
                </div>
            </div>
            <div class="card-immo stat-card" style="padding:14px 16px">
                <div class="stat-icon" style="background:#F5F3FF;color:#7C3AED">
                    <i class="bi bi-tags-fill"></i>
                </div>
                <div>
                    <div class="stat-val" style="color:#7C3AED;font-size:1.1rem">
                        {{ $depenses->pluck('categorie')->unique()->count() }}
                    </div>
                    <div class="stat-label">Catégories</div>
                </div>
            </div>
        </div>

        {{-- Tableau --}}
        <div class="card-immo">
            @if($depenses->count())
            <table class="table-immo">
                <thead>
                    <tr>
                        <th>Intitulé</th>
                        <th>Catégorie</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($depenses as $dep)
                @php $cat = $cats[$dep->categorie] ?? $cats['autres']; @endphp
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:.84rem">{{ $dep->titre }}</div>
                        @if($dep->notes)
                        <div style="font-size:.72rem;color:#9CA3AF">{{ Str::limit($dep->notes, 60) }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:5px;
                                     padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;
                                     background:{{ $cat['color'] }}18;color:{{ $cat['color'] }}">
                            <i class="bi {{ $cat['icon'] }}" style="font-size:.7rem"></i>
                            {{ $cat['label'] }}
                        </span>
                    </td>
                    <td style="font-size:.82rem">{{ $dep->date_depense->format('d/m/Y') }}</td>
                    <td style="font-weight:700;color:#DC2626">
                        {{ number_format($dep->montant, 0, ',', ' ') }} {{ $sym }}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('depenses.destroy', $dep) }}"
                              onsubmit="return confirm('Supprimer cette dépense ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-ghost"
                                    style="padding:4px 8px;font-size:.72rem;color:#DC2626;border-color:#FECDD3">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <div style="padding:48px;text-align:center">
                <i class="bi bi-receipt" style="font-size:2.5rem;color:#E5E7EB"></i>
                <p style="color:#9CA3AF;margin:12px 0 0;font-size:.85rem">
                    Aucune dépense enregistrée pour ce mois.
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
