@extends('layouts.app')
@section('title', 'Détail Amende')
@section('page-title', 'Détail Amende')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('amendes.index') }}">Amendes</a></li>
    <li class="breadcrumb-item active">{{ $amende->numero_amende }}</li>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-invoice-dollar me-2"></i>{{ $amende->numero_amende }}</span>
                @php $c = ['paye'=>'success','partiel'=>'warning','impaye'=>'danger']; @endphp
                <span class="badge bg-{{ $c[$amende->statut_paiement] ?? 'secondary' }}">{{ ucfirst($amende->statut_paiement) }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr><th>Contrevenant:</th><td>{{ $amende->nom_contrevenant ?? '-' }}</td></tr>
                            <tr><th>Infraction:</th><td>
                                @if($amende->infraction)
                                    <a href="{{ route('infractions.show', $amende->infraction) }}">{{ $amende->infraction->numero_dossier }}</a>
                                @else -
                                @endif
                            </td></tr>
                            <tr><th>Date émission:</th><td>{{ $amende->date_emission->format('d/m/Y') }}</td></tr>
                            <tr><th>Date échéance:</th><td>{{ $amende->date_echeance ? $amende->date_echeance->format('d/m/Y') : '-' }}</td></tr>
                            <tr><th>Service:</th><td>{{ $amende->service->nom ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 bg-light rounded mb-3">
                            <h3 class="fw-bold text-primary">{{ number_format($amende->montant, 0, ',', ' ') }} FCFA</h3>
                            <p class="text-muted mb-0">Montant total</p>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 text-center">
                                <div class="bg-success bg-opacity-10 rounded p-2">
                                    <h5 class="fw-bold text-success mb-0">{{ number_format($amende->montant_paye, 0, ',', ' ') }}</h5>
                                    <small class="text-muted">Payé (FCFA)</small>
                                </div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="bg-danger bg-opacity-10 rounded p-2">
                                    <h5 class="fw-bold text-danger mb-0">{{ number_format($amende->montant - $amende->montant_paye, 0, ',', ' ') }}</h5>
                                    <small class="text-muted">Reste (FCFA)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($amende->motif)
                <div class="mt-3">
                    <h6 class="fw-bold">Motif</h6>
                    <p class="text-muted">{{ $amende->motif }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Historique paiements -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2"></i>Historique des paiements</span>
                @if($amende->statut_paiement != 'paye')
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#paiementModal">
                    <i class="fas fa-plus me-1"></i>Paiement
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Montant</th><th>Mode</th><th>Enregistré par</th></tr>
                    </thead>
                    <tbody>
                        @forelse($amende->paiements as $paiement)
                        <tr>
                            <td>{{ $paiement->date_paiement->format('d/m/Y H:i') }}</td>
                            <td class="fw-bold text-success">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                            <td>{{ ucfirst(str_replace('_',' ',$paiement->mode_paiement)) }}</td>
                            <td>{{ $paiement->user->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucun paiement enregistré</td></tr>
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
                    <a href="{{ route('amendes.edit', $amende) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                    @if($amende->statut_paiement != 'paye')
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paiementModal"><i class="fas fa-money-bill me-1"></i>Enregistrer paiement</button>
                    @endif
                    <form action="{{ route('amendes.destroy', $amende) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm w-100"><i class="fas fa-trash me-1"></i>Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal paiement -->
@if($amende->statut_paiement != 'paye')
<div class="modal fade" id="paiementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enregistrer un paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('amendes.paiement', $amende) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Reste à payer: <strong>{{ number_format($amende->montant - $amende->montant_paye, 0, ',', ' ') }} FCFA</strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Montant payé</label>
                        <input type="number" name="montant_paiement" class="form-control" max="{{ $amende->montant - $amende->montant_paye }}" required>
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
