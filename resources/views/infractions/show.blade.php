@extends('layouts.app')
@section('title', 'Détail Infraction')
@section('page-title', 'Dossier Infraction')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('infractions.index') }}">Infractions</a></li>
    <li class="breadcrumb-item active">{{ $infraction->numero_dossier }}</li>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-gavel me-2"></i>Dossier: {{ $infraction->numero_dossier }}</span>
                <div>
                    @php $colors = ['ouvert'=>'primary','en_cours'=>'warning','ferme'=>'success','classe'=>'secondary']; @endphp
                    <span class="badge bg-{{ $colors[$infraction->statut] ?? 'secondary' }} me-2">{{ ucfirst(str_replace('_',' ',$infraction->statut)) }}</span>
                    <a href="{{ route('infractions.edit', $infraction) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr><th>Type:</th><td><span class="badge bg-secondary">{{ $infraction->typeInfraction->nom ?? '-' }}</span></td></tr>
                            <tr><th>Date:</th><td>{{ $infraction->date_infraction->format('d/m/Y') }}</td></tr>
                            <tr><th>Localité:</th><td>{{ $infraction->localite }}</td></tr>
                            <tr><th>Région:</th><td>{{ $infraction->region ?? '-' }}</td></tr>
                            <tr><th>Service:</th><td>{{ $infraction->service->nom ?? '-' }}</td></tr>
                            <tr><th>Agent:</th><td>{{ $infraction->agent->nom_complet ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">Contrevenant</h6>
                        <table class="table table-borderless table-sm">
                            <tr><th>Nom:</th><td>{{ $infraction->contrevenant_nom_complet ?: '-' }}</td></tr>
                            <tr><th>Nationalité:</th><td>{{ $infraction->nationalite_contrevenant ?? '-' }}</td></tr>
                            <tr><th>Naissance:</th><td>{{ $infraction->date_naissance_contrevenant ? $infraction->date_naissance_contrevenant->format('d/m/Y') : '-' }}</td></tr>
                            <tr><th>Adresse:</th><td>{{ $infraction->adresse_contrevenant ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="mt-3">
                    <h6 class="fw-bold">Description</h6>
                    <p class="text-muted">{{ $infraction->description }}</p>
                </div>
                @if($infraction->observations)
                <div>
                    <h6 class="fw-bold">Observations</h6>
                    <p class="text-muted">{{ $infraction->observations }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Amendes liées -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-invoice-dollar me-2"></i>Amendes associées</span>
                <a href="{{ route('amendes.create') }}?infraction_id={{ $infraction->id }}" class="btn btn-sm btn-success"><i class="fas fa-plus me-1"></i>Ajouter amende</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>N° Amende</th><th>Montant</th><th>Payé</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($infraction->amendes as $amende)
                        <tr>
                            <td>{{ $amende->numero_amende }}</td>
                            <td>{{ number_format($amende->montant, 0, ',', ' ') }} FCFA</td>
                            <td>{{ number_format($amende->montant_paye, 0, ',', ' ') }} FCFA</td>
                            <td>
                                @php $c = ['paye'=>'success','partiel'=>'warning','impaye'=>'danger']; @endphp
                                <span class="badge bg-{{ $c[$amende->statut_paiement] ?? 'secondary' }}">{{ ucfirst($amende->statut_paiement) }}</span>
                            </td>
                            <td><a href="{{ route('amendes.show', $amende) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucune amende</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Métadonnées</div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr><th>Créé par:</th><td>{{ $infraction->user->name ?? '-' }}</td></tr>
                    <tr><th>Créé le:</th><td>{{ $infraction->created_at->format('d/m/Y H:i') }}</td></tr>
                    <tr><th>Modifié le:</th><td>{{ $infraction->updated_at->format('d/m/Y H:i') }}</td></tr>
                </table>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('infractions.edit', $infraction) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                    <form action="{{ route('infractions.destroy', $infraction) }}" method="POST" onsubmit="return confirm('Supprimer ce dossier ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm w-100"><i class="fas fa-trash me-1"></i>Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
