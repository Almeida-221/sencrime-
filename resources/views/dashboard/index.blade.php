@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('breadcrumb')
    <li class="breadcrumb-item active">Accueil</li>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ── Bandeau de contexte (Admin Région / Agent) ─────────────────── --}}
@if(!$isAdmin)
<div class="alert mb-4 py-2" style="background:rgba(26,58,92,0.08);border:1px solid rgba(26,58,92,0.2);border-radius:10px;">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <i class="fas fa-filter text-primary fs-5"></i>
        <div>
            <strong class="text-primary">Vue filtrée</strong>
            @if($scopeService)
                — données limitées à votre <strong>service</strong>
                @php $svc = \App\Models\Service::find($scopeService); @endphp
                : <span class="badge bg-primary">{{ $svc->nom ?? "ID $scopeService" }}</span>
            @elseif($scopeRegion)
                — données limitées à la région
                <span class="badge bg-primary">{{ $scopeRegion }}</span>
            @endif
        </div>
        <a href="{{ route('surveillance.index') }}" class="btn btn-sm btn-outline-primary ms-auto">
            <i class="fas fa-map-marked-alt me-1"></i>Voir la carte
        </a>
    </div>
</div>
@endif

{{-- ── Statistiques principales ─────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    {{-- Agents (admin + admin_region) --}}
    @if(isset($stats['total_agents']))
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card bg-primary-custom">
            <div class="icon"><i class="fas fa-user-shield"></i></div>
            <h3>{{ $stats['total_agents'] }}</h3>
            <p>Agents actifs</p>
        </div>
    </div>
    @endif

    {{-- Services (admin + admin_region) --}}
    @if(isset($stats['total_services']))
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card bg-info-custom">
            <div class="icon"><i class="fas fa-building"></i></div>
            <h3>{{ $stats['total_services'] }}</h3>
            <p>Services</p>
        </div>
    </div>
    @endif

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card bg-danger-custom">
            <div class="icon"><i class="fas fa-gavel"></i></div>
            <h3>{{ $stats['total_infractions'] }}</h3>
            <p>Infractions</p>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card bg-warning-custom">
            <div class="icon"><i class="fas fa-car-crash"></i></div>
            <h3>{{ $stats['total_accidents'] }}</h3>
            <p>Accidents</p>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card bg-purple-custom">
            <div class="icon"><i class="fas fa-passport"></i></div>
            <h3>{{ $stats['total_immigrations'] }}</h3>
            <p>Cas immigration</p>
        </div>
    </div>

    {{-- Alertes (tous rôles) --}}
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card" style="background:linear-gradient(135deg,#e63946,#c1121f);color:#fff;">
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h3>{{ $stats['infractions_ouvertes'] }}</h3>
            <p>Infractions ouvertes</p>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card" style="background:linear-gradient(135deg,#f4a261,#e76f51);color:#fff;">
            <div class="icon"><i class="fas fa-car-burst"></i></div>
            <h3>{{ $stats['accidents_graves'] }}</h3>
            <p>Accidents graves/mortels</p>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card" style="background:linear-gradient(135deg,#457b9d,#1d3557);color:#fff;">
            <div class="icon"><i class="fas fa-user-secret"></i></div>
            <h3>{{ $stats['immigrations_actives'] }}</h3>
            <p>Immigrations en cours</p>
        </div>
    </div>

    {{-- Amendes (admin uniquement) --}}
    @if($isAdmin)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card bg-success-custom">
            <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <h3>{{ $stats['total_amendes'] }}</h3>
            <p>Amendes</p>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card" style="background:linear-gradient(135deg,#2d6a4f,#1b4332);color:#fff;">
            <div class="icon"><i class="fas fa-handshake"></i></div>
            <h3>{{ $stats['total_services_retribues'] }}</h3>
            <p>Missions rétribuées</p>
        </div>
    </div>
    @endif
</div>

{{-- ── Finances (admin seulement) ──────────────────────────────────── --}}
@if($isAdmin)
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                <h4 class="fw-bold text-success">{{ number_format($stats['montant_amendes_paye'], 0, ',', ' ') }} FCFA</h4>
                <p class="text-muted mb-0">Amendes perçues</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-circle fa-2x text-danger mb-2"></i>
                <h4 class="fw-bold text-danger">{{ $stats['amendes_impayees'] }}</h4>
                <p class="text-muted mb-0">Amendes impayées</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-chart-pie fa-2x text-primary mb-2"></i>
                <h4 class="fw-bold text-primary">{{ number_format($stats['montant_amendes_total'], 0, ',', ' ') }} FCFA</h4>
                <p class="text-muted mb-0">Montant total des amendes</p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Graphiques ───────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="{{ $isAdmin ? 'col-lg-8' : 'col-lg-12' }}">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-line me-2 text-primary"></i>Évolution des infractions et accidents (12 mois)
                    @if(!$isAdmin && $scopeRegion)
                        <small class="text-muted ms-1">— {{ $scopeRegion }}</small>
                    @endif
                </span>
            </div>
            <div class="card-body">
                <canvas id="evolutionChart" height="{{ $isAdmin ? 100 : 80 }}"></canvas>
            </div>
        </div>
    </div>
    @if($isAdmin)
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2 text-warning"></i>Amendes par statut
            </div>
            <div class="card-body">
                <canvas id="amendesChart" height="200"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2 text-danger"></i>Top 10 types d'infractions
            </div>
            <div class="card-body">
                <canvas id="typesChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-map-marker-alt me-2 text-info"></i>Infractions par localité (Top 10)
            </div>
            <div class="card-body">
                <canvas id="localiteChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── Dernières activités ──────────────────────────────────────────── --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-gavel me-2 text-danger"></i>Dernières infractions</span>
                <a href="{{ route('infractions.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N° Dossier</th>
                                <th>Type</th>
                                <th>Localité</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dernieresInfractions as $inf)
                            <tr>
                                <td><a href="{{ route('infractions.show', $inf) }}" class="text-decoration-none fw-bold">{{ $inf->numero_dossier }}</a></td>
                                <td><small>{{ $inf->typeInfraction->nom ?? '-' }}</small></td>
                                <td><small>{{ $inf->localite }}</small></td>
                                <td>
                                    @php $colors = ['ouvert'=>'primary','en_cours'=>'warning','ferme'=>'success','classe'=>'secondary']; @endphp
                                    <span class="badge bg-{{ $colors[$inf->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$inf->statut)) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Aucune infraction enregistrée</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-car-crash me-2 text-warning"></i>Derniers accidents</span>
                <a href="{{ route('accidents.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N° Rapport</th>
                                <th>Localité</th>
                                <th>Gravité</th>
                                <th>Victimes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($derniersAccidents as $acc)
                            <tr>
                                <td><a href="{{ route('accidents.show', $acc) }}" class="text-decoration-none fw-bold">{{ $acc->numero_rapport }}</a></td>
                                <td><small>{{ $acc->localite }}</small></td>
                                <td>
                                    @php $colors = ['leger'=>'success','grave'=>'warning','mortel'=>'danger']; @endphp
                                    <span class="badge bg-{{ $colors[$acc->gravite] ?? 'secondary' }}">{{ ucfirst($acc->gravite) }}</span>
                                </td>
                                <td>{{ $acc->nombre_victimes }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Aucun accident enregistré</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const moisLabels = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

const infractionsData = new Array(12).fill(0);
@foreach($infractionsParMois as $item)
    infractionsData[{{ $item->mois - 1 }}] = {{ $item->total }};
@endforeach

const accidentsData = new Array(12).fill(0);
@foreach($accidentsParMois as $item)
    accidentsData[{{ $item->mois - 1 }}] = {{ $item->total }};
@endforeach

const immigrationsData = new Array(12).fill(0);
@foreach($immigrationsParMois as $item)
    immigrationsData[{{ $item->mois - 1 }}] = {{ $item->total }};
@endforeach

new Chart(document.getElementById('evolutionChart'), {
    type: 'line',
    data: {
        labels: moisLabels,
        datasets: [
            { label: 'Infractions', data: infractionsData, borderColor: '#e63946', backgroundColor: 'rgba(230,57,70,0.1)', tension: 0.4, fill: true },
            { label: 'Accidents',   data: accidentsData,   borderColor: '#f4a261', backgroundColor: 'rgba(244,162,97,0.1)', tension: 0.4, fill: true },
            { label: 'Immigration', data: immigrationsData, borderColor: '#457b9d', backgroundColor: 'rgba(69,123,157,0.1)', tension: 0.4, fill: true },
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
});

@if($isAdmin)
const amendesLabels = [], amendesValues = [], amendesColors = [];
@foreach($amendesParStatut as $item)
    amendesLabels.push('{{ ucfirst($item->statut_paiement) }}');
    amendesValues.push({{ $item->total }});
    amendesColors.push('{{ $item->statut_paiement == "paye" ? "#40916c" : ($item->statut_paiement == "partiel" ? "#f4a261" : "#e63946") }}');
@endforeach
if (amendesValues.length > 0) {
    new Chart(document.getElementById('amendesChart'), {
        type: 'doughnut',
        data: { labels: amendesLabels, datasets: [{ data: amendesValues, backgroundColor: amendesColors }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}
@endif

const typesLabels = [], typesValues = [];
@foreach($infractionsParType as $item)
    typesLabels.push('{{ addslashes($item->nom) }}');
    typesValues.push({{ $item->infractions_count }});
@endforeach
if (typesValues.length > 0) {
    new Chart(document.getElementById('typesChart'), {
        type: 'bar',
        data: { labels: typesLabels, datasets: [{ label: "Infractions", data: typesValues, backgroundColor: 'rgba(230,57,70,0.7)', borderColor: '#e63946', borderWidth: 1 }] },
        options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
    });
}

const localiteLabels = [], localiteValues = [];
@foreach($infractionsParLocalite as $item)
    localiteLabels.push('{{ addslashes($item->localite) }}');
    localiteValues.push({{ $item->total }});
@endforeach
if (localiteValues.length > 0) {
    new Chart(document.getElementById('localiteChart'), {
        type: 'bar',
        data: { labels: localiteLabels, datasets: [{ label: "Infractions", data: localiteValues, backgroundColor: 'rgba(0,119,182,0.7)', borderColor: '#0077b6', borderWidth: 1 }] },
        options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
    });
}
</script>
@endpush
