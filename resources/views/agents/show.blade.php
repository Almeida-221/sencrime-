@extends('layouts.app')
@section('title', 'Détail Agent')
@section('page-title', 'Fiche Agent')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('agents.index') }}">Agents</a></li>
    <li class="breadcrumb-item active">{{ $agent->nom_complet }}</li>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                    <i class="fas fa-user-shield fa-2x text-white"></i>
                </div>
                <h5 class="fw-bold">{{ $agent->nom_complet }}</h5>
                <p class="text-muted mb-1">{{ $agent->grade }}</p>
                <p class="text-muted small mb-2">{{ $agent->matricule }}</p>
                @php $colors = ['actif'=>'success','inactif'=>'secondary','suspendu'=>'warning','retraite'=>'info']; @endphp
                <span class="badge bg-{{ $colors[$agent->statut] ?? 'secondary' }} mb-3">{{ ucfirst($agent->statut) }}</span>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('agents.edit', $agent) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Informations</div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr><th>Genre:</th><td>{{ $agent->genre == 'M' ? 'Masculin' : 'Féminin' }}</td></tr>
                    <tr><th>Naissance:</th><td>{{ $agent->date_naissance ? $agent->date_naissance->format('d/m/Y') : '-' }}</td></tr>
                    <tr><th>Lieu:</th><td>{{ $agent->lieu_naissance ?? '-' }}</td></tr>
                    <tr><th>Nationalité:</th><td>{{ $agent->nationalite ?? '-' }}</td></tr>
                    <tr><th>Téléphone:</th><td>{{ $agent->telephone ?? '-' }}</td></tr>
                    <tr><th>Email:</th><td>{{ $agent->email ?? '-' }}</td></tr>
                    <tr><th>Adresse:</th><td>{{ $agent->adresse ?? '-' }}</td></tr>
                    <tr><th>Recrutement:</th><td>{{ $agent->date_recrutement ? $agent->date_recrutement->format('d/m/Y') : '-' }}</td></tr>
                    <tr><th>Fonction:</th><td>{{ $agent->fonction ?? '-' }}</td></tr>
                    <tr><th>Service:</th><td>{{ $agent->service->nom ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <!-- Mouvement -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-exchange-alt me-2"></i>Enregistrer un Mouvement</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#mouvementForm">
                    <i class="fas fa-plus me-1"></i>Nouveau
                </button>
            </div>
            <div class="collapse" id="mouvementForm">
                <div class="card-body">
                    <form action="{{ route('agents.mouvement', $agent) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Type de mouvement</label>
                                <select name="type_mouvement" class="form-select" required>
                                    <option value="affectation">Affectation</option>
                                    <option value="mutation">Mutation</option>
                                    <option value="detachement">Détachement</option>
                                    <option value="retour">Retour</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Service destination</label>
                                <select name="service_destination_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach(\App\Models\Service::where('actif',true)->orderBy('nom')->get() as $s)
                                        <option value="{{ $s->id }}">{{ $s->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Date</label>
                                <input type="date" name="date_mouvement" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Motif</label>
                                <input type="text" name="motif" class="form-control" placeholder="Motif du mouvement">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm mt-3"><i class="fas fa-save me-1"></i>Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Historique des mouvements -->
        <div class="card">
            <div class="card-header"><i class="fas fa-history me-2"></i>Historique des mouvements</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Type</th><th>De</th><th>Vers</th><th>Motif</th></tr>
                        </thead>
                        <tbody>
                            @forelse($agent->mouvements as $mouvement)
                            <tr>
                                <td>{{ $mouvement->date_mouvement->format('d/m/Y') }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($mouvement->type_mouvement) }}</span></td>
                                <td><small>{{ $mouvement->serviceOrigine->nom ?? '-' }}</small></td>
                                <td><small>{{ $mouvement->serviceDestination->nom ?? '-' }}</small></td>
                                <td><small>{{ $mouvement->motif ?? '-' }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Aucun mouvement enregistré</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
