@extends('layouts.app')
@section('title', 'Détail Accident')
@section('page-title', 'Rapport Accident')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accidents.index') }}">Accidents</a></li>
    <li class="breadcrumb-item active">{{ $accident->numero_rapport }}</li>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-car-crash me-2"></i>{{ $accident->numero_rapport }}</span>
                <div>
                    @php $colors = ['leger'=>'success','grave'=>'warning','mortel'=>'danger']; @endphp
                    <span class="badge bg-{{ $colors[$accident->gravite] ?? 'secondary' }} me-2">{{ ucfirst($accident->gravite) }}</span>
                    <a href="{{ route('accidents.edit', $accident) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr><th>Date:</th><td>{{ $accident->date_accident->format('d/m/Y') }}</td></tr>
                            <tr><th>Heure:</th><td>{{ $accident->heure_accident ?? '-' }}</td></tr>
                            <tr><th>Localité:</th><td>{{ $accident->localite }}</td></tr>
                            <tr><th>Région:</th><td>{{ $accident->region ?? '-' }}</td></tr>
                            <tr><th>Lieu exact:</th><td>{{ $accident->lieu_exact ?? '-' }}</td></tr>
                            <tr><th>Type:</th><td>{{ $accident->type_accident }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <div class="bg-warning bg-opacity-10 rounded p-2">
                                    <h4 class="fw-bold text-warning mb-0">{{ $accident->nombre_victimes ?? 0 }}</h4>
                                    <small class="text-muted">Victimes</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="bg-info bg-opacity-10 rounded p-2">
                                    <h4 class="fw-bold text-info mb-0">{{ $accident->nombre_blesses ?? 0 }}</h4>
                                    <small class="text-muted">Blessés</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="bg-danger bg-opacity-10 rounded p-2">
                                    <h4 class="fw-bold text-danger mb-0">{{ $accident->nombre_morts ?? 0 }}</h4>
                                    <small class="text-muted">Décès</small>
                                </div>
                            </div>
                        </div>
                        <table class="table table-borderless table-sm">
                            <tr><th>Service:</th><td>{{ $accident->service->nom ?? '-' }}</td></tr>
                            <tr><th>Agent:</th><td>{{ $accident->agent->nom_complet ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="mt-3">
                    <h6 class="fw-bold">Description</h6>
                    <p class="text-muted">{{ $accident->description }}</p>
                </div>
                @if($accident->causes)
                <div>
                    <h6 class="fw-bold">Causes</h6>
                    <p class="text-muted">{{ $accident->causes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Informations</div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr><th>Statut:</th><td>
                        @php $sc = ['ouvert'=>'primary','en_cours'=>'warning','ferme'=>'success']; @endphp
                        <span class="badge bg-{{ $sc[$accident->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$accident->statut)) }}</span>
                    </td></tr>
                    <tr><th>Créé par:</th><td>{{ $accident->user->name ?? '-' }}</td></tr>
                    <tr><th>Créé le:</th><td>{{ $accident->created_at->format('d/m/Y H:i') }}</td></tr>
                </table>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('accidents.edit', $accident) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                    <form action="{{ route('accidents.destroy', $accident) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm w-100"><i class="fas fa-trash me-1"></i>Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
