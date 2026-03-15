@extends('layouts.app')
@section('title', 'Agents')
@section('page-title', 'Gestion des Agents')
@section('breadcrumb')
    <li class="breadcrumb-item active">Agents</li>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="service_id" class="form-select form-select-sm">
                    <option value="">Tous les services</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    <option value="suspendu" {{ request('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                    <option value="retraite" {{ request('statut') == 'retraite' ? 'selected' : '' }}>Retraité</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Filtrer</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('agents.index') }}" class="btn btn-secondary btn-sm w-100">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-user-shield me-2"></i>Liste des Agents ({{ $agents->total() }})</span>
        <a href="{{ route('agents.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouvel Agent</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Matricule</th><th>Nom Complet</th><th>Grade</th><th>Service</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $agent->matricule }}</span></td>
                        <td class="fw-bold">{{ $agent->nom_complet }}</td>
                        <td>{{ $agent->grade }}</td>
                        <td><small>{{ $agent->service->nom ?? '-' }}</small></td>
                        <td>
                            @php $colors = ['actif'=>'success','inactif'=>'secondary','suspendu'=>'warning','retraite'=>'info']; @endphp
                            <span class="badge bg-{{ $colors[$agent->statut] ?? 'secondary' }}">{{ ucfirst($agent->statut) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('agents.show', $agent) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('agents.edit', $agent) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('agents.destroy', $agent) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet agent ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun agent trouvé</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($agents->hasPages())
    <div class="card-footer">{{ $agents->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
