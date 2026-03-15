@extends('layouts.app')
@section('title', 'Types d\'Infraction')
@section('page-title', 'Types d\'Infraction')
@section('breadcrumb')
    <li class="breadcrumb-item active">Types d'Infraction</li>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher par nom ou code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="categorie" class="form-select form-select-sm">
                    <option value="">Toutes catégories</option>
                    <option value="crime" {{ request('categorie') == 'crime' ? 'selected' : '' }}>Crime</option>
                    <option value="delit" {{ request('categorie') == 'delit' ? 'selected' : '' }}>Délit</option>
                    <option value="contravention" {{ request('categorie') == 'contravention' ? 'selected' : '' }}>Contravention</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Filtrer</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('types-infractions.index') }}" class="btn btn-secondary btn-sm w-100">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-tags me-2"></i>Types d'Infraction ({{ $types->total() }})</span>
        <a href="{{ route('types-infractions.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouveau Type</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Amende Min</th>
                        <th>Amende Max</th>
                        <th>Infractions</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $type->code }}</span></td>
                        <td class="fw-bold">{{ $type->nom }}</td>
                        <td>
                            @php $cat = ['crime'=>'danger','delit'=>'warning','contravention'=>'info']; @endphp
                            @if($type->categorie)
                                <span class="badge bg-{{ $cat[$type->categorie] ?? 'secondary' }}">{{ ucfirst($type->categorie) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $type->amende_min ? number_format($type->amende_min, 0, ',', ' ') . ' FCFA' : '-' }}</td>
                        <td>{{ $type->amende_max ? number_format($type->amende_max, 0, ',', ' ') . ' FCFA' : '-' }}</td>
                        <td><span class="badge bg-primary">{{ $type->infractions_count }}</span></td>
                        <td>
                            <span class="badge bg-{{ $type->actif ? 'success' : 'secondary' }}">{{ $type->actif ? 'Actif' : 'Inactif' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('types-infractions.show', $type) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('types-infractions.edit', $type) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('types-infractions.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce type d\'infraction ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucun type d'infraction trouvé</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($types->hasPages())
    <div class="card-footer">{{ $types->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
