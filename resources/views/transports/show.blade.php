@extends('layouts.app')
@section('title', 'Transport #' . $transport->id)
@section('page-title', 'Surveillance du Transport #' . $transport->id)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transports.index') }}">Transports</a></li>
    <li class="breadcrumb-item active">Transport #{{ $transport->id }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-transport {
        height: 460px;
        border-radius: 0 0 12px 12px;
        z-index: 1;
    }
    .live-blink  { animation: blink 1.2s infinite; }
    @keyframes blink { 0%,100% { opacity:1; } 50% { opacity:0.25; } }

    /* Pulsation rouge sur destination */
    .pulse-icon {
        width: 36px; height: 36px;
        background: rgba(220,53,69,0.25);
        border-radius: 50%;
        border: 2px solid #dc3545;
        animation: pulse 1.8s infinite;
    }
    @keyframes pulse {
        0%   { transform: scale(1);   opacity: 1; }
        70%  { transform: scale(1.7); opacity: 0; }
        100% { transform: scale(1);   opacity: 0; }
    }

    .info-label { font-size: 0.78rem; color: #6c757d; margin-bottom: 2px; }
    .info-value { font-size: 0.93rem; font-weight: 600; }

    /* Pastille statut côté gauche de la carte */
    .map-overlay-top {
        position: absolute; top: 10px; left: 10px; z-index: 500;
        background: #fff;
        border-radius: 10px;
        padding: 8px 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.18);
        min-width: 200px;
    }
    .eta-pill {
        background: #1a3a5c;
        color: #fff;
        border-radius: 8px;
        padding: 5px 12px;
        font-size: 0.85rem;
        font-weight: 700;
    }
    .map-overlay-bottom {
        position: absolute; bottom: 10px; left: 10px; z-index: 500;
        background: #fff;
        border-radius: 10px;
        padding: 8px 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.18);
    }
</style>
@endpush

@section('content')

@php
    $isActive    = in_array($transport->statut, ['acceptee', 'en_cours']);
    $hasDestination = $transport->latitude_arrivee && $transport->longitude_arrivee;
    $statutColors = [
        'en_attente' => ['bg' => 'secondary', 'label' => 'En attente'],
        'acceptee'   => ['bg' => 'primary',   'label' => 'Acceptée'],
        'en_cours'   => ['bg' => 'warning',   'label' => 'En cours'],
        'terminee'   => ['bg' => 'success',   'label' => 'Terminée'],
        'annulee'    => ['bg' => 'danger',     'label' => 'Annulée'],
    ];
    $sc = $statutColors[$transport->statut] ?? ['bg' => 'secondary', 'label' => $transport->statut];
@endphp

