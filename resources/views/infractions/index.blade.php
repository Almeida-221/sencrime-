@extends('layouts.app')
@section('title', 'Infractions')
@section('page-title', 'Gestion des Infractions')
@section('breadcrumb')
    <li class="breadcrumb-item active">Infractions</li>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2"><input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}"></div>
            <div class="col-md-2">
                <select name="type_infraction_id" class="form-select form-select-sm">
                    <option value="">Tous types</option>
                    @foreach($typesInfractions as $t)
                        <option value="{{ $t->id }}" {{ request('type_infraction_id') == $t->id ? 'selected' : '' }}>{{ $t->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="ouvert" {{ request('statut') == 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                    <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                    <option value="ferme" {{ request('statut') == 'ferme' ? 'selected' : '' }}>Fermé</option>
                    <option value="classe" {{ request('statut') == 'classe' ? 'selected' : '' }}>Classé</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}"></div>
            <div class="col-md-2"><input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}"></div>
            <div class="col-md-1"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button></div>
            <div class="col-md-1"><a href="{{ route('infractions.index') }}" class="btn btn-secondary btn-sm w-100"><i class="fas fa-times"></i></a></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-gavel me-2"></i>Infractions ({{ $infractions->total() }})</span>
        <a href="{{ route('infractions.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouvelle Infraction</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>N° Dossier</th><th>Type</th><th>Date</th><th>Localité</th><th>Contrevenant</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($infractions as $inf)
                    <tr>
                        <td><a href="{{ route('infractions.show', $inf) }}" class="fw-bold text-decoration-none">{{ $inf->numero_dossier }}</a></td>
                        <td><small class="badge bg-secondary">{{ $inf->typeInfraction->nom ?? '-' }}</small></td>
                        <td>{{ $inf->date_infraction->format('d/m/Y') }}</td>
                        <td>{{ $inf->localite }}</td>
                        <td>{{ $inf->contrevenant_nom_complet ?: '-' }}</td>
                        <td>
                            @php $colors = ['ouvert'=>'primary','en_cours'=>'warning','ferme'=>'success','classe'=>'secondary']; @endphp
                            <span class="badge bg-{{ $colors[$inf->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$inf->statut)) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('infractions.show', $inf) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('infractions.edit', $inf) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                            @role('super_admin|admin')
                            <form action="{{ route('infractions.destroy', $inf) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                            </form>
                            @endrole
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune infraction trouvée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($infractions->hasPages())
    <div class="card-footer">{{ $infractions->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
