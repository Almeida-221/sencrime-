@extends('layouts.app')
@section('title', 'Services')
@section('page-title', 'Gestion des Services')
@section('breadcrumb')
    <li class="breadcrumb-item active">Services</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-building me-2"></i>Liste des Services</span>
        <a href="{{ route('services.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Nouveau Service
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th><th>Nom</th><th>Localité</th><th>Région</th><th>Effectif</th><th>Statut</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $service->code }}</span></td>
                        <td class="fw-bold">{{ $service->nom }}</td>
                        <td>{{ $service->localite ?? '-' }}</td>
                        <td>{{ $service->region ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ $service->agents_count }} agents</span></td>
                        <td>
                            @if($service->actif)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-danger">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('services.show', $service) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('services.edit', $service) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('services.effectifs', $service) }}" class="btn btn-sm btn-primary btn-action"><i class="fas fa-users"></i></a>
                            <form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce service ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun service enregistré</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($services->hasPages())
    <div class="card-footer">{{ $services->links() }}</div>
    @endif
</div>
@endsection
