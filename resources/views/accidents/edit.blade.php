@extends('layouts.app')
@section('title', 'Modifier Accident')
@section('page-title', 'Modifier Accident')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accidents.index') }}">Accidents</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-edit me-2"></i>Modifier: {{ $accident->numero_rapport }}</div>
    <div class="card-body">
        <form action="{{ route('accidents.update', $accident) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">N° Rapport <span class="text-danger">*</span></label>
                    <input type="text" name="numero_rapport" class="form-control" value="{{ old('numero_rapport', $accident->numero_rapport) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_accident" class="form-control" value="{{ old('date_accident', $accident->date_accident->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Heure</label>
                    <input type="time" name="heure_accident" class="form-control" value="{{ old('heure_accident', $accident->heure_accident) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Localité <span class="text-danger">*</span></label>
                    <input type="text" name="localite" class="form-control" value="{{ old('localite', $accident->localite) }}" required>
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
                            <option value="{{ $region }}" {{ old('region', $authRegion ?? $accident->region) == $region ? 'selected' : '' }}>{{ $region }}</option>
                        @endforeach
                    </select>
                    @if(!$canEditScope && $authRegion)
                        <input type="hidden" name="region" value="{{ $authRegion }}">
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Type d'accident <span class="text-danger">*</span></label>
                    <select name="type_accident" class="form-select" required>
                        @foreach(['Collision frontale','Collision latérale','Renversement','Chute de véhicule','Accident piéton','Accident moto','Accident camion','Autre'] as $type)
                            <option value="{{ $type }}" {{ old('type_accident', $accident->type_accident) == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Gravité <span class="text-danger">*</span></label>
                    <select name="gravite" class="form-select" required>
                        <option value="leger" {{ old('gravite', $accident->gravite) == 'leger' ? 'selected' : '' }}>Léger</option>
                        <option value="grave" {{ old('gravite', $accident->gravite) == 'grave' ? 'selected' : '' }}>Grave</option>
                        <option value="mortel" {{ old('gravite', $accident->gravite) == 'mortel' ? 'selected' : '' }}>Mortel</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-select" required>
                        <option value="ouvert" {{ old('statut', $accident->statut) == 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                        <option value="en_cours" {{ old('statut', $accident->statut) == 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="ferme" {{ old('statut', $accident->statut) == 'ferme' ? 'selected' : '' }}>Fermé</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Service</label>
                    <select name="{{ $canEditScope ? 'service_id' : '_service_ignored' }}" class="form-select" {{ !$canEditScope && $authServiceId ? 'disabled' : '' }}>
                        <option value="">-- Sélectionner --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" {{ old('service_id', $authServiceId ?? $accident->service_id) == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
                        @endforeach
                    </select>
                    @if(!$canEditScope && $authServiceId)
                        <input type="hidden" name="service_id" value="{{ $authServiceId }}">
                    @endif
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nb victimes</label>
                    <input type="number" name="nombre_victimes" class="form-control" value="{{ old('nombre_victimes', $accident->nombre_victimes) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nb blessés</label>
                    <input type="number" name="nombre_blesses" class="form-control" value="{{ old('nombre_blesses', $accident->nombre_blesses) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nb morts</label>
                    <input type="number" name="nombre_morts" class="form-control" value="{{ old('nombre_morts', $accident->nombre_morts) }}" min="0">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="3" required>{{ old('description', $accident->description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Causes</label>
                    <textarea name="causes" class="form-control" rows="2">{{ old('causes', $accident->causes) }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Mettre à jour</button>
                <a href="{{ route('accidents.show', $accident) }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
