@extends('layouts.app')
@section('title', 'Modifier Type d\'Infraction')
@section('page-title', 'Modifier un Type d\'Infraction')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('types-infractions.index') }}">Types d'Infraction</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-edit me-2"></i>Modifier : {{ $typesInfraction->nom }}</div>
    <div class="card-body">
        <form action="{{ route('types-infractions.update', $typesInfraction) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $typesInfraction->nom) }}" required>
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $typesInfraction->code) }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Catégorie</label>
                    <select name="categorie" class="form-select @error('categorie') is-invalid @enderror">
                        <option value="">-- Sélectionner --</option>
                        <option value="crime" {{ old('categorie', $typesInfraction->categorie) == 'crime' ? 'selected' : '' }}>Crime</option>
                        <option value="delit" {{ old('categorie', $typesInfraction->categorie) == 'delit' ? 'selected' : '' }}>Délit</option>
                        <option value="contravention" {{ old('categorie', $typesInfraction->categorie) == 'contravention' ? 'selected' : '' }}>Contravention</option>
                    </select>
                    @error('categorie')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Amende minimale (FCFA)</label>
                    <input type="number" name="amende_min" class="form-control @error('amende_min') is-invalid @enderror" value="{{ old('amende_min', $typesInfraction->amende_min) }}" min="0">
                    @error('amende_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Amende maximale (FCFA)</label>
                    <input type="number" name="amende_max" class="form-control @error('amende_max') is-invalid @enderror" value="{{ old('amende_max', $typesInfraction->amende_max) }}" min="0">
                    @error('amende_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $typesInfraction->description) }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="actif" id="actif" {{ old('actif', $typesInfraction->actif) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="actif">Type actif</label>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Mettre à jour</button>
                <a href="{{ route('types-infractions.show', $typesInfraction) }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
