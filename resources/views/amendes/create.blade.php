@extends('layouts.app')
@section('title', 'Nouvelle Amende')
@section('page-title', 'Enregistrer une Amende')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('amendes.index') }}">Amendes</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-file-invoice-dollar me-2"></i>Enregistrer une Amende</div>
    <div class="card-body">
        <form action="{{ route('amendes.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">N° Amende <span class="text-danger">*</span></label>
                    <input type="text" name="numero_amende" class="form-control @error('numero_amende') is-invalid @enderror" value="{{ old('numero_amende', $numeroAmende) }}" required>
                    @error('numero_amende')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Infraction liée</label>
                    <select name="infraction_id" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        @foreach($infractions as $inf)
                            <option value="{{ $inf->id }}" {{ old('infraction_id', request('infraction_id')) == $inf->id ? 'selected' : '' }}>{{ $inf->numero_dossier }} - {{ $inf->typeInfraction->nom ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date d'amende <span class="text-danger">*</span></label>
                    <input type="date" name="date_amende" class="form-control @error('date_amende') is-invalid @enderror" value="{{ old('date_amende', date('Y-m-d')) }}" required>
                    @error('date_amende')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date d'échéance</label>
                    <input type="date" name="date_echeance" class="form-control" value="{{ old('date_echeance') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Montant (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" name="montant" class="form-control @error('montant') is-invalid @enderror" value="{{ old('montant') }}" min="0" required>
                    @error('montant')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    <label class="form-label fw-bold">Nom contrevenant</label>
                    <input type="text" name="nom_contrevenant" class="form-control" value="{{ old('nom_contrevenant') }}">
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
                    <label class="form-label fw-bold">Motif</label>
                    <textarea name="motif" class="form-control" rows="2">{{ old('motif') }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                <a href="{{ route('amendes.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
