@extends('layouts.app')
@section('title', 'Immigration Clandestine')
@section('page-title', 'Immigration Clandestine')
@section('breadcrumb')
    <li class="breadcrumb-item active">Immigration Clandestine</li>
@endsection
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-2">
                <h5 class="fw-bold text-primary mb-0">{{ $cas->total() }}</h5>
                <small class="text-muted">Total cas</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-2">
                <h5 class="fw-bold text-danger mb-0">{{ number_format($totalPersonnes) }}</h5>
                <small class="text-muted">Personnes interceptées</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-2">
                <h5 class="fw-bold text-warning mb-0">
                    {{ \App\Models\ImmigrationClandestine::where('statut','ouvert')->orWhere('statut','en_cours')->count() }}
                </h5>
                <small class="text-muted">Cas actifs</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="N° cas, localité, pays..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="ouvert"    {{ request('statut') == 'ouvert'    ? 'selected' : '' }}>Ouvert</option>
                    <option value="en_cours"  {{ request('statut') == 'en_cours'  ? 'selected' : '' }}>En cours</option>
                    <option value="ferme"     {{ request('statut') == 'ferme'     ? 'selected' : '' }}>Fermé</option>
                    <option value="rapatrie"  {{ request('statut') == 'rapatrie'  ? 'selected' : '' }}>Rapatrié</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type_operation" class="form-select form-select-sm">
                    <option value="">Type opération</option>
                    <option value="interception" {{ request('type_operation') == 'interception' ? 'selected' : '' }}>Interception</option>
                    <option value="arrestation"  {{ request('type_operation') == 'arrestation'  ? 'selected' : '' }}>Arrestation</option>
                    <option value="rapatriement" {{ request('type_operation') == 'rapatriement' ? 'selected' : '' }}>Rapatriement</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('immigrations.index') }}" class="btn btn-secondary btn-sm w-100">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-passport me-2"></i>Cas d'Immigration ({{ $cas->total() }})</span>
        <a href="{{ route('immigrations.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouveau Cas</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Cas</th>
                        <th>Date</th>
                        <th>Localité</th>
                        <th>Personnes</th>
                        <th>Pays Origine</th>
                        <th>Type Opération</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cas as $item)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $item->numero_cas }}</span></td>
                        <td>{{ $item->date_interception->format('d/m/Y') }}</td>
                        <td>
                            {{ $item->localite }}
                            @if($item->region)<br><small class="text-muted">{{ $item->region }}</small>@endif
                        </td>
                        <td>
                            <span class="fw-bold text-danger">{{ $item->nombre_personnes }}</span>
                            <br>
                            <small class="text-muted">
                                H:{{ $item->nombre_hommes }} F:{{ $item->nombre_femmes }} M:{{ $item->nombre_mineurs }}
                            </small>
                        </td>
                        <td>{{ $item->pays_origine ?? '-' }}</td>
                        <td>
                            @php $to = ['interception'=>'info','arrestation'=>'danger','rapatriement'=>'success']; @endphp
                            <span class="badge bg-{{ $to[$item->type_operation] ?? 'secondary' }}">
                                {{ ucfirst($item->type_operation) }}
                            </span>
                        </td>
                        <td>
                            @php $ts = ['ouvert'=>'primary','en_cours'=>'warning','ferme'=>'success','rapatrie'=>'info']; @endphp
                            <span class="badge bg-{{ $ts[$item->statut] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_',' ',$item->statut)) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('immigrations.show', $item) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('immigrations.edit', $item) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                            @role('super_admin|admin')
                            <form action="{{ route('immigrations.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce cas ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                            </form>
                            @endrole
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucun cas enregistré</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($cas->hasPages())
    <div class="card-footer">{{ $cas->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
