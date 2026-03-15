@extends('layouts.app')
@section('title', 'Nouvelle Mission')
@section('page-title', 'Enregistrer une Mission Rétribuée')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('services-retribues.index') }}">Services Rétribués</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-handshake me-2"></i>Nouvelle Mission Rétribuée</div>
    <div class="card-body">
        <form action="{{ route('services-retribues.store') }}" method="POST" id="missionForm">
            @csrf
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Informations de la mission</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold">N° Mission <span class="text-danger">*</span></label>
                    <input type="text" name="numero_mission" class="form-control @error('numero_mission') is-invalid @enderror" value="{{ old('numero_mission', $numeroMission) }}" required>
                    @error('numero_mission')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror" value="{{ old('titre') }}" required>
                    @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Type de mission <span class="text-danger">*</span></label>
                    <select name="type_mission" class="form-select @error('type_mission') is-invalid @enderror" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach(['Escorte','Gardiennage','Sécurisation événement','Protection rapprochée','Convoyage de fonds','Sécurité routière','Autre'] as $type)
                            <option value="{{ $type }}" {{ old('type_mission') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('type_mission')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Date début <span class="text-danger">*</span></label>
                    <input type="date" name="date_debut" class="form-control @error('date_debut') is-invalid @enderror" value="{{ old('date_debut', date('Y-m-d')) }}" required>
                    @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Date fin</label>
                    <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Localité</label>
                    <input type="text" name="localite" class="form-control" value="{{ old('localite') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Région</label>
                    <select name="region" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        @foreach(['Dakar','Thiès','Diourbel','Fatick','Kaolack','Kaffrine','Louga','Saint-Louis','Matam','Tambacounda','Kédougou','Kolda','Sédhiou','Ziguinchor'] as $region)
                            <option value="{{ $region }}" {{ old('region') == $region ? 'selected' : '' }}>{{ $region }}</option>
                        @endforeach
                    </select>
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
                        <option value="planifie" {{ old('statut') == 'planifie' ? 'selected' : '' }}>Planifié</option>
                        <option value="en_cours" {{ old('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="termine" {{ old('statut') == 'termine' ? 'selected' : '' }}>Terminé</option>
                        <option value="annule" {{ old('statut') == 'annule' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Informations client</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nom client <span class="text-danger">*</span></label>
                    <input type="text" name="client_nom" class="form-control @error('client_nom') is-invalid @enderror" value="{{ old('client_nom') }}" required>
                    @error('client_nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Téléphone client</label>
                    <input type="text" name="client_telephone" class="form-control" value="{{ old('client_telephone') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Email client</label>
                    <input type="email" name="client_email" class="form-control" value="{{ old('client_email') }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Adresse client</label>
                    <input type="text" name="client_adresse" class="form-control" value="{{ old('client_adresse') }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Informations financières</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Montant total (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" name="montant_total" class="form-control @error('montant_total') is-invalid @enderror" value="{{ old('montant_total') }}" min="0" required>
                    @error('montant_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Statut paiement</label>
                    <select name="statut_paiement" class="form-select">
                        <option value="impaye" {{ old('statut_paiement') == 'impaye' ? 'selected' : '' }}>Impayé</option>
                        <option value="partiel" {{ old('statut_paiement') == 'partiel' ? 'selected' : '' }}>Partiel</option>
                        <option value="paye" {{ old('statut_paiement') == 'paye' ? 'selected' : '' }}>Payé</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Montant payé (FCFA)</label>
                    <input type="number" name="montant_paye" class="form-control" value="{{ old('montant_paye', 0) }}" min="0">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Agents affectés</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered" id="agentsTable">
                    <thead class="table-light">
                        <tr><th>Sélectionner</th><th>Agent</th><th>Grade</th><th>Rôle</th><th>Rémunération (FCFA)</th></tr>
                    </thead>
                    <tbody>
                        @foreach($agents as $agent)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="agents[]" value="{{ $agent->id }}" {{ in_array($agent->id, old('agents', [])) ? 'checked' : '' }}>
                            </td>
                            <td>{{ $agent->nom_complet }}</td>
                            <td><small>{{ $agent->grade }}</small></td>
                            <td><input type="text" name="roles[{{ $agent->id }}]" class="form-control form-control-sm" value="{{ old('roles.'.$agent->id) }}" placeholder="Ex: Chef d'équipe"></td>
                            <td><input type="number" name="remunerations[{{ $agent->id }}]" class="form-control form-control-sm" value="{{ old('remunerations.'.$agent->id, 0) }}" min="0"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                <a href="{{ route('services-retribues.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
