@extends('layouts.app')
@section('title', 'Surveillance')
@section('page-title', 'Carte de Surveillance')
@section('breadcrumb')
    <li class="breadcrumb-item active">Surveillance</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: calc(100vh - 220px);
        min-height: 500px;
        border-radius: 0 0 12px 12px;
        z-index: 1;
    }

    /* Panneau de filtres */
    .filter-card { border-radius: 12px 12px 0 0 !important; margin-bottom: 0 !important; }

    /* Légende */
    .legend-box {
        background: #fff;
        border-radius: 10px;
        padding: 12px 16px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.12);
        font-size: 0.82rem;
    }
    .legend-dot {
        width: 14px; height: 14px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
        border: 2px solid rgba(0,0,0,0.15);
        vertical-align: middle;
    }

    /* Compteurs */
    .counter-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #fff;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 0.82rem;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
    }

    /* Loader */
    #map-loader {
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        border-radius: 0 0 12px 12px;
    }

    /* Popup Leaflet custom */
    .leaflet-popup-content-wrapper {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .popup-header {
        font-weight: 700;
        font-size: 0.9rem;
        padding-bottom: 6px;
        margin-bottom: 6px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .popup-row { font-size: 0.8rem; color: #6b7280; margin: 3px 0; }
    .popup-row strong { color: #1f2937; }
    .popup-link {
        display: inline-block;
        margin-top: 8px;
        padding: 4px 12px;
        background: #1a3a5c;
        color: #fff !important;
        border-radius: 6px;
        font-size: 0.78rem;
        text-decoration: none !important;
    }
    .popup-link:hover { background: #0d2137; }

    /* Type checkboxes */
    .type-check { cursor: pointer; }
    .type-check input { accent-color: #1a3a5c; }
</style>
@endpush

@section('content')
<!-- Barre de filtres -->
<div class="card filter-card">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <!-- Types d'incidents -->
            <div class="col-md-3">
                <label class="form-label fw-bold mb-1" style="font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">Types d'incidents</label>
                <div class="d-flex gap-3">
                    <label class="type-check d-flex align-items-center gap-1">
                        <input type="checkbox" id="chk-accidents" checked>
                        <span style="font-size:0.82rem;"><i class="fas fa-car-crash text-danger me-1"></i>Accidents</span>
                    </label>
                    <label class="type-check d-flex align-items-center gap-1">
                        <input type="checkbox" id="chk-infractions" checked>
                        <span style="font-size:0.82rem;"><i class="fas fa-gavel text-warning me-1"></i>Infractions</span>
                    </label>
                    <label class="type-check d-flex align-items-center gap-1">
                        <input type="checkbox" id="chk-immigrations" checked>
                        <span style="font-size:0.82rem;"><i class="fas fa-passport text-primary me-1"></i>Immigration</span>
                    </label>
                </div>
            </div>

            <!-- Région -->
            <div class="col-md-2">
                <select id="filter-region" class="form-select form-select-sm" {{ !$isAdmin ? 'disabled' : '' }}>
                    @if($isAdmin)<option value="">Toutes les régions</option>@endif
                    @foreach($regions as $r)
                        <option value="{{ $r }}" {{ isset($scopeRegion) && $scopeRegion == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Commune / Localité -->
            <div class="col-md-2">
                <input type="text" id="filter-commune" class="form-control form-control-sm" placeholder="Commune / Localité">
            </div>

            <!-- Période -->
            <div class="col-md-2">
                <select id="filter-periode" class="form-select form-select-sm">
                    <option value="">Toute la période</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="week">7 derniers jours</option>
                    <option value="month">Ce mois</option>
                    <option value="year">Cette année</option>
                    <option value="custom">Personnalisé</option>
                </select>
            </div>

            <!-- Dates custom -->
            <div class="col-md-2" id="custom-dates" style="display:none;">
                <div class="d-flex gap-1">
                    <input type="date" id="filter-date-debut" class="form-control form-control-sm" placeholder="Début">
                    <input type="date" id="filter-date-fin" class="form-control form-control-sm" placeholder="Fin">
                </div>
            </div>

            <!-- Boutons -->
            <div class="col-md-1">
                <button id="btn-filtrer" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="col-auto">
                <button id="btn-reset" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Compteurs -->
            <div class="col-auto ms-auto d-flex gap-2 align-items-center flex-wrap" id="compteurs">
                <span class="counter-pill text-danger" id="cnt-accidents">
                    <i class="fas fa-car-crash"></i><span id="n-accidents">0</span> accidents
                </span>
                <span class="counter-pill text-warning" id="cnt-infractions">
                    <i class="fas fa-gavel"></i><span id="n-infractions">0</span> infractions
                </span>
                <span class="counter-pill text-primary" id="cnt-immigrations">
                    <i class="fas fa-passport"></i><span id="n-immigrations">0</span> immigrations
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Carte -->
<div class="card" style="border-radius:0 0 12px 12px;position:relative;">
    <div id="map-loader">
        <div class="text-center">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <div class="text-muted small">Chargement de la carte...</div>
        </div>
    </div>
    <div id="map"></div>

    <!-- Légende (overlay sur la carte) -->
    <div style="position:absolute;bottom:30px;left:20px;z-index:500;">
        <div class="legend-box">
            <div class="fw-bold mb-2" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:.5px;">Légende</div>
            <div class="mb-1">
                <span class="legend-dot" style="background:#e63946;"></span>Incident récent (&lt; 24h)
            </div>
            <div class="mb-1">
                <span class="legend-dot" style="background:#f4a261;"></span>Incident moyen (2–7 jours)
            </div>
            <div class="mb-2">
                <span class="legend-dot" style="background:#2d6a4f;"></span>Incident ancien (&gt; 7 jours)
            </div>
            <hr class="my-1">
            <div class="mb-1">
                <i class="fas fa-car-crash text-danger me-1"></i>Accident
            </div>
            <div class="mb-1">
                <i class="fas fa-gavel text-warning me-1"></i>Infraction
            </div>
            <div>
                <i class="fas fa-passport text-primary me-1"></i>Immigration
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Initialisation carte ──────────────────────────────────────────────
@php
    $regionCoords = \App\Http\Controllers\SurveillanceController::REGION_COORDS;
    $initCenter = isset($scopeRegion) && $scopeRegion && isset($regionCoords[$scopeRegion])
        ? $regionCoords[$scopeRegion]
        : [14.4974, -14.4524];
    $initZoom = isset($scopeRegion) && $scopeRegion ? 10 : 7;
@endphp
const map = L.map('map', {
    center: [{{ $initCenter[0] }}, {{ $initCenter[1] }}],
    zoom: {{ $initZoom }},
    minZoom: 6,
    maxZoom: 16,
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19,
}).addTo(map);

// Masquer le loader quand la carte est prête
map.on('load', () => document.getElementById('map-loader').style.display = 'none');
setTimeout(() => document.getElementById('map-loader').style.display = 'none', 1500);

// ── Icônes personnalisées ─────────────────────────────────────────────
const COLORS = { red: '#e63946', orange: '#f4a261', green: '#2d6a4f' };

function makeIcon(color, type) {
    const icons = {
        accident:    'fa-car-crash',
        infraction:  'fa-gavel',
        immigration: 'fa-passport',
    };
    const fa = icons[type] ?? 'fa-circle';
    const bg = COLORS[color] ?? COLORS.green;

    return L.divIcon({
        className: '',
        html: `
            <div style="
                width:34px;height:34px;
                border-radius:50% 50% 50% 0;
                transform:rotate(-45deg);
                background:${bg};
                border:3px solid rgba(255,255,255,0.9);
                box-shadow:0 3px 10px rgba(0,0,0,0.3);
                display:flex;align-items:center;justify-content:center;
            ">
                <i class="fas ${fa}" style="
                    transform:rotate(45deg);
                    color:#fff;font-size:13px;
                "></i>
            </div>`,
        iconSize: [34, 34],
        iconAnchor: [17, 34],
        popupAnchor: [0, -36],
    });
}

// ── Couches de marqueurs ──────────────────────────────────────────────
let layerGroup = L.layerGroup().addTo(map);

// ── Chargement des données ────────────────────────────────────────────
function loadMarkers() {
    document.getElementById('map-loader').style.display = 'flex';

    const types = [];
    if (document.getElementById('chk-accidents').checked)   types.push('accidents');
    if (document.getElementById('chk-infractions').checked) types.push('infractions');
    if (document.getElementById('chk-immigrations').checked) types.push('immigrations');

    const periode = document.getElementById('filter-periode').value;
    const params  = new URLSearchParams({
        region:     document.getElementById('filter-region').value,
        commune:    document.getElementById('filter-commune').value,
        periode:    periode,
        date_debut: periode === 'custom' ? document.getElementById('filter-date-debut').value : '',
        date_fin:   periode === 'custom' ? document.getElementById('filter-date-fin').value   : '',
        _token:     document.querySelector('meta[name="csrf-token"]').content,
    });
    types.forEach(t => params.append('types[]', t));

    fetch(`{{ route('surveillance.data') }}?${params}`)
        .then(r => r.json())
        .then(data => {
            layerGroup.clearLayers();

            let nAcc = 0, nInf = 0, nImm = 0;

            data.markers.forEach(m => {
                if (m.type === 'accident')    nAcc++;
                if (m.type === 'infraction')  nInf++;
                if (m.type === 'immigration') nImm++;

                const typeLabels = {
                    accident:    '<i class="fas fa-car-crash text-danger me-1"></i>Accident',
                    infraction:  '<i class="fas fa-gavel text-warning me-1"></i>Infraction',
                    immigration: '<i class="fas fa-passport text-primary me-1"></i>Immigration',
                };
                const statutColors = {
                    ouvert: 'primary', en_cours: 'warning', ferme: 'success',
                    rapatrie: 'info', clos: 'success', classe_sans_suite: 'secondary',
                };
                const sc = statutColors[m.statut] ?? 'secondary';

                const gpsInfo = m.has_gps
                    ? `<span class="badge bg-success" style="font-size:.65rem;">GPS</span>`
                    : `<span class="badge bg-secondary" style="font-size:.65rem;">Région</span>`;

                const victimsHtml = m.victimes !== null
                    ? `<div class="popup-row"><strong>Personnes :</strong> ${m.victimes}</div>`
                    : '';

                const popup = `
                    <div style="min-width:220px;">
                        <div class="popup-header">
                            ${typeLabels[m.type] ?? m.type}
                            ${gpsInfo}
                        </div>
                        <div class="popup-row"><strong>N° :</strong> ${m.titre}</div>
                        <div class="popup-row"><strong>Date :</strong> ${m.date}</div>
                        <div class="popup-row"><strong>Lieu :</strong> ${m.localite}${m.region ? ' — ' + m.region : ''}</div>
                        <div class="popup-row"><strong>Détail :</strong> ${m.description}</div>
                        ${victimsHtml}
                        <div class="popup-row">
                            <strong>Statut :</strong>
                            <span class="badge bg-${sc}" style="font-size:.65rem;">${m.statut.replace('_',' ')}</span>
                        </div>
                        <a href="${m.url}" class="popup-link" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i>Voir le dossier
                        </a>
                    </div>`;

                const marker = L.marker([m.lat, m.lng], { icon: makeIcon(m.color, m.type) });
                marker.bindPopup(popup, { maxWidth: 280 });
                layerGroup.addLayer(marker);
            });

            document.getElementById('n-accidents').textContent   = nAcc;
            document.getElementById('n-infractions').textContent  = nInf;
            document.getElementById('n-immigrations').textContent = nImm;

            document.getElementById('map-loader').style.display = 'none';
        })
        .catch(() => {
            document.getElementById('map-loader').style.display = 'none';
        });
}

// ── Période custom ────────────────────────────────────────────────────
document.getElementById('filter-periode').addEventListener('change', function () {
    document.getElementById('custom-dates').style.display =
        this.value === 'custom' ? 'block' : 'none';
});

// ── Événements ────────────────────────────────────────────────────────
document.getElementById('btn-filtrer').addEventListener('click', loadMarkers);

document.getElementById('btn-reset').addEventListener('click', () => {
    document.getElementById('filter-region').value   = '';
    document.getElementById('filter-commune').value  = '';
    document.getElementById('filter-periode').value  = '';
    document.getElementById('filter-date-debut').value = '';
    document.getElementById('filter-date-fin').value   = '';
    document.getElementById('custom-dates').style.display = 'none';
    document.getElementById('chk-accidents').checked    = true;
    document.getElementById('chk-infractions').checked  = true;
    document.getElementById('chk-immigrations').checked = true;
    loadMarkers();
});

// Recharger automatiquement si filtre modifié
['filter-region', 'filter-periode', 'chk-accidents', 'chk-infractions', 'chk-immigrations']
    .forEach(id => document.getElementById(id).addEventListener('change', loadMarkers));

// ── Chargement initial ────────────────────────────────────────────────
loadMarkers();
</script>
@endpush
