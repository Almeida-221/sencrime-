@extends('layouts.app')
@section('title', 'Accidents')
@section('page-title', 'Gestion des Accidents')
@section('breadcrumb')
    <li class="breadcrumb-item active">Accidents</li>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2"><input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}"></div>
            <div class="col-md-2">
                <select name="gravite" class="form-select form-select-sm">
                    <option value="">Toutes gravités</option>
                    <option value="leger" {{ request('gravite') == 'leger' ? 'selected' : '' }}>Léger</option>
                    <option value="grave" {{ request('gravite') == 'grave' ? 'selected' : '' }}>Grave</option>
                    <option value="mortel" {{ request('gravite') == 'mortel' ? 'selected' : '' }}>Mortel</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="ouvert" {{ request('statut') == 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                    <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                    <option value="ferme" {{ request('statut') == 'ferme' ? 'selected' : '' }}>Fermé</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}"></div>
            <div class="col-md-2"><input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}"></div>
            <div class="col-md-1"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button></div>
            <div class="col-md-1"><a href="{{ route('accidents.index') }}" class="btn btn-secondary btn-sm w-100"><i class="fas fa-times"></i></a></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-car-crash me-2"></i>Accidents ({{ $accidents->total() }})</span>
        <a href="{{ route('accidents.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouvel Accident</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>N° Rapport</th><th>Date</th><th>Localité</th><th>Type</th><th>Gravité</th><th>Victimes</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($accidents as $acc)
                    <tr>
                        <td><a href="{{ route('accidents.show', $acc) }}" class="fw-bold text-decoration-none">{{ $acc->numero_rapport }}</a></td>
                        <td>{{ $acc->date_accident->format('d/m/Y') }}</td>
                        <td>{{ $acc->localite }}</td>
                        <td><small>{{ $acc->type_accident }}</small></td>
                        <td>
                            @php $colors = ['leger'=>'success','grave'=>'warning','mortel'=>'danger']; @endphp
                            <span class="badge bg-{{ $colors[$acc->gravite] ?? 'secondary' }}">{{ ucfirst($acc->gravite) }}</span>
                        </td>
                        <td>{{ $acc->nombre_victimes ?? 0 }}</td>
                        <td>
                            @php $sc = ['ouvert'=>'primary','en_cours'=>'warning','ferme'=>'success']; @endphp
                            <span class="badge bg-{{ $sc[$acc->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$acc->statut)) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('accidents.show', $acc) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('accidents.edit', $acc) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                            @role('super_admin|admin')
                            <form action="{{ route('accidents.destroy', $acc) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                            </form>
                            @endrole
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucun accident trouvé</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($accidents->hasPages())
    <div class="card-footer">{{ $accidents->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
