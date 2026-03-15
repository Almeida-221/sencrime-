@extends('layouts.app')
@section('title', 'Nouveau Cas Immigration')
@section('page-title', 'Enregistrer un Cas d\'Immigration Clandestine')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('immigrations.index') }}">Immigration Clandestine</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-passport me-2"></i>Nouveau Cas d'Immigration Clandestine</div>
    <div class="card-body">
        <form action="{{ route('immigrations.store') }}" method="POST">
            @csrf

            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Informations générales</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold">N° Cas <span class="text-danger">*</span></label>
                    <input type="text" name="numero_cas" class="form-control @error('numero_cas') is-invalid @enderror" value="{{ old('numero_cas', $numeroCas) }}" required>
                    @error('numero_cas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Date d'interception <span class="text-danger">*</span></label>
                    <input type="date" name="date_interception" class="form-control @error('date_interception') is-invalid @enderror" value="{{ old('date_interception', date('Y-m-d')) }}" required>
                    @error('date_interception')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Type d'opération <span class="text-danger">*</span></label>
                    <select name="type_operation" class="form-select @error('type_operation') is-invalid @enderror" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="interception" {{ old('type_operation') == 'interception' ? 'selected' : '' }}>Interception</option>
                        <option value="arrestation"  {{ old('type_operation') == 'arrestation'  ? 'selected' : '' }}>Arrestation</option>
                        <option value="rapatriement" {{ old('type_operation') == 'rapatriement' ? 'selected' : '' }}>Rapatriement</option>
                    </select>
                    @error('type_operation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-select @error('statut') is-invalid @enderror" required>
                        <option value="ouvert"   {{ old('statut','ouvert') == 'ouvert'   ? 'selected' : '' }}>Ouvert</option>
                        <option value="en_cours" {{ old('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="ferme"    {{ old('statut') == 'ferme'    ? 'selected' : '' }}>Fermé</option>
                        <option value="rapatrie" {{ old('statut') == 'rapatrie' ? 'selected' : '' }}>Rapatrié</option>
                    </select>
                    @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Lieu d'interception</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Localité <span class="text-danger">*</span></label>
                    <input type="text" name="localite" class="form-control @error('localite') is-invalid @enderror" value="{{ old('localite') }}" required>
                    @error('localite')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                        @foreach(['Dakar','Thiès','Diourbel','Fatick','Kaolack','Kaffrine','Louga','Saint-Louis','Matam','Tambacounda','Kédougou','Kolda','Sédhiou','Ziguinchor'] as $r)
                            <option value="{{ $r }}" {{ old('region', $authRegion) == $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                    @if(!$canEditScope && $authRegion)
                        <input type="hidden" name="region" value="{{ $authRegion }}">
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Lieu précis</label>
                    <input type="text" name="lieu_interception" class="form-control" value="{{ old('lieu_interception') }}" placeholder="Ex: Plage de Yoff, km 34 RN1...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Latitude</label>
                    <input type="number" name="latitude" class="form-control" value="{{ old('latitude') }}" step="0.00000001" placeholder="Ex: 14.6937">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Longitude</label>
                    <input type="number" name="longitude" class="form-control" value="{{ old('longitude') }}" step="0.00000001" placeholder="Ex: -17.4441">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Personnes interceptées</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nombre total <span class="text-danger">*</span></label>
                    <input type="number" name="nombre_personnes" class="form-control @error('nombre_personnes') is-invalid @enderror" value="{{ old('nombre_personnes', 1) }}" min="1" required>
                    @error('nombre_personnes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Hommes</label>
                    <input type="number" name="nombre_hommes" class="form-control" value="{{ old('nombre_hommes', 0) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Femmes</label>
                    <input type="number" name="nombre_femmes" class="form-control" value="{{ old('nombre_femmes', 0) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Mineurs</label>
                    <input type="number" name="nombre_mineurs" class="form-control" value="{{ old('nombre_mineurs', 0) }}" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nationalités</label>
                    <input type="text" name="nationalites" class="form-control" value="{{ old('nationalites') }}" placeholder="Ex: Malienne, Guinéenne, Gambienne">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Pays d'origine</label>
                    <input type="text" name="pays_origine" class="form-control" value="{{ old('pays_origine') }}" placeholder="Ex: Mali, Guinée...">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Pays de destination</label>
                    <input type="text" name="pays_destination" class="form-control" value="{{ old('pays_destination') }}" placeholder="Ex: Espagne, Italie...">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Moyen de transport</label>
                    <select name="moyen_transport" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        @foreach(['Pirogue','Véhicule terrestre','À pied','Avion','Bateau','Moto','Autre'] as $mt)
                            <option value="{{ $mt }}" {{ old('moyen_transport') == $mt ? 'selected' : '' }}>{{ $mt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Service & Agent responsable</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label class="form-label fw-bold">Agent responsable</label>
                    <select name="agent_id" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" {{ old('agent_id') == $a->id ? 'selected' : '' }}>{{ $a->nom_complet }} — {{ $a->grade }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Détails sur le cas...">{{ old('description') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Observations</label>
                    <textarea name="observations" class="form-control" rows="3" placeholder="Observations supplémentaires...">{{ old('observations') }}</textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                <a href="{{ route('immigrations.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
