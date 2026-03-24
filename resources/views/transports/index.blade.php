@extends('layouts.app')
@section('title', 'Transports')
@section('page-title', 'Historique des Transports')
@section('breadcrumb')
    <li class="breadcrumb-item active">Transports</li>
@endsection

@section('content')

{{-- ── Carte Live — tous les transporteurs actifs ──────────────────────── --}}
<div class="card mb-3" id="live-map-card">
    <div class="card-header d-flex align-items-center justify-content-between py-2">
        <span class="fw-bold">
            <i class="fas fa-satellite-dish me-2 text-danger"></i>
            Transporteurs en direct
        </span>
        <div class="d-flex align-items-center gap-2">
            <span id="active-count" class="badge bg-primary fs-6">—</span>
            <span class="badge bg-danger live-blink">
                <i class="fas fa-circle" style="font-size:7px;"></i> Live
            </span>
            <button class="btn btn-sm btn-outline-secondary" id="toggle-map-btn" onclick="toggleLiveMap()">
                <i class="fas fa-chevron-up" id="toggle-icon"></i>
            </button>
        </div>
    </div>
    <div id="live-map-body">
        <div id="live-map-all" style="height:380px;"></div>
    </div>
</div>

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
    <div class="col-6 col-md-2">
        <a href="{{ route('transports.index', ['statut' => 'expiree']) }}" class="text-decoration-none">
            <div class="card text-center border-0 shadow-sm h-100" style="border-left: 4px solid #6f42c1 !important;">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-purple" style="color:#6f42c1;">{{ $stats['expiree'] }}</div>
                    <div class="small text-muted">Expirées</div>
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
                    <option value="expiree"    {{ request('statut') == 'expiree'    ? 'selected' : '' }}>Expirée (7h)</option>
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
                            'expiree'    => 'purple',
                        ];
                        $statutLabels = [
                            'en_attente' => 'En attente',
                            'acceptee'   => 'Acceptée',
                            'en_cours'   => 'En cours',
                            'terminee'   => 'Terminée',
                            'annulee'    => 'Annulée',
                            'expiree'    => 'Expirée',
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
                            @php $sColor = $statutColors[$t->statut] ?? 'secondary'; @endphp
                            <span class="badge {{ $sColor === 'purple' ? '' : 'bg-'.$sColor }}"
                                  @if($sColor === 'purple') style="background:#6f42c1;" @endif>
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
                        <td class="d-flex gap-1">
                            <a href="{{ route('transports.show', $t) }}"
                               class="btn btn-sm btn-info btn-action"
                               title="{{ in_array($t->statut, ['acceptee','en_cours']) ? 'Surveiller en live' : 'Voir détail' }}">
                                @if(in_array($t->statut, ['acceptee', 'en_cours']))
                                    <i class="fas fa-satellite-dish"></i>
                                @else
                                    <i class="fas fa-eye"></i>
                                @endif
                            </a>
                            @hasanyrole('super_admin|admin')
                            <form method="POST" action="{{ route('transports.destroy', $t) }}"
                                  onsubmit="return confirm('Supprimer cette demande de transport ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger btn-action" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endhasanyrole
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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .live-blink { animation: blink 1.2s infinite; }
    @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
    .btn-action { width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    #live-map-all { border-radius: 0 0 8px 8px; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const LIVE_ALL_URL = "{{ route('transports.live-all') }}";

    // ── Initialisation de la carte ─────────────────────────────────────────
    const map = L.map('live-map-all', { zoomControl: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19,
    }).addTo(map);
    map.setView([14.6937, -17.4441], 8); // Sénégal

    // ── Icônes ─────────────────────────────────────────────────────────────
    const makeTransporteurIcon = (color = '#0d6efd') => L.divIcon({
        html: `<div style="
            width:38px;height:38px;
            background:${color};
            border-radius:50%;
            border:3px solid #fff;
            box-shadow:0 2px 10px rgba(0,0,0,0.35);
            display:flex;align-items:center;justify-content:center;
        "><i class="fas fa-truck" style="color:#fff;font-size:15px;"></i></div>`,
        className: '',
        iconSize: [38, 38],
        iconAnchor: [19, 19],
    });

    const destIcon = L.divIcon({
        html: `<div style="
            width:28px;height:28px;
            background:#dc3545;
            border-radius:50%;
            border:3px solid #fff;
            box-shadow:0 2px 8px rgba(220,53,69,0.5);
            display:flex;align-items:center;justify-content:center;
        "><i class="fas fa-plus" style="color:#fff;font-size:11px;"></i></div>`,
        className: '',
        iconSize: [28, 28],
        iconAnchor: [14, 14],
    });

    // ── Marqueurs stockés par ID transport ─────────────────────────────────
    const markers = {};      // { id: { truck: marker, dest: marker, route: polyline } }

    // ── Tracé de l'itinéraire OSRM ────────────────────────────────────────
    async function fetchRoute(id, fromLat, fromLng, toLat, toLng) {
        if (!toLat || !toLng) return;
        try {
            const url = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`;
            const resp = await fetch(url, { signal: AbortSignal.timeout(8000) });
            const data = await resp.json();
            if (data.routes && data.routes.length) {
                const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                if (markers[id]) {
                    if (markers[id].route) {
                        markers[id].route.setLatLngs(coords);
                    } else {
                        markers[id].route = L.polyline(coords, {
                            color: '#1565c0',
                            weight: 5,
                            opacity: 0.75,
                        }).addTo(map);
                    }
                }
            }
        } catch (_) {}
    }

    // ── Mise à jour de la carte depuis les données JSON ────────────────────
    function updateMap(transports) {
        const activeEl = document.getElementById('active-count');
        if (activeEl) activeEl.textContent = transports.length + ' actif' + (transports.length > 1 ? 's' : '');

        const seenIds = new Set();

        transports.forEach(t => {
            seenIds.add(t.id);
            const color = t.statut === 'en_cours' ? '#198754' : '#0d6efd';
            const popupHtml = `
                <div style="min-width:180px;">
                    <div class="fw-bold"><i class="fas fa-truck me-1"></i>${t.transporteur_name}</div>
                    <div class="text-muted small">📍 ${t.localite}</div>
                    <div class="small">Gravité : <b>${t.gravite}</b></div>
                    <div class="small text-muted">${t.statut === 'en_cours' ? '🟢 En cours' : '🔵 Acceptée'}</div>
                    <a href="${t.detail_url}" class="btn btn-sm btn-primary mt-1 w-100">
                        <i class="fas fa-satellite-dish me-1"></i>Surveiller
                    </a>
                </div>`;

            if (markers[t.id]) {
                // Déplacer le marqueur existant
                markers[t.id].truck.setLatLng([t.lat_transporteur, t.lng_transporteur]);
                markers[t.id].truck.setIcon(makeTransporteurIcon(color));
                markers[t.id].truck.setPopupContent(popupHtml);
            } else {
                // Nouveau marqueur
                const truck = L.marker([t.lat_transporteur, t.lng_transporteur], {
                    icon: makeTransporteurIcon(color),
                }).addTo(map).bindPopup(popupHtml);

                const dest = t.dest_lat && t.dest_lng
                    ? L.marker([t.dest_lat, t.dest_lng], { icon: destIcon })
                        .addTo(map)
                        .bindPopup(`<b>Destination</b><br>${t.localite}`)
                    : null;

                markers[t.id] = { truck, dest, route: null };
            }

            // Recalculer la route
            if (t.dest_lat && t.dest_lng) {
                fetchRoute(t.id, t.lat_transporteur, t.lng_transporteur, t.dest_lat, t.dest_lng);
            }
        });

        // Supprimer les marqueurs des courses terminées/absentes
        Object.keys(markers).forEach(id => {
            if (!seenIds.has(parseInt(id))) {
                markers[id].truck?.remove();
                markers[id].dest?.remove();
                markers[id].route?.remove();
                delete markers[id];
            }
        });
    }

    // ── Polling toutes les 6 secondes ─────────────────────────────────────
    async function pollAll() {
        try {
            const resp = await fetch(LIVE_ALL_URL, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: AbortSignal.timeout(8000),
            });
            if (!resp.ok) return;
            const data = await resp.json();
            updateMap(data);
        } catch (_) {}
    }

    pollAll();
    setInterval(pollAll, 6000);

    // ── Toggle carte ──────────────────────────────────────────────────────
    window.toggleLiveMap = function () {
        const body = document.getElementById('live-map-body');
        const icon = document.getElementById('toggle-icon');
        if (body.style.display === 'none') {
            body.style.display = '';
            icon.className = 'fas fa-chevron-up';
            map.invalidateSize();
        } else {
            body.style.display = 'none';
            icon.className = 'fas fa-chevron-down';
        }
    };
})();
</script>
@endpush
