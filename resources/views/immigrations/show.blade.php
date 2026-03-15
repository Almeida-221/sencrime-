@extends('layouts.app')
@section('title', 'Détail Cas Immigration')
@section('page-title', 'Cas d\'Immigration Clandestine')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('immigrations.index') }}">Immigration Clandestine</a></li>
    <li class="breadcrumb-item active">{{ $immigration->numero_cas }}</li>
@endsection
@section('content')
@php
    $colorsOp = ['interception'=>'info','arrestation'=>'danger','rapatriement'=>'success'];
    $colorsSt = ['ouvert'=>'primary','en_cours'=>'warning','ferme'=>'success','rapatrie'=>'info'];
@endphp
<div class="row g-3">
    <!-- Colonne gauche -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:80px;height:80px;background:linear-gradient(135deg,#0d2137,#1a3a5c);">
                    <i class="fas fa-passport fa-2x text-white"></i>
                </div>
                <h5 class="fw-bold">{{ $immigration->numero_cas }}</h5>
                <p class="text-muted small mb-2">{{ $immigration->date_interception->format('d/m/Y') }}</p>
                <div class="mb-2">
                    <span class="badge bg-{{ $colorsOp[$immigration->type_operation] ?? 'secondary' }} me-1">
                        {{ ucfirst($immigration->type_operation) }}
                    </span>
                    <span class="badge bg-{{ $colorsSt[$immigration->statut] ?? 'secondary' }}">
                        {{ ucfirst(str_replace('_',' ',$immigration->statut)) }}
                    </span>
                </div>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <a href="{{ route('immigrations.edit', $immigration) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1"></i>Modifier
                    </a>
                    <form action="{{ route('immigrations.destroy', $immigration) }}" method="POST"
                          onsubmit="return confirm('Supprimer ce cas ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash me-1"></i>Supprimer</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Chiffres clés -->
        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-users me-2"></i>Personnes interceptées</div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h2 class="fw-bold text-danger mb-0">{{ $immigration->nombre_personnes }}</h2>
                    <small class="text-muted">Personnes au total</small>
                </div>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="bg-primary bg-opacity-10 rounded p-2">
                            <div class="fw-bold text-primary">{{ $immigration->nombre_hommes }}</div>
                            <small class="text-muted">Hommes</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-danger bg-opacity-10 rounded p-2">
                            <div class="fw-bold text-danger">{{ $immigration->nombre_femmes }}</div>
                            <small class="text-muted">Femmes</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-warning bg-opacity-10 rounded p-2">
                            <div class="fw-bold text-warning">{{ $immigration->nombre_mineurs }}</div>
                            <small class="text-muted">Mineurs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Responsable -->
        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-user-shield me-2"></i>Responsable</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><th>Service:</th><td>{{ $immigration->service->nom ?? '-' }}</td></tr>
                    <tr><th>Agent:</th><td>{{ $immigration->agent->nom_complet ?? '-' }}</td></tr>
                    <tr><th>Enregistré par:</th><td>{{ $immigration->user->name ?? '-' }}</td></tr>
                    <tr><th>Le:</th><td>{{ $immigration->created_at->format('d/m/Y H:i') }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Colonne droite -->
    <div class="col-md-8">
        <!-- Détails du cas -->
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Détails du cas</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr><th>Localité:</th><td>{{ $immigration->localite }}</td></tr>
                            <tr><th>Région:</th><td>{{ $immigration->region ?? '-' }}</td></tr>
                            <tr><th>Lieu précis:</th><td>{{ $immigration->lieu_interception ?? '-' }}</td></tr>
                            @if($immigration->latitude && $immigration->longitude)
                            <tr>
                                <th>Coordonnées:</th>
                                <td>
                                    <small class="text-muted">
                                        {{ $immigration->latitude }}, {{ $immigration->longitude }}
                                    </small>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr><th>Nationalités:</th><td>{{ $immigration->nationalites ?? '-' }}</td></tr>
                            <tr><th>Pays d'origine:</th><td>{{ $immigration->pays_origine ?? '-' }}</td></tr>
                            <tr><th>Pays destination:</th><td>{{ $immigration->pays_destination ?? '-' }}</td></tr>
                            <tr><th>Moyen transport:</th><td>{{ $immigration->moyen_transport ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($immigration->latitude && $immigration->longitude)
        <!-- Mini carte -->
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-map-marker-alt me-2"></i>Localisation</div>
            <div class="card-body p-0" style="height:260px;">
                <iframe
                    width="100%"
                    height="260"
                    frameborder="0"
                    style="border:0;border-radius:0 0 12px 12px;"
                    loading="lazy"
                    src="https://maps.google.com/maps?q={{ $immigration->latitude }},{{ $immigration->longitude }}&z=10&output=embed"
                ></iframe>
            </div>
        </div>
        @endif

        <!-- Description & Observations -->
        @if($immigration->description || $immigration->observations)
        <div class="card">
            <div class="card-header"><i class="fas fa-file-alt me-2"></i>Description & Observations</div>
            <div class="card-body">
                @if($immigration->description)
                <h6 class="fw-bold">Description</h6>
                <p class="text-muted">{{ $immigration->description }}</p>
                @endif
                @if($immigration->observations)
                <h6 class="fw-bold mt-3">Observations</h6>
                <p class="text-muted mb-0">{{ $immigration->observations }}</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Changer statut rapide -->
        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-exchange-alt me-2"></i>Changer le statut</div>
            <div class="card-body">
                <form action="{{ route('immigrations.update', $immigration) }}" method="POST" class="d-flex gap-2 align-items-center">
                    @csrf @method('PUT')
                    {{-- Champs cachés pour conserver les valeurs obligatoires --}}
                    <input type="hidden" name="numero_cas" value="{{ $immigration->numero_cas }}">
                    <input type="hidden" name="date_interception" value="{{ $immigration->date_interception->format('Y-m-d') }}">
                    <input type="hidden" name="localite" value="{{ $immigration->localite }}">
                    <input type="hidden" name="nombre_personnes" value="{{ $immigration->nombre_personnes }}">
                    <input type="hidden" name="type_operation" value="{{ $immigration->type_operation }}">
                    <select name="statut" class="form-select form-select-sm" style="max-width:180px;">
                        <option value="ouvert"   {{ $immigration->statut == 'ouvert'   ? 'selected' : '' }}>Ouvert</option>
                        <option value="en_cours" {{ $immigration->statut == 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="ferme"    {{ $immigration->statut == 'ferme'    ? 'selected' : '' }}>Fermé</option>
                        <option value="rapatrie" {{ $immigration->statut == 'rapatrie' ? 'selected' : '' }}>Rapatrié</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-check me-1"></i>Mettre à jour
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