<div class="row g-3">

    {{-- ── Colonne gauche : infos ────────────────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Statut --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-{{ $sc['bg'] }} bg-opacity-10"
                         style="width:52px;height:52px;">
                        <i class="fas fa-truck-medical fa-lg text-{{ $sc['bg'] }}"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5">Transport #{{ $transport->id }}</div>
                        <span class="badge bg-{{ $sc['bg'] }} fs-6">{{ $sc['label'] }}</span>
                        @if($isActive)
                            <span class="badge bg-danger ms-1 live-blink fs-6">
                                <i class="fas fa-circle" style="font-size:7px;"></i> En direct
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Accident --}}
        @if($transport->accident)
        <div class="card mb-3">
            <div class="card-header py-2 bg-danger bg-opacity-10 text-danger fw-bold">
                <i class="fas fa-car-crash me-1"></i> Accident lié
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="info-label">N° Rapport</div>
                        <div class="info-value">{{ $transport->accident->numero_rapport ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Localité</div>
                        <div class="info-value">{{ $transport->accident->localite }}</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Gravité</div>
                        @php $gc = ['leger'=>'success','grave'=>'warning','mortel'=>'danger']; @endphp
                        <span class="badge bg-{{ $gc[$transport->accident->gravite] ?? 'secondary' }}">
                            {{ ucfirst($transport->accident->gravite) }}
                        </span>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Victimes</div>
                        <div class="info-value">{{ $transport->accident->nombre_victimes ?? 0 }}</div>
                    </div>
                    @if($transport->accident->id)
                    <div class="col-12 mt-1">
                        <a href="{{ route('accidents.show', $transport->accident->id) }}"
                           class="btn btn-sm btn-outline-danger w-100">
                            <i class="fas fa-external-link-alt me-1"></i> Voir l'accident
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Personnes --}}
        <div class="card mb-3">
            <div class="card-header py-2 fw-bold">
                <i class="fas fa-users me-1"></i> Personnes impliquées
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="info-label"><i class="fas fa-user-shield text-info me-1"></i> Agent demandeur</div>
                    <div class="info-value">{{ $transport->demandeur?->name ?? '—' }}</div>
                    <small class="text-muted">{{ $transport->demandeur?->email ?? '' }}</small>
                </div>
                <div>
                    <div class="info-label"><i class="fas fa-truck text-primary me-1"></i> Transporteur</div>
                    @if($transport->transporteur)
                        <div class="info-value">{{ $transport->transporteur->name }}</div>
                        <small class="text-muted">{{ $transport->transporteur->email }}</small>
                    @else
                        <span class="text-muted fst-italic">Non assigné</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Chronologie --}}
        <div class="card mb-3">
            <div class="card-header py-2 fw-bold">
                <i class="fas fa-clock me-1"></i> Chronologie
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="small"><i class="fas fa-plus-circle text-secondary me-2"></i>Demandé le</span>
                        <span class="small fw-semibold">{{ $transport->created_at->format('d/m/Y à H:i') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2
                        {{ $transport->acceptee_at ? '' : 'text-muted' }}">
                        <span class="small"><i class="fas fa-check-circle text-primary me-2"></i>Accepté le</span>
                        <span class="small fw-semibold">
                            {{ $transport->acceptee_at?->format('d/m/Y à H:i') ?? '—' }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2
                        {{ $transport->terminee_at ? '' : 'text-muted' }}">
                        <span class="small"><i class="fas fa-flag-checkered text-success me-2"></i>Terminé le</span>
                        <span class="small fw-semibold">
                            {{ $transport->terminee_at?->format('d/m/Y à H:i') ?? '—' }}
                        </span>
                    </li>
                    @if($transport->acceptee_at && $transport->terminee_at)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="small"><i class="fas fa-stopwatch text-warning me-2"></i>Durée totale</span>
                        <span class="small fw-semibold">
                            {{ $transport->acceptee_at->diffForHumans($transport->terminee_at, true) }}
                        </span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- Notes --}}
        @if($transport->notes)
        <div class="card">
            <div class="card-header py-2 fw-bold"><i class="fas fa-sticky-note me-1"></i> Notes</div>
            <div class="card-body"><p class="mb-0 small">{{ $transport->notes }}</p></div>
        </div>
        @endif

    </div>

    {{-- ── Colonne droite : carte ─────────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between py-2">
                <span class="fw-bold">
                    <i class="fas fa-map-marked-alt me-1"></i>
                    @if($isActive)
                        Surveillance en temps réel
                    @else
                        Itinéraire enregistré
                    @endif
                </span>
                @if($isActive)
                <div class="d-flex align-items-center gap-2">
                    <span id="live-indicator" class="badge bg-danger live-blink">
                        <i class="fas fa-satellite-dish me-1"></i> Live
                    </span>
                    <span id="last-update" class="text-muted small"></span>
                </div>
                @endif
            </div>

            {{-- Carte --}}
            <div class="position-relative" style="background:#f8f9fa;">
                <div id="map-transport"></div>

                {{-- Overlay info transporteur (en haut à gauche sur la carte) --}}
                @if($transport->transporteur)
                <div class="map-overlay-top" id="transport-info-overlay">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                             style="width:32px;height:32px;flex-shrink:0;">
                            <i class="fas fa-truck text-white" style="font-size:13px;"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:0.85rem;line-height:1.1;">{{ $transport->transporteur->name }}</div>
                            <div id="statut-overlay" class="text-muted" style="font-size:0.75rem;">{{ $sc['label'] }}</div>
                        </div>
                    </div>
                    @if($isActive)
                    <div id="eta-overlay" class="eta-pill text-center mt-1">
                        <i class="fas fa-spinner fa-spin me-1"></i> Calcul itinéraire…
                    </div>
                    @endif
                </div>
                @endif

                {{-- Overlay légende (en bas à gauche) --}}
                <div class="map-overlay-bottom" style="font-size:0.78rem;">
                    <div class="d-flex gap-3">
                        <span>
                            <span style="display:inline-block;width:12px;height:12px;background:#dc3545;border-radius:50%;margin-right:4px;"></span>
                            Destination (accident)
                        </span>
                        @if($transport->transporteur)
                        <span>
                            <span style="display:inline-block;width:12px;height:12px;background:#0d6efd;border-radius:50%;margin-right:4px;"></span>
                            Transporteur
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- GPS Coordonnées --}}
        <div class="card mt-3">
            <div class="card-header py-2 fw-bold"><i class="fas fa-map-pin me-1"></i> Coordonnées GPS</div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-md-4">
                        <div class="info-label">Départ (agent)</div>
                        @if($transport->latitude_depart && $transport->longitude_depart)
                            <div class="small font-monospace">
                                {{ number_format($transport->latitude_depart, 6) }},
                                {{ number_format($transport->longitude_depart, 6) }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Destination (accident)</div>
                        @if($transport->latitude_arrivee && $transport->longitude_arrivee)
                            <div class="small font-monospace">
                                {{ number_format($transport->latitude_arrivee, 6) }},
                                {{ number_format($transport->longitude_arrivee, 6) }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Position transporteur</div>
                        <div id="gps-live-coords" class="small font-monospace text-muted">
                            @if($transport->lat_transporteur && $transport->lng_transporteur)
                                {{ number_format($transport->lat_transporteur, 6) }},
                                {{ number_format($transport->lng_transporteur, 6) }}
                            @else
                                En attente…
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    // ── Données initiales Blade → JS ───────────────────────────────────────
    const TRANSPORT_ID   = {{ $transport->id }};
    const IS_ACTIVE      = {{ $isActive ? 'true' : 'false' }};
    const POLL_URL       = "{{ route('transports.position', $transport) }}";
    const STATUT_INITIAL = "{{ $transport->statut }}";

    const DEST_LAT = {{ $transport->latitude_arrivee ?? ($transport->accident?->latitude ?? 'null') }};
    const DEST_LNG = {{ $transport->longitude_arrivee ?? ($transport->accident?->longitude ?? 'null') }};
    const TRANS_LAT = {{ $transport->lat_transporteur ?? 'null' }};
    const TRANS_LNG = {{ $transport->lng_transporteur ?? 'null' }};

    // ── Initialisation de la carte ─────────────────────────────────────────
    const map = L.map('map-transport', { zoomControl: true });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19,
    }).addTo(map);

    // ── Marqueur destination (rouge, pulsant) ──────────────────────────────
    let destinationMarker = null;
    const destIcon = L.divIcon({
        html: `<div style="
            width:32px;height:32px;
            background:#dc3545;
            border-radius:50%;
            border:3px solid #fff;
            box-shadow:0 0 0 4px rgba(220,53,69,0.35);
            display:flex;align-items:center;justify-content:center;
        "><i class="fas fa-plus" style="color:#fff;font-size:14px;"></i></div>`,
        className: '',
        iconSize: [32, 32],
        iconAnchor: [16, 16],
        popupAnchor: [0, -18],
    });

    if (DEST_LAT !== null && DEST_LNG !== null) {
        destinationMarker = L.marker([DEST_LAT, DEST_LNG], { icon: destIcon })
            .addTo(map)
            .bindPopup(`<b>Destination — Accident</b><br>
                {{ $transport->accident?->localite ?? '' }}<br>
                <small class="text-muted">{{ $transport->accident?->numero_rapport ?? '' }}</small>`);
        map.setView([DEST_LAT, DEST_LNG], 13);
    } else {
        map.setView([14.6937, -17.4441], 12); // Dakar par défaut
    }

    // ── Marqueur transporteur (bleu, avec flèche) ──────────────────────────
    let transporteurMarker = null;
    let routePolyline = null;

    const truckIconHtml = (color = '#0d6efd') => `
        <div style="
            width:36px;height:36px;
            background:${color};
            border-radius:50%;
            border:3px solid #fff;
            box-shadow:0 2px 8px rgba(13,110,253,0.4);
            display:flex;align-items:center;justify-content:center;
        "><i class="fas fa-truck" style="color:#fff;font-size:15px;"></i></div>`;

    const truckIcon = L.divIcon({
        html: truckIconHtml(),
        className: '',
        iconSize: [36, 36],
        iconAnchor: [18, 18],
        popupAnchor: [0, -20],
    });

    function placeTransporteurMarker(lat, lng, name) {
        if (transporteurMarker) {
            transporteurMarker.setLatLng([lat, lng]);
        } else {
            transporteurMarker = L.marker([lat, lng], { icon: truckIcon })
                .addTo(map)
                .bindPopup(`<b>${name}</b><br><small>Transporteur en route</small>`);
        }
    }

    // ── Tracé de l'itinéraire via OSRM ────────────────────────────────────
    let fetchingRoute = false;
    async function fetchAndDrawRoute(fromLat, fromLng, toLat, toLng) {
        if (fetchingRoute || toLat === null || toLng === null) return;
        fetchingRoute = true;
        try {
            const url = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`;
            const resp = await fetch(url, { signal: AbortSignal.timeout(8000) });
            const data = await resp.json();
            if (data.routes && data.routes.length > 0) {
                const route = data.routes[0];
                const coords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                const distKm  = (route.distance / 1000).toFixed(1);
                const etaMin  = Math.ceil(route.duration / 60);
                const arrival = new Date(Date.now() + route.duration * 1000);
                const hh = String(arrival.getHours()).padStart(2, '0');
                const mm = String(arrival.getMinutes()).padStart(2, '0');

                // Dessiner la polyline
                if (routePolyline) {
                    routePolyline.setLatLngs(coords);
                } else {
                    routePolyline = L.polyline(coords, {
                        color: '#1565c0',
                        weight: 6,
                        opacity: 0.85,
                        dashArray: null,
                    }).addTo(map);
                }

                // Ajuster la vue pour tout voir
                const bounds = routePolyline.getBounds();
                if (destinationMarker) bounds.extend(destinationMarker.getLatLng());
                map.fitBounds(bounds, { padding: [50, 50] });

                // Mettre à jour l'overlay ETA
                const etaEl = document.getElementById('eta-overlay');
                if (etaEl) {
                    etaEl.innerHTML = `
                        <i class="fas fa-clock me-1"></i>${etaMin} min
                        &nbsp;·&nbsp;
                        <i class="fas fa-road me-1"></i>${distKm} km
                        &nbsp;·&nbsp;
                        Arrivée ~${hh}:${mm}`;
                }
            }
        } catch (_) {}
        fetchingRoute = false;
    }

    // ── Position initiale du transporteur ─────────────────────────────────
    if (TRANS_LAT !== null && TRANS_LNG !== null) {
        placeTransporteurMarker(TRANS_LAT, TRANS_LNG, "{{ $transport->transporteur?->name ?? 'Transporteur' }}");
        if (DEST_LAT !== null) {
            fetchAndDrawRoute(TRANS_LAT, TRANS_LNG, DEST_LAT, DEST_LNG);
            // Ajuster la vue
            map.fitBounds([
                [Math.min(TRANS_LAT, DEST_LAT) - 0.005, Math.min(TRANS_LNG, DEST_LNG) - 0.005],
                [Math.max(TRANS_LAT, DEST_LAT) + 0.005, Math.max(TRANS_LNG, DEST_LNG) + 0.005],
            ]);
        }
    } else if (DEST_LAT !== null) {
        map.setView([DEST_LAT, DEST_LNG], 13);
    }

    // ── Polling live si transport actif ───────────────────────────────────
    if (!IS_ACTIVE) return;

    const statusLabels = {
        en_attente: 'En attente',
        acceptee:   'Acceptée',
        en_cours:   'En cours',
        terminee:   'Terminée',
        annulee:    'Annulée',
    };

    async function pollPosition() {
        try {
            const resp = await fetch(POLL_URL, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: AbortSignal.timeout(6000),
            });
            if (!resp.ok) return;
            const data = await resp.json();

            const lat = data.lat_transporteur ? parseFloat(data.lat_transporteur) : null;
            const lng = data.lng_transporteur ? parseFloat(data.lng_transporteur) : null;
            const statut = data.statut || '';

            // Mise à jour du statut dans l'overlay
            const statutEl = document.getElementById('statut-overlay');
            if (statutEl) statutEl.textContent = statusLabels[statut] || statut;

            // Mise à jour de la date de dernière position
            if (data.position_updated_at) {
                const d = new Date(data.position_updated_at);
                const diff = Math.round((Date.now() - d.getTime()) / 1000);
                const lastUpdateEl = document.getElementById('last-update');
                if (lastUpdateEl) {
                    lastUpdateEl.textContent = diff < 15 ? 'Position en direct' :
                        diff < 60 ? `Il y a ${diff}s` : `Il y a ${Math.floor(diff/60)} min`;
                }
            }

            // Mise à jour des coordonnées GPS affichées
            const coordsEl = document.getElementById('gps-live-coords');
            if (coordsEl && lat !== null) {
                coordsEl.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            }

            // Déplacer le marqueur
            if (lat !== null && lng !== null) {
                placeTransporteurMarker(lat, lng, "{{ $transport->transporteur?->name ?? 'Transporteur' }}");
                fetchAndDrawRoute(lat, lng, DEST_LAT, DEST_LNG);
            }

            // Si course terminée/annulée → arrêter le polling
            if (statut === 'terminee' || statut === 'annulee') {
                clearInterval(pollInterval);
                const liveIndicator = document.getElementById('live-indicator');
                if (liveIndicator) liveIndicator.remove();
                const etaEl = document.getElementById('eta-overlay');
                if (etaEl) {
                    etaEl.style.background = statut === 'terminee' ? '#198754' : '#dc3545';
                    etaEl.innerHTML = statut === 'terminee'
                        ? '<i class="fas fa-check-circle me-1"></i> Arrivé à destination'
                        : '<i class="fas fa-times-circle me-1"></i> Course annulée';
                }
            }
        } catch (_) {}
    }

    // Lancer le polling toutes les 5 secondes
    const pollInterval = setInterval(pollPosition, 5000);
    pollPosition(); // première exécution immédiate

})();
</script>
@endpush
