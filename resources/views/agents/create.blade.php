@extends('layouts.app')
@section('title', 'Nouvel Agent')
@section('page-title', 'Enregistrer un Agent')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('agents.index') }}">Agents</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-user-plus me-2"></i>Enregistrer un Agent</div>
    <div class="card-body">
        <form action="{{ route('agents.store') }}" method="POST">
            @csrf
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Informations personnelles</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Matricule <span class="text-danger">*</span></label>
                    <input type="text" name="matricule" class="form-control @error('matricule') is-invalid @enderror" value="{{ old('matricule') }}" required>
                    @error('matricule')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror" value="{{ old('prenom') }}" required>
                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Genre <span class="text-danger">*</span></label>
                    <select name="genre" class="form-select @error('genre') is-invalid @enderror" required>
                        <option value="M" {{ old('genre') == 'M' ? 'selected' : '' }}>Masculin</option>
                        <option value="F" {{ old('genre') == 'F' ? 'selected' : '' }}>Féminin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" value="{{ old('date_naissance') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Lieu de naissance</label>
                    <input type="text" name="lieu_naissance" class="form-control" value="{{ old('lieu_naissance') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nationalité</label>
                    <input type="text" name="nationalite" class="form-control" value="{{ old('nationalite', 'Sénégalaise') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Adresse</label>
                    <input type="text" name="adresse" class="form-control" value="{{ old('adresse') }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Informations professionnelles</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Grade <span class="text-danger">*</span></label>
                    <select name="grade" class="form-select @error('grade') is-invalid @enderror" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach(['Gardien de la Paix','Brigadier','Brigadier-Chef','Inspecteur','Inspecteur Principal','Commissaire','Commissaire Divisionnaire','Commissaire Général','Officier de Police','Capitaine','Commandant','Lieutenant-Colonel','Colonel','Général'] as $grade)
                            <option value="{{ $grade }}" {{ old('grade') == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                        @endforeach
                    </select>
                    @error('grade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Fonction</label>
                    <input type="text" name="fonction" class="form-control" value="{{ old('fonction') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date de recrutement</label>
                    <input type="date" name="date_recrutement" class="form-control" value="{{ old('date_recrutement') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Service</label>
                    <select name="service_id" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" {{ old('service_id') == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-select" required>
                        <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        <option value="suspendu" {{ old('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                        <option value="retraite" {{ old('statut') == 'retraite' ? 'selected' : '' }}>Retraité</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Observations</label>
                    <textarea name="observations" class="form-control" rows="2">{{ old('observations') }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                <a href="{{ route('agents.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
