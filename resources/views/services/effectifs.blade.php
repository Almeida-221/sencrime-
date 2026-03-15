@extends('layouts.app')
@section('title', 'Effectifs')
@section('page-title', 'Effectifs du Service')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('services.index') }}">Services</a></li>
    <li class="breadcrumb-item"><a href="{{ route('services.show', $service) }}">{{ $service->nom }}</a></li>
    <li class="breadcrumb-item active">Effectifs</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users me-2"></i>Effectifs - {{ $service->nom }}</span>
        <a href="{{ route('agents.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Ajouter un agent</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Matricule</th><th>Nom Complet</th><th>Grade</th><th>Fonction</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                    <tr>
                        <td>{{ $agent->matricule }}</td>
                        <td class="fw-bold">{{ $agent->nom_complet }}</td>
                        <td>{{ $agent->grade }}</td>
                        <td>{{ $agent->fonction ?? '-' }}</td>
                        <td>
                            @php $colors = ['actif'=>'success','inactif'=>'secondary','suspendu'=>'warning','retraite'=>'info']; @endphp
                            <span class="badge bg-{{ $colors[$agent->statut] ?? 'secondary' }}">{{ ucfirst($agent->statut) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('agents.show', $agent) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('agents.edit', $agent) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun agent dans ce service</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($agents->hasPages())
    <div class="card-footer">{{ $agents->links() }}</div>
    @endif
</div>
@endsection
