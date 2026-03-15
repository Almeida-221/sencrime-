@extends('layouts.app')

@section('title', 'Rapport Statistique')

@push('styles')
<style>
    .rapport-header { background: linear-gradient(135deg,#1a3a5c,#2d6a9f); color:#fff; border-radius:12px; padding:25px 30px; margin-bottom:24px; }
    .kpi-card { background:#fff; border-radius:10px; padding:18px; box-shadow:0 2px 12px rgba(0,0,0,.07); text-align:center; }
    .kpi-card .val { font-size:2rem; font-weight:800; line-height:1; }
    .kpi-card .lbl { font-size:.78rem; color:#6c757d; text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }
    .chart-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,.07); margin-bottom:20px; }
    .chart-card h6 { font-weight:700; color:#1a3a5c; margin-bottom:16px; font-size:.9rem; text-transform:uppercase; letter-spacing:.5px; }
    .periode-btn { border:2px solid #e0e0e0; background:#fff; border-radius:8px; padding:6px 16px; font-size:.85rem; cursor:pointer; transition:all .2s; }
    .periode-btn.active, .periode-btn:hover { border-color:#1a3a5c; background:#1a3a5c; color:#fff; }
    .filter-bar { background:#fff; border-radius:12px; padding:16px 20px; box-shadow:0 2px 12px rgba(0,0,0,.07); margin-bottom:20px; }
    @media print { .no-print { display:none!important; } body { background:#fff; } }
</style>
@endpush

@section('content')

{{-- ── Barre de filtres ── --}}
<div class="filter-bar no-print">
    <form method="GET" action="{{ route('rapports.index') }}" class="row g-2 align-items-end" id="filterForm">
        <div class="col-auto">
            <label class="form-label small fw-bold mb-1">Période</label>
            <div class="d-flex gap-1 flex-wrap">
                @foreach(['jour'=>'Jour','semaine'=>'Semaine','mois'=>'Mois','annee'=>'Année','custom'=>'Personnalisé'] as $val=>$lbl)
                <button type="button" class="periode-btn {{ $periode==$val?'active':'' }}"
                    onclick="setPeriode('{{ $val }}')">{{ $lbl }}</button>
                @endforeach
            </div>
            <input type="hidden" name="periode" id="periodeInput" value="{{ $periode }}">
        </div>

        <div class="col-auto" id="customDates" style="{{ $periode=='custom'?'':'display:none' }}">
            <label class="form-label small fw-bold mb-1">Du</label>
            <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut', $dateDebut->format('Y-m-d')) }}">
        </div>
        <div class="col-auto" id="customDates2" style="{{ $periode=='custom'?'':'display:none' }}">
            <label class="form-label small fw-bold mb-1">Au</label>
            <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin', $dateFin->format('Y-m-d')) }}">
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter me-1"></i> Filtrer
            </button>
        </div>

        <div class="col-auto ms-auto">
            <a href="{{ route('rapports.pdf', request()->all()) }}" target="_blank" class="btn btn-danger btn-sm">
                <i class="fas fa-file-pdf me-1"></i> Télécharger PDF
            </a>
        </div>
    </form>
</div>

{{-- ── En-tête rapport ── --}}
<div class="rapport-header">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-chart-bar me-2"></i>
                Rapport Statistique — {{ $isAdmin ? 'National' : 'Région '.$region }}
            </h4>
            <p class="mb-0 opacity-75">{{ $periodeLabel }}</p>
            <p class="mb-0 opacity-60 small">Généré le {{ now()->format('d/m/Y à H:i') }} par {{ $user->name }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-white text-dark px-3 py-2">
                <i class="fas fa-shield-alt me-1 text-primary"></i> République du Sénégal
            </span>
            <span class="badge bg-white text-dark px-3 py-2">
                <i class="fas fa-lock me-1 text-danger"></i> Confidentiel
            </span>
        </div>
    </div>
</div>

{{-- ── KPIs ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card"><div class="val text-warning">{{ $stats['total_infractions'] }}</div><div class="lbl">Infractions</div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card"><div class="val text-danger">{{ $stats['total_accidents'] }}</div><div class="lbl">Accidents</div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card"><div class="val text-primary">{{ $stats['total_immigrations'] }}</div><div class="lbl">Cas immigration</div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card"><div class="val text-dark">{{ $stats['total_migrants'] }}</div><div class="lbl">Migrants interceptés</div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card"><div class="val" style="color:#e63946;">{{ $stats['total_morts'] }}</div><div class="lbl">Décès (accidents)</div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card"><div class="val text-warning">{{ $stats['total_blesses'] }}</div><div class="lbl">Blessés</div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card"><div class="val text-danger">{{ $stats['accidents_mortels'] }}</div><div class="lbl">Accidents mortels</div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card"><div class="val text-secondary">{{ $stats['accidents_graves'] }}</div><div class="lbl">Accidents graves</div></div>
    </div>
</div>

{{-- ── Graphes ── --}}
<div class="row g-4">

    {{-- Évolution mensuelle --}}
    <div class="col-12">
        <div class="chart-card">
            <h6><i class="fas fa-chart-line me-2 text-primary"></i>Évolution sur la période</h6>
            <canvas id="chartEvolution" height="80"></canvas>
        </div>
    </div>

    {{-- Infractions par type --}}
    <div class="col-md-6">
        <div class="chart-card">
            <h6><i class="fas fa-gavel me-2 text-warning"></i>Infractions par type</h6>
            @if($infractionsParType->count())
            <canvas id="chartInfractionType" height="220"></canvas>
            @else
            <p class="text-muted text-center py-4">Aucune donnée sur cette période</p>
            @endif
        </div>
    </div>

    {{-- Accidents par gravité --}}
    <div class="col-md-6">
        <div class="chart-card">
            <h6><i class="fas fa-car-crash me-2 text-danger"></i>Accidents par gravité</h6>
            <canvas id="chartAccidentGravite" height="220"></canvas>
        </div>
    </div>

    {{-- Top localités infractions --}}
    <div class="col-md-6">
        <div class="chart-card">
            <h6><i class="fas fa-map-marker-alt me-2 text-info"></i>Top localités — Infractions</h6>
            @if($topLocalitesInfractions->count())
            <canvas id="chartLocalitesInfractions" height="200"></canvas>
            @else
            <p class="text-muted text-center py-4">Aucune donnée</p>
            @endif
        </div>
    </div>

    {{-- Top localités accidents --}}
    <div class="col-md-6">
        <div class="chart-card">
            <h6><i class="fas fa-map-marker-alt me-2 text-danger"></i>Top localités — Accidents</h6>
            @if($topLocalitesAccidents->count())
            <canvas id="chartLocalitesAccidents" height="200"></canvas>
            @else
            <p class="text-muted text-center py-4">Aucune donnée</p>
            @endif
        </div>
    </div>

    {{-- Immigration par pays --}}
    @if($immigrationsParPays->count())
    <div class="col-md-6">
        <div class="chart-card">
            <h6><i class="fas fa-globe-africa me-2 text-primary"></i>Immigration — Pays d'origine</h6>
            <canvas id="chartImmigration" height="220"></canvas>
        </div>
    </div>
    @endif

    {{-- Par région (admin national) --}}
    @if($isAdmin && $infractionsParRegion && $infractionsParRegion->count())
    <div class="col-md-6">
        <div class="chart-card">
            <h6><i class="fas fa-map me-2 text-success"></i>Infractions par région (national)</h6>
            <canvas id="chartRegions" height="220"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-card">
            <h6><i class="fas fa-map me-2 text-danger"></i>Accidents par région (national)</h6>
            <canvas id="chartRegionsAcc" height="220"></canvas>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const COLORS = ['#1a3a5c','#e63946','#f4a261','#2d6a4f','#0077b6','#7b2d8b','#e9c46a','#264653','#2a9d8f','#e76f51'];

// Évolution
new Chart(document.getElementById('chartEvolution'), {
    type: 'line',
    data: {
        labels: @json($evolutionParMois['labels']),
        datasets: [
            { label:'Infractions', data:@json($evolutionParMois['infractions']), borderColor:'#f4a261', backgroundColor:'rgba(244,162,97,.15)', tension:.4, fill:true },
            { label:'Accidents',   data:@json($evolutionParMois['accidents']),   borderColor:'#e63946', backgroundColor:'rgba(230,57,70,.1)',    tension:.4, fill:true },
            { label:'Immigration', data:@json($evolutionParMois['migrations']),  borderColor:'#0077b6', backgroundColor:'rgba(0,119,182,.1)',    tension:.4, fill:true },
        ]
    },
    options: { responsive:true, plugins:{ legend:{ position:'top' } }, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
});

@if($infractionsParType->count())
// Infractions par type
new Chart(document.getElementById('chartInfractionType'), {
    type: 'bar',
    data: {
        labels: @json($infractionsParType->pluck('nom')),
        datasets: [{ label:'Nombre', data:@json($infractionsParType->pluck('total')), backgroundColor:COLORS, borderRadius:6 }]
    },
    options: { indexAxis:'y', responsive:true, plugins:{ legend:{ display:false } }, scales:{ x:{ beginAtZero:true } } }
});
@endif

// Accidents par gravité
new Chart(document.getElementById('chartAccidentGravite'), {
    type: 'doughnut',
    data: {
        labels: ['Léger','Grave','Mortel'],
        datasets: [{ data:[
            {{ $accidentsParGravite['leger'] ?? 0 }},
            {{ $accidentsParGravite['grave'] ?? 0 }},
            {{ $accidentsParGravite['mortel'] ?? 0 }}
        ], backgroundColor:['#40916c','#f4a261','#e63946'], borderWidth:2 }]
    },
    options: { responsive:true, plugins:{ legend:{ position:'bottom' } } }
});

@if($topLocalitesInfractions->count())
new Chart(document.getElementById('chartLocalitesInfractions'), {
    type: 'bar',
    data: {
        labels: @json($topLocalitesInfractions->pluck('localite')),
        datasets: [{ label:'Infractions', data:@json($topLocalitesInfractions->pluck('total')), backgroundColor:'#f4a261', borderRadius:6 }]
    },
    options: { responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
});
@endif

@if($topLocalitesAccidents->count())
new Chart(document.getElementById('chartLocalitesAccidents'), {
    type: 'bar',
    data: {
        labels: @json($topLocalitesAccidents->pluck('localite')),
        datasets: [{ label:'Accidents', data:@json($topLocalitesAccidents->pluck('total')), backgroundColor:'#e63946', borderRadius:6 }]
    },
    options: { responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
});
@endif

@if($immigrationsParPays->count())
new Chart(document.getElementById('chartImmigration'), {
    type: 'bar',
    data: {
        labels: @json($immigrationsParPays->pluck('pays_origine')),
        datasets: [{ label:'Personnes', data:@json($immigrationsParPays->pluck('total_personnes')), backgroundColor:'#0077b6', borderRadius:6 }]
    },
    options: { indexAxis:'y', responsive:true, plugins:{ legend:{ display:false } }, scales:{ x:{ beginAtZero:true } } }
});
@endif

@if($isAdmin && isset($infractionsParRegion) && $infractionsParRegion->count())
new Chart(document.getElementById('chartRegions'), {
    type: 'bar',
    data: {
        labels: @json($infractionsParRegion->pluck('region')),
        datasets: [{ label:'Infractions', data:@json($infractionsParRegion->pluck('total')), backgroundColor:COLORS, borderRadius:6 }]
    },
    options: { responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
});
new Chart(document.getElementById('chartRegionsAcc'), {
    type: 'bar',
    data: {
        labels: @json($accidentsParRegion->pluck('region')),
        datasets: [{ label:'Accidents', data:@json($accidentsParRegion->pluck('total')), backgroundColor:COLORS, borderRadius:6 }]
    },
    options: { responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
});
@endif

function setPeriode(val) {
    document.getElementById('periodeInput').value = val;
    const show = val === 'custom';
    document.getElementById('customDates').style.display  = show ? '' : 'none';
    document.getElementById('customDates2').style.display = show ? '' : 'none';
    document.querySelectorAll('.periode-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    if (val !== 'custom') document.getElementById('filterForm').submit();
}
</script>
@endpush
@endsection
