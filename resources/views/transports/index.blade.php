@extends('layouts.app')
@section('title', 'Transports')
@section('page-title', 'Historique des Transports')
@section('breadcrumb')
    <li class="breadcrumb-item active">Transports</li>
@endsection

@section('content')

{{-- ── Cartes statistiques ─────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-2">
        <div class="card text-center border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-dark">{{ $stats['total'] }}</div>
                <div class="small text-muted">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('transports.index', ['statut' => 'en_attente']) }}" class="text-decoration-none">
            <div class="card text-center border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d !important;">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-secondary">{{ $stats['en_attente'] }}</div>
                    <div class="small text-muted">En attente</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('transports.index', ['statut' => 'acceptee']) }}" class="text-decoration-none">
            <div class="card text-center border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-primary">{{ $stats['acceptee'] }}</div>
                    <div class="small text-muted">Acceptées</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('transports.index', ['statut' => 'en_cours']) }}" class="text-decoration-none">
            <div class="card text-center border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-warning">{{ $stats['en_cours'] }}</div>
                    <div class="small text-muted">En cours</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('transports.index', ['statut' => 'terminee']) }}" class="text-decoration-none">
            <div class="card text-center border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-success">{{ $stats['terminee'] }}</div>
                    <div class="small text-muted">Terminées</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('transports.index', ['statut' => 'annulee']) }}" class="text-decoration-none">
            <div class="card text-center border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-danger">{{ $stats['annulee'] }}</div>
                    <div class="small text-muted">Annulées</div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- ── Filtres ──────────────────────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Transporteur, agent, localité…" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="acceptee"   {{ request('statut') == 'acceptee'   ? 'selected' : '' }}>Acceptée</option>
                    <option value="en_cours"   {{ request('statut') == 'en_cours'   ? 'selected' : '' }}>En cours</option>
                    <option value="terminee"   {{ request('statut') == 'terminee'   ? 'selected' : '' }}>Terminée</option>
                    <option value="annulee"    {{ request('statut') == 'annulee'    ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('transports.index') }}" class="btn btn-secondary btn-sm w-100"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- ── Tableau ───────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-truck-medical me-2"></i>Demandes de transport ({{ $transports->total() }})</span>
        @if(request('statut'))
            <span class="badge bg-secondary fs-6">Filtre : {{ request('statut') }}</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Accident / Localité</th>
                        <th>Agent demandeur</th>
                        <th>Transporteur</th>
                        <th>Statut</th>
                        <th>Demandé le</th>
                        <th>Accepté le</th>
                        <th>Terminé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transports as $t)
                    @php
                        $statutColors = [
                            'en_attente' => 'secondary',
                            'acceptee'   => 'primary',
                            'en_cours'   => 'warning',
                            'terminee'   => 'success',
                            'annulee'    => 'danger',
                        ];
                        $statutLabels = [
                            'en_attente' => 'En attente',
                            'acceptee'   => 'Acceptée',
                            'en_cours'   => 'En cours',
                            'terminee'   => 'Terminée',
                            'annulee'    => 'Annulée',
                        ];
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('transports.show', $t) }}" class="fw-bold text-decoration-none">
                                #{{ $t->id }}
                            </a>
                        </td>
                        <td>
                            @if($t->accident)
                                <div class="fw-semibold">{{ $t->accident->localite }}</div>
                                <small class="text-muted">{{ $t->accident->numero_rapport ?? 'ACC-'.$t->accident_id }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $t->demandeur?->name ?? '—' }}</td>
                        <td>
                            @if($t->transporteur)
                                <i class="fas fa-truck text-primary me-1"></i>{{ $t->transporteur->name }}
                            @else
                                <span class="text-muted fst-italic">Non assigné</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $statutColors[$t->statut] ?? 'secondary' }}">
                                {{ $statutLabels[$t->statut] ?? $t->statut }}
                            </span>
                            {{-- Indicateur live si en cours --}}
                            @if(in_array($t->statut, ['acceptee', 'en_cours']))
                                <span class="badge bg-danger ms-1 live-blink">
                                    <i class="fas fa-circle" style="font-size:7px;"></i> Live
                                </span>
                            @endif
                        </td>
                        <td><small>{{ $t->created_at->format('d/m/Y H:i') }}</small></td>
                        <td>
                            @if($t->acceptee_at)
                                <small>{{ $t->acceptee_at->format('d/m/Y H:i') }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($t->terminee_at)
                                <small>{{ $t->terminee_at->format('d/m/Y H:i') }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('transports.show', $t) }}"
                               class="btn btn-sm btn-info btn-action"
                               title="{{ in_array($t->statut, ['acceptee','en_cours']) ? 'Surveiller en live' : 'Voir détail' }}">
                                @if(in_array($t->statut, ['acceptee', 'en_cours']))
                                    <i class="fas fa-satellite-dish"></i>
                                @else
                                    <i class="fas fa-eye"></i>
                                @endif
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                            Aucune demande de transport trouvée
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transports->hasPages())
    <div class="card-footer">{{ $transports->appends(request()->query())->links() }}</div>
    @endif
</div>

@endsection

@push('styles')
<style>
    .live-blink { animation: blink 1.2s infinite; }
    @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
    .btn-action { width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
</style>
@endpush
