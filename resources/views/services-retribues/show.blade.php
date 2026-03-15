@extends('layouts.app')
@section('title', 'Détail Mission')
@section('page-title', 'Détail Mission Rétribuée')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('services-retribues.index') }}">Services Rétribués</a></li>
    <li class="breadcrumb-item active">{{ $serviceRetribue->numero_mission }}</li>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-handshake me-2"></i>{{ $serviceRetribue->numero_mission }}</span>
                @php $c = ['paye'=>'success','partiel'=>'warning','impaye'=>'danger']; @endphp
                <span class="badge bg-{{ $c[$serviceRetribue->statut_paiement] ?? 'secondary' }}">{{ ucfirst($serviceRetribue->statut_paiement) }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr><th>Type:</th><td>{{ $serviceRetribue->type_service }}</td></tr>
                            <tr><th>Client:</th><td>{{ $serviceRetribue->client }}</td></tr>
                            <tr><th>Date début:</th><td>{{ $serviceRetribue->date_debut->format('d/m/Y') }}</td></tr>
                            <tr><th>Date fin:</th><td>{{ $serviceRetribue->date_fin ? $serviceRetribue->date_fin->format('d/m/Y') : '-' }}</td></tr>
                            <tr><th>Localité:</th><td>{{ $serviceRetribue->localite ?? '-' }}</td></tr>
                            <tr><th>Service:</th><td>{{ $serviceRetribue->service->nom ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 bg-light rounded mb-3">
                            <h3 class="fw-bold text-primary">{{ number_format($serviceRetribue->montant, 0, ',', ' ') }} FCFA</h3>
                            <p class="text-muted mb-0">Montant total</p>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 text-center">
                                <div class="bg-success bg-opacity-10 rounded p-2">
                                    <h5 class="fw-bold text-success mb-0">{{ number_format($serviceRetribue->montant_paye, 0, ',', ' ') }}</h5>
                                    <small class="text-muted">Payé (FCFA)</small>
                                </div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="bg-danger bg-opacity-10 rounded p-2">
                                    <h5 class="fw-bold text-danger mb-0">{{ number_format($serviceRetribue->montant - $serviceRetribue->montant_paye, 0, ',', ' ') }}</h5>
                                    <small class="text-muted">Reste (FCFA)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($serviceRetribue->description)
                <div class="mt-3">
                    <h6 class="fw-bold">Description</h6>
                    <p class="text-muted">{{ $serviceRetribue->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Agents affectés -->
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-user-shield me-2"></i>Agents affectés</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Matricule</th><th>Nom</th><th>Grade</th></tr>
                    </thead>
                    <tbody>
                        @forelse($serviceRetribue->agents as $agent)
                        <tr>
                            <td>{{ $agent->matricule }}</td>
                            <td>{{ $agent->nom_complet }}</td>
                            <td>{{ $agent->grade }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Aucun agent affecté</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-cog me-2"></i>Actions</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('services-retribues.edit', $serviceRetribue) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                    @if($serviceRetribue->statut_paiement != 'paye')
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paiementModal"><i class="fas fa-money-bill me-1"></i>Enregistrer paiement</button>
                    @endif
                    <form action="{{ route('services-retribues.destroy', $serviceRetribue) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm w-100"><i class="fas fa-trash me-1"></i>Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if($serviceRetribue->statut_paiement != 'paye')
<div class="modal fade" id="paiementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enregistrer un paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('services-retribues.paiement', $serviceRetribue) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Reste à payer: <strong>{{ number_format($serviceRetribue->montant - $serviceRetribue->montant_paye, 0, ',', ' ') }} FCFA</strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Montant payé</label>
                        <input type="number" name="montant_paiement" class="form-control" max="{{ $serviceRetribue->montant - $serviceRetribue->montant_paye }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mode de paiement</label>
                        <select name="mode_paiement" class="form-select">
                            <option value="especes">Espèces</option>
                            <option value="virement">Virement</option>
                            <option value="cheque">Chèque</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Valider</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
