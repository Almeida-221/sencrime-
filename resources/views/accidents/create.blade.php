@extends('layouts.app')
@section('title', 'Nouvel Accident')
@section('page-title', 'Enregistrer un Accident')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accidents.index') }}">Accidents</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-car-crash me-2"></i>Enregistrer un Accident</div>
    <div class="card-body">
        <form action="{{ route('accidents.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">N° Rapport <span class="text-danger">*</span></label>
                    <input type="text" name="numero_rapport" class="form-control @error('numero_rapport') is-invalid @enderror" value="{{ old('numero_rapport', $numeroRapport) }}" required>
                    @error('numero_rapport')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_accident" class="form-control" value="{{ old('date_accident', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Heure</label>
                    <input type="time" name="heure_accident" class="form-control" value="{{ old('heure_accident') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Localité <span class="text-danger">*</span></label>
                    <input type="text" name="localite" class="form-control" value="{{ old('localite') }}" required>
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
                            <option value="{{ $region }}" {{ old('region', $authRegion) == $region ? 'selected' : '' }}>{{ $region }}</option>
                        @endforeach
                    </select>
                    @if(!$canEditScope && $authRegion)
                        <input type="hidden" name="region" value="{{ $authRegion }}">
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Lieu exact</label>
                    <input type="text" name="lieu_exact" class="form-control" value="{{ old('lieu_exact') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Type d'accident <span class="text-danger">*</span></label>
                    <select name="type_accident" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach(['Collision frontale','Collision latérale','Renversement','Chute de véhicule','Accident piéton','Accident moto','Accident camion','Autre'] as $type)
                            <option value="{{ $type }}" {{ old('type_accident') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Gravité <span class="text-danger">*</span></label>
                    <select name="gravite" class="form-select" required>
                        <option value="leger" {{ old('gravite') == 'leger' ? 'selected' : '' }}>Léger</option>
                        <option value="grave" {{ old('gravite') == 'grave' ? 'selected' : '' }}>Grave</option>
                        <option value="mortel" {{ old('gravite') == 'mortel' ? 'selected' : '' }}>Mortel</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-select" required>
                        <option value="ouvert">Ouvert</option>
                        <option value="en_cours">En cours</option>
                        <option value="ferme">Fermé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nb victimes</label>
                    <input type="number" name="nombre_victimes" class="form-control" value="{{ old('nombre_victimes', 0) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nb blessés</label>
                    <input type="number" name="nombre_blesses" class="form-control" value="{{ old('nombre_blesses', 0) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nb morts</label>
                    <input type="number" name="nombre_morts" class="form-control" value="{{ old('nombre_morts', 0) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Service</label>
                    <select name="{{ $canEditScope ? 'service_id' : '_service_ignored' }}" class="form-select" {{ !$canEditScope && $authServiceId ? 'disabled' : '' }}>
                        <option value="">-- Sélectionner --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" {{ old('service_id', $authServiceId) == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
                        @endforeach
                    </select>
                    @if(!$canEditScope && $authServiceId)
                        <input type="hidden" name="service_id" value="{{ $authServiceId }}">
                    @endif
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="3" required>{{ old('description') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Causes</label>
                    <textarea name="causes" class="form-control" rows="2">{{ old('causes') }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                <a href="{{ route('accidents.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
