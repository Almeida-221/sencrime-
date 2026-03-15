@extends('layouts.app')
@section('title', 'Amendes')
@section('page-title', 'Gestion des Amendes')
@section('breadcrumb')
    <li class="breadcrumb-item active">Amendes</li>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}"></div>
            <div class="col-md-2">
                <select name="statut_paiement" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="impaye" {{ request('statut_paiement') == 'impaye' ? 'selected' : '' }}>Impayé</option>
                    <option value="partiel" {{ request('statut_paiement') == 'partiel' ? 'selected' : '' }}>Partiel</option>
                    <option value="paye" {{ request('statut_paiement') == 'paye' ? 'selected' : '' }}>Payé</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}"></div>
            <div class="col-md-2"><input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}"></div>
            <div class="col-md-1"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button></div>
            <div class="col-md-1"><a href="{{ route('amendes.index') }}" class="btn btn-secondary btn-sm w-100"><i class="fas fa-times"></i></a></div>
        </form>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-2">
                <h5 class="fw-bold text-success mb-0">{{ number_format($stats['total_paye'], 0, ',', ' ') }} FCFA</h5>
                <small class="text-muted">Total perçu</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-2">
                <h5 class="fw-bold text-danger mb-0">{{ number_format($stats['total_impaye'], 0, ',', ' ') }} FCFA</h5>
                <small class="text-muted">Total impayé</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-2">
                <h5 class="fw-bold text-primary mb-0">{{ $stats['nb_amendes'] }}</h5>
                <small class="text-muted">Total amendes</small>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-file-invoice-dollar me-2"></i>Amendes ({{ $amendes->total() }})</span>
        <a href="{{ route('amendes.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouvelle Amende</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>N° Amende</th><th>Contrevenant</th><th>Montant</th><th>Payé</th><th>Date</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($amendes as $amende)
                    <tr>
                        <td><a href="{{ route('amendes.show', $amende) }}" class="fw-bold text-decoration-none">{{ $amende->numero_amende }}</a></td>
                        <td>{{ $amende->nom_contrevenant ?? ($amende->infraction->contrevenant_nom_complet ?? '-') }}</td>
                        <td>{{ number_format($amende->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($amende->montant_paye, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $amende->date_amende->format('d/m/Y') }}</td>
                        <td>
                            @php $c = ['paye'=>'success','partiel'=>'warning','impaye'=>'danger']; @endphp
                            <span class="badge bg-{{ $c[$amende->statut_paiement] ?? 'secondary' }}">{{ ucfirst($amende->statut_paiement) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('amendes.show', $amende) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('amendes.edit', $amende) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                            @if($amende->statut_paiement != 'paye')
                            <button class="btn btn-sm btn-success btn-action" data-bs-toggle="modal" data-bs-target="#paiementModal{{ $amende->id }}"><i class="fas fa-money-bill"></i></button>
                            @endif
                            <form action="{{ route('amendes.destroy', $amende) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <!-- Modal paiement -->
                    @if($amende->statut_paiement != 'paye')
                    <div class="modal fade" id="paiementModal{{ $amende->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Enregistrer un paiement</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('amendes.paiement', $amende) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <p>Amende: <strong>{{ $amende->numero_amende }}</strong> - Reste: <strong>{{ number_format($amende->montant - $amende->montant_paye, 0, ',', ' ') }} FCFA</strong></p>
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
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune amende trouvée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($amendes->hasPages())
    <div class="card-footer">{{ $amendes->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
