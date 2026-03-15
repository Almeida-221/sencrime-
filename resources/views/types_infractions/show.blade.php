@extends('layouts.app')
@section('title', 'Détail Type d\'Infraction')
@section('page-title', 'Type d\'Infraction')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('types-infractions.index') }}">Types d'Infraction</a></li>
    <li class="breadcrumb-item active">{{ $typesInfraction->nom }}</li>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="bg-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                    <i class="fas fa-exclamation-triangle fa-2x text-white"></i>
                </div>
                <h5 class="fw-bold">{{ $typesInfraction->nom }}</h5>
                <p class="text-muted mb-2"><span class="badge bg-secondary fs-6">{{ $typesInfraction->code }}</span></p>
                @php $cat = ['crime'=>'danger','delit'=>'warning','contravention'=>'info']; @endphp
                @if($typesInfraction->categorie)
                    <span class="badge bg-{{ $cat[$typesInfraction->categorie] ?? 'secondary' }} mb-2">{{ ucfirst($typesInfraction->categorie) }}</span>
                @endif
                <br>
                <span class="badge bg-{{ $typesInfraction->actif ? 'success' : 'secondary' }}">{{ $typesInfraction->actif ? 'Actif' : 'Inactif' }}</span>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <a href="{{ route('types-infractions.edit', $typesInfraction) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Détails</div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th>Amende min:</th>
                        <td>{{ $typesInfraction->amende_min ? number_format($typesInfraction->amende_min, 0, ',', ' ') . ' FCFA' : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Amende max:</th>
                        <td>{{ $typesInfraction->amende_max ? number_format($typesInfraction->amende_max, 0, ',', ' ') . ' FCFA' : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Infractions:</th>
                        <td><span class="badge bg-primary">{{ $typesInfraction->infractions_count }}</span></td>
                    </tr>
                </table>
                @if($typesInfraction->description)
                <hr>
                <p class="text-muted small mb-0">{{ $typesInfraction->description }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i>Infractions associées ({{ $typesInfraction->infractions_count }})</span>
                <a href="{{ route('infractions.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Nouvelle Infraction</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>N° Dossier</th><th>Date</th><th>Contrevenant</th><th>Statut</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse($typesInfraction->infractions()->latest('date_infraction')->take(20)->get() as $infraction)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $infraction->numero_dossier }}</span></td>
                                <td>{{ $infraction->date_infraction->format('d/m/Y') }}</td>
                                <td>{{ $infraction->contrevenant_nom_complet }}</td>
                                <td>
                                    @php $s = ['ouvert'=>'primary','en_cours'=>'warning','clos'=>'success','classe_sans_suite'=>'secondary']; @endphp
                                    <span class="badge bg-{{ $s[$infraction->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$infraction->statut)) }}</span>
                                </td>
                                <td><a href="{{ route('infractions.show', $infraction) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Aucune infraction pour ce type</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
