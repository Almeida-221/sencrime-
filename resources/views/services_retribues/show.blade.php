@extends('layouts.app')
@section('title', 'Détail Mission')
@section('page-title', 'Mission Rétribuée')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('services-retribues.index') }}">Services Rétribués</a></li>
    <li class="breadcrumb-item active">{{ $servicesRetribue->numero_mission }}</li>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-handshake me-2"></i>{{ $servicesRetribue->numero_mission }} — {{ $servicesRetribue->titre }}</span>
                @php
                    $cp = ['paye'=>'success','partiel'=>'warning','impaye'=>'danger'];
                    $cs = ['planifie'=>'info','en_cours'=>'primary','termine'=>'success','annule'=>'secondary'];
                @endphp
                <div>
                    <span class="badge bg-{{ $cs[$servicesRetribue->statut] ?? 'secondary' }} me-1">{{ ucfirst(str_replace('_',' ',$servicesRetribue->statut)) }}</span>
                    <span class="badge bg-{{ $cp[$servicesRetribue->statut_paiement] ?? 'secondary' }}">{{ ucfirst($servicesRetribue->statut_paiement) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr><th>Type de mission:</th><td>{{ $servicesRetribue->type_mission }}</td></tr>
                            <tr><th>Date début:</th><td>{{ $servicesRetribue->date_debut?->format('d/m/Y') ?? '-' }}</td></tr>
                            <tr><th>Date fin:</th><td>{{ $servicesRetribue->date_fin?->format('d/m/Y') ?? '-' }}</td></tr>
                            <tr><th>Localité:</th><td>{{ $servicesRetribue->localite ?? '-' }}</td></tr>
                            <tr><th>Région:</th><td>{{ $servicesRetribue->region ?? '-' }}</td></tr>
                            <tr><th>Service:</th><td>{{ $servicesRetribue->service->nom ?? '-' }}</td></tr>
                            <tr><th>Créé par:</th><td>{{ $servicesRetribue->user->name ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 bg-light rounded mb-3">
                            <h3 class="fw-bold text-primary">{{ number_format($servicesRetribue->montant_total, 0, ',', ' ') }} FCFA</h3>
                            <p class="text-muted mb-0">Montant total</p>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 text-center">
                                <div class="bg-success bg-opacity-10 rounded p-2">
                                    <h5 class="fw-bold text-success mb-0">{{ number_format($servicesRetribue->montant_paye, 0, ',', ' ') }}</h5>
                                    <small class="text-muted">Payé (FCFA)</small>
                                </div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="bg-danger bg-opacity-10 rounded p-2">
                                    <h5 class="fw-bold text-danger mb-0">{{ number_format($servicesRetribue->montant_restant, 0, ',', ' ') }}</h5>
                                    <small class="text-muted">Reste (FCFA)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($servicesRetribue->description)
                <hr>
                <h6 class="fw-bold">Description</h6>
                <p class="text-muted">{{ $servicesRetribue->description }}</p>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-user me-2"></i>Informations client</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><th>Nom:</th><td>{{ $servicesRetribue->client_nom }}</td></tr>
                            <tr><th>Téléphone:</th><td>{{ $servicesRetribue->client_telephone ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><th>Email:</th><td>{{ $servicesRetribue->client_email ?? '-' }}</td></tr>
                            <tr><th>Adresse:</th><td>{{ $servicesRetribue->client_adresse ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-user-shield me-2"></i>Agents affectés ({{ $servicesRetribue->agents->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Matricule</th><th>Nom</th><th>Grade</th><th>Rôle</th><th>Rémunération</th></tr>
                    </thead>
                    <tbody>
                        @forelse($servicesRetribue->agents as $agent)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $agent->matricule }}</span></td>
                            <td>{{ $agent->nom_complet }}</td>
                            <td><small>{{ $agent->grade }}</small></td>
                            <td>{{ $agent->pivot->role ?? '-' }}</td>
                            <td>{{ $agent->pivot->remuneration ? number_format($agent->pivot->remuneration, 0, ',', ' ') . ' FCFA' : '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun agent affecté</td></tr>
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
                    <a href="{{ route('services-retribues.edit', $servicesRetribue) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                    @if($servicesRetribue->statut_paiement != 'paye')
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paiementModal">
                        <i class="fas fa-money-bill me-1"></i>Enregistrer paiement
                    </button>
                    @endif
                    <form action="{{ route('services-retribues.destroy', $servicesRetribue) }}" method="POST" onsubmit="return confirm('Supprimer cette mission ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm w-100"><i class="fas fa-trash me-1"></i>Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
        @if($servicesRetribue->date_paiement)
        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-receipt me-2"></i>Dernier paiement</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><th>Date:</th><td>{{ $servicesRetribue->date_paiement->format('d/m/Y') }}</td></tr>
                    <tr><th>Mode:</th><td>{{ ucfirst($servicesRetribue->mode_paiement ?? '-') }}</td></tr>
                    <tr><th>Montant payé:</th><td class="text-success fw-bold">{{ number_format($servicesRetribue->montant_paye, 0, ',', ' ') }} FCFA</td></tr>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

@if($servicesRetribue->statut_paiement != 'paye')
<div class="modal fade" id="paiementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-money-bill me-2"></i>Enregistrer un paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('services-retribues.paiement', $servicesRetribue) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Reste à payer : <strong class="text-danger">{{ number_format($servicesRetribue->montant_restant, 0, ',', ' ') }} FCFA</strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Montant du paiement <span class="text-danger">*</span></label>
                        <input type="number" name="montant_paiement" class="form-control" max="{{ $servicesRetribue->montant_restant }}" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mode de paiement <span class="text-danger">*</span></label>
                        <select name="mode_paiement" class="form-select" required>
                            <option value="especes">Espèces</option>
                            <option value="virement">Virement bancaire</option>
                            <option value="cheque">Chèque</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Valider le paiement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
