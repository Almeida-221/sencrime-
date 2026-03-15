@extends('layouts.app')
@section('title', 'Modifier Infraction')
@section('page-title', 'Modifier Infraction')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('infractions.index') }}">Infractions</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-edit me-2"></i>Modifier: {{ $infraction->numero_dossier }}</div>
    <div class="card-body">
        <form action="{{ route('infractions.update', $infraction) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">N° Dossier <span class="text-danger">*</span></label>
                    <input type="text" name="numero_dossier" class="form-control" value="{{ old('numero_dossier', $infraction->numero_dossier) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Type d'infraction <span class="text-danger">*</span></label>
                    <select name="type_infraction_id" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach($typesInfractions as $t)
                            <option value="{{ $t->id }}" {{ old('type_infraction_id', $infraction->type_infraction_id) == $t->id ? 'selected' : '' }}>{{ $t->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_infraction" class="form-control" value="{{ old('date_infraction', $infraction->date_infraction->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Localité <span class="text-danger">*</span></label>
                    <input type="text" name="localite" class="form-control" value="{{ old('localite', $infraction->localite) }}" required>
                </div>
                @php
                    $authRegion    = auth()->user()->getRegionEffective();
                    $authServiceId = auth()->user()->service_id;
                    $canEditScope  = auth()->user()->hasRole(['super_admin', 'admin']);
                @endphp
                <div class="col-md-4">
                    <label class="form-label fw-bold">Région</label>
                    <select name="{{ $canEditScope ? 'region' : '_region_ignored' }}" class="form-select" {{ !$canEditScope && $authRegion ? 'disabled' : '' }}>
                        <option value="">-- Sélectionner --</option>
                        @foreach(['Dakar','Thiès','Saint-Louis','Ziguinchor','Kaolack','Tambacounda','Kolda','Fatick','Kaffrine','Kédougou','Louga','Matam','Sédhiou','Diourbel'] as $region)
                            <option value="{{ $region }}" {{ old('region', $authRegion ?? $infraction->region) == $region ? 'selected' : '' }}>{{ $region }}</option>
                        @endforeach
                    </select>
                    @if(!$canEditScope && $authRegion)
                        <input type="hidden" name="region" value="{{ $authRegion }}">
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-select" required>
                        <option value="ouvert" {{ old('statut', $infraction->statut) == 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                        <option value="en_cours" {{ old('statut', $infraction->statut) == 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="ferme" {{ old('statut', $infraction->statut) == 'ferme' ? 'selected' : '' }}>Fermé</option>
                        <option value="classe" {{ old('statut', $infraction->statut) == 'classe' ? 'selected' : '' }}>Classé</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="3" required>{{ old('description', $infraction->description) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Service</label>
                    <select name="{{ $canEditScope ? 'service_id' : '_service_ignored' }}" class="form-select" {{ !$canEditScope && $authServiceId ? 'disabled' : '' }}>
                        <option value="">-- Sélectionner --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" {{ old('service_id', $authServiceId ?? $infraction->service_id) == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
                        @endforeach
                    </select>
                    @if(!$canEditScope && $authServiceId)
                        <input type="hidden" name="service_id" value="{{ $authServiceId }}">
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Agent responsable</label>
                    <select name="agent_id" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" {{ old('agent_id', $infraction->agent_id) == $a->id ? 'selected' : '' }}>{{ $a->nom_complet }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <h6 class="fw-bold text-primary mt-4 mb-3 border-bottom pb-2">Informations du contrevenant</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nom</label>
                    <input type="text" name="nom_contrevenant" class="form-control" value="{{ old('nom_contrevenant', $infraction->nom_contrevenant) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Prénom</label>
                    <input type="text" name="prenom_contrevenant" class="form-control" value="{{ old('prenom_contrevenant', $infraction->prenom_contrevenant) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nationalité</label>
                    <input type="text" name="nationalite_contrevenant" class="form-control" value="{{ old('nationalite_contrevenant', $infraction->nationalite_contrevenant) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Observations</label>
                    <textarea name="observations" class="form-control" rows="2">{{ old('observations', $infraction->observations) }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Mettre à jour</button>
                <a href="{{ route('infractions.show', $infraction) }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
