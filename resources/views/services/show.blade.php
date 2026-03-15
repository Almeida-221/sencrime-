@extends('layouts.app')
@section('title', 'Détail Service')
@section('page-title', 'Détail Service')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('services.index') }}">Services</a></li>
    <li class="breadcrumb-item active">{{ $service->nom }}</li>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-building me-2"></i>Informations</div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr><th>Code:</th><td><span class="badge bg-secondary">{{ $service->code }}</span></td></tr>
                    <tr><th>Nom:</th><td>{{ $service->nom }}</td></tr>
                    <tr><th>Localité:</th><td>{{ $service->localite ?? '-' }}</td></tr>
                    <tr><th>Région:</th><td>{{ $service->region ?? '-' }}</td></tr>
                    <tr><th>Téléphone:</th><td>{{ $service->telephone ?? '-' }}</td></tr>
                    <tr><th>Email:</th><td>{{ $service->email ?? '-' }}</td></tr>
                    <tr><th>Effectif:</th><td><span class="badge bg-info">{{ $effectif }} agents actifs</span></td></tr>
                    <tr><th>Statut:</th><td>
                        @if($service->actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </td></tr>
                </table>
                @if($service->description)
                <p class="text-muted small">{{ $service->description }}</p>
                @endif
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('services.edit', $service) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                    <a href="{{ route('services.effectifs', $service) }}" class="btn btn-primary btn-sm"><i class="fas fa-users me-1"></i>Effectifs</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-user-shield me-2"></i>Agents du service</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Matricule</th><th>Nom</th><th>Grade</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                            @forelse($service->agents as $agent)
                            <tr>
                                <td>{{ $agent->matricule }}</td>
                                <td>{{ $agent->nom_complet }}</td>
                                <td>{{ $agent->grade }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($agent->statut) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Aucun agent dans ce service</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
