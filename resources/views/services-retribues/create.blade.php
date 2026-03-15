@extends('layouts.app')
@section('title', 'Nouvelle Mission')
@section('page-title', 'Enregistrer une Mission Rétribuée')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('services-retribues.index') }}">Services Rétribués</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-handshake me-2"></i>Enregistrer une Mission</div>
    <div class="card-body">
        <form action="{{ route('services-retribues.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">N° Mission <span class="text-danger">*</span></label>
                    <input type="text" name="numero_mission" class="form-control @error('numero_mission') is-invalid @enderror" value="{{ old('numero_mission', $numeroMission) }}" required>
                    @error('numero_mission')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Type de service <span class="text-danger">*</span></label>
                    <select name="type_service" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach(['Escorte','Gardiennage','Sécurisation événement','Protection rapprochée','Convoyage de fonds','Sécurité routière','Autre'] as $type)
                            <option value="{{ $type }}" {{ old('type_service') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Client/Organisme <span class="text-danger">*</span></label>
                    <input type="text" name="client" class="form-control" value="{{ old('client') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date début <span class="text-danger">*</span></label>
                    <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date fin</label>
                    <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Localité</label>
                    <input type="text" name="localite" class="form-control" value="{{ old('localite') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Montant (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" name="montant" class="form-control" value="{{ old('montant') }}" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Statut paiement</label>
                    <select name="statut_paiement" class="form-select">
                        <option value="impaye">Impayé</option>
                        <option value="partiel">Partiel</option>
                        <option value="paye">Payé</option>
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
                <div class="col-12">
                    <label class="form-label fw-bold">Agents affectés</label>
                    <select name="agents[]" class="form-select" multiple>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" {{ in_array($a->id, old('agents', [])) ? 'selected' : '' }}>{{ $a->nom_complet }} - {{ $a->grade }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Maintenez Ctrl pour sélectionner plusieurs agents</small>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                <a href="{{ route('services-retribues.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
