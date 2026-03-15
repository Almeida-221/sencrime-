@extends('layouts.app')
@section('title', 'Services Rétribués')
@section('page-title', 'Gestion des Services Rétribués')
@section('breadcrumb')
    <li class="breadcrumb-item active">Services Rétribués</li>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="N° mission, titre, client..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
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
                    <option value="planifie" {{ request('statut') == 'planifie' ? 'selected' : '' }}>Planifié</option>
                    <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                    <option value="termine" {{ request('statut') == 'termine' ? 'selected' : '' }}>Terminé</option>
                    <option value="annule" {{ request('statut') == 'annule' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut_paiement" class="form-select form-select-sm">
                    <option value="">Tout paiement</option>
                    <option value="impaye" {{ request('statut_paiement') == 'impaye' ? 'selected' : '' }}>Impayé</option>
                    <option value="partiel" {{ request('statut_paiement') == 'partiel' ? 'selected' : '' }}>Partiel</option>
                    <option value="paye" {{ request('statut_paiement') == 'paye' ? 'selected' : '' }}>Payé</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('services-retribues.index') }}" class="btn btn-secondary btn-sm w-100">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-handshake me-2"></i>Missions Rétribuées ({{ $missions->total() }})</span>
        <a href="{{ route('services-retribues.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouvelle Mission</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Mission</th>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Client</th>
                        <th>Date début</th>
                        <th>Montant total</th>
                        <th>Paiement</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($missions as $mission)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $mission->numero_mission }}</span></td>
                        <td class="fw-bold">{{ $mission->titre }}</td>
                        <td><small>{{ $mission->type_mission }}</small></td>
                        <td>{{ $mission->client_nom }}</td>
                        <td>{{ $mission->date_debut ? $mission->date_debut->format('d/m/Y') : '-' }}</td>
                        <td>{{ number_format($mission->montant_total, 0, ',', ' ') }} FCFA</td>
                        <td>
                            @php $cp = ['paye'=>'success','partiel'=>'warning','impaye'=>'danger']; @endphp
                            <span class="badge bg-{{ $cp[$mission->statut_paiement] ?? 'secondary' }}">{{ ucfirst($mission->statut_paiement) }}</span>
                        </td>
                        <td>
                            @php $cs = ['planifie'=>'info','en_cours'=>'primary','termine'=>'success','annule'=>'secondary']; @endphp
                            <span class="badge bg-{{ $cs[$mission->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$mission->statut)) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('services-retribues.show', $mission) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('services-retribues.edit', $mission) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('services-retribues.destroy', $mission) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette mission ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Aucune mission enregistrée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($missions->hasPages())
    <div class="card-footer">{{ $missions->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
