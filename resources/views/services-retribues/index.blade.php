@extends('layouts.app')
@section('title', 'Services Rétribués')
@section('page-title', 'Gestion des Services Rétribués')
@section('breadcrumb')
    <li class="breadcrumb-item active">Services Rétribués</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-handshake me-2"></i>Missions Rétribuées ({{ $servicesRetribues->total() }})</span>
        <a href="{{ route('services-retribues.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouvelle Mission</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>N° Mission</th><th>Type</th><th>Date</th><th>Montant</th><th>Payé</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($servicesRetribues as $sr)
                    <tr>
                        <td><a href="{{ route('services-retribues.show', $sr) }}" class="fw-bold text-decoration-none">{{ $sr->numero_mission }}</a></td>
                        <td>{{ $sr->type_service }}</td>
                        <td>{{ $sr->date_debut->format('d/m/Y') }}</td>
                        <td>{{ number_format($sr->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($sr->montant_paye, 0, ',', ' ') }} FCFA</td>
                        <td>
                            @php $c = ['paye'=>'success','partiel'=>'warning','impaye'=>'danger']; @endphp
                            <span class="badge bg-{{ $c[$sr->statut_paiement] ?? 'secondary' }}">{{ ucfirst($sr->statut_paiement) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('services-retribues.show', $sr) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('services-retribues.edit', $sr) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('services-retribues.destroy', $sr) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune mission enregistrée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($servicesRetribues->hasPages())
    <div class="card-footer">{{ $servicesRetribues->links() }}</div>
    @endif
</div>
@endsection
