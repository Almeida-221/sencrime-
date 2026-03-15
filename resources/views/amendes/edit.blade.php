@extends('layouts.app')
@section('title', 'Modifier Amende')
@section('page-title', 'Modifier Amende')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('amendes.index') }}">Amendes</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-edit me-2"></i>Modifier: {{ $amende->numero_amende }}</div>
    <div class="card-body">
        <form action="{{ route('amendes.update', $amende) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">N° Amende <span class="text-danger">*</span></label>
                    <input type="text" name="numero_amende" class="form-control" value="{{ old('numero_amende', $amende->numero_amende) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Infraction liée</label>
                    <select name="infraction_id" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        @foreach($infractions as $inf)
                            <option value="{{ $inf->id }}" {{ old('infraction_id', $amende->infraction_id) == $inf->id ? 'selected' : '' }}>{{ $inf->numero_dossier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date d'émission <span class="text-danger">*</span></label>
                    <input type="date" name="date_emission" class="form-control" value="{{ old('date_emission', $amende->date_emission->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date d'échéance</label>
                    <input type="date" name="date_echeance" class="form-control" value="{{ old('date_echeance', $amende->date_echeance ? $amende->date_echeance->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Montant (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" name="montant" class="form-control" value="{{ old('montant', $amende->montant) }}" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Statut paiement</label>
                    <select name="statut_paiement" class="form-select">
                        <option value="impaye" {{ old('statut_paiement', $amende->statut_paiement) == 'impaye' ? 'selected' : '' }}>Impayé</option>
                        <option value="partiel" {{ old('statut_paiement', $amende->statut_paiement) == 'partiel' ? 'selected' : '' }}>Partiel</option>
                        <option value="paye" {{ old('statut_paiement', $amende->statut_paiement) == 'paye' ? 'selected' : '' }}>Payé</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nom contrevenant</label>
                    <input type="text" name="nom_contrevenant" class="form-control" value="{{ old('nom_contrevenant', $amende->nom_contrevenant) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Service</label>
                    <select name="service_id" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" {{ old('service_id', $amende->service_id) == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Motif</label>
                    <textarea name="motif" class="form-control" rows="2">{{ old('motif', $amende->motif) }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Mettre à jour</button>
                <a href="{{ route('amendes.show', $amende) }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
