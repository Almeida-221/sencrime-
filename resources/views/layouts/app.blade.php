<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SenCrime') - Système de Gestion Criminelle</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #1a3a5c;
            --secondary: #e63946;
            --accent: #f4a261;
        }
        body { background: #f0f2f5; font-family: 'Nunito', sans-serif; }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1a3a5c 0%, #0d2137 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: all 0.3s;
        }
        .sidebar .brand {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar .brand h4 { color: #fff; font-weight: 800; font-size: 1.3rem; margin: 0; }
        .sidebar .brand small { color: rgba(255,255,255,0.6); font-size: 0.7rem; }
        .sidebar .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 20px;
        }
        /* Scrollbar fine et discrète */
        .sidebar .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .sidebar .sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 0;
            transition: all 0.2s;
            font-size: 0.875rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
            border-left: 3px solid var(--accent);
        }
        .sidebar .nav-section {
            padding: 10px 20px 5px;
            color: rgba(255,255,255,0.4);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        .topbar {
            background: #fff;
            padding: 12px 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .page-content { padding: 25px; }
        .card { border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.07); border-radius: 12px; }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; font-weight: 700; border-radius: 12px 12px 0 0 !important; }
        .stat-card { border-radius: 12px; padding: 20px; color: #fff; position: relative; overflow: hidden; }
        .stat-card .icon { font-size: 2.5rem; opacity: 0.3; position: absolute; right: 15px; top: 15px; }
        .stat-card h3 { font-size: 2rem; font-weight: 800; margin: 0; }
        .stat-card p { margin: 0; opacity: 0.9; font-size: 0.875rem; }
        .bg-primary-custom { background: linear-gradient(135deg, #1a3a5c, #2d6a9f); }
        .bg-danger-custom { background: linear-gradient(135deg, #e63946, #c1121f); }
        .bg-success-custom { background: linear-gradient(135deg, #2d6a4f, #40916c); }
        .bg-warning-custom { background: linear-gradient(135deg, #f4a261, #e76f51); }
        .bg-info-custom { background: linear-gradient(135deg, #0077b6, #00b4d8); }
        .bg-purple-custom { background: linear-gradient(135deg, #7b2d8b, #a855f7); }
        .badge-statut { font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; }
        .table th { font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #6c757d; }
        .btn-action { padding: 4px 10px; font-size: 0.8rem; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <h4><i class="fas fa-shield-alt me-2"></i>SenCrime</h4>
            <small>Système de Gestion Criminelle</small>
        </div>
        @auth
        @php
            $authUser      = auth()->user();
            $isSuperAdmin  = $authUser->hasRole('super_admin');
            $isAdminRegion = $authUser->hasRole('superviseur');
            $isAgent       = $authUser->hasRole('agent');
            $canManage     = $isSuperAdmin;
        @endphp
        <div class="sidebar-nav">
        <nav class="mt-3">
            @if($isAdminRegion && $authUser->getRegionEffective())
            <div class="text-center mb-2">
                <span class="badge" style="background:rgba(244,162,97,0.25);color:#f4a261;font-size:0.7rem;padding:4px 10px;">
                    <i class="fas fa-map-marker-alt me-1"></i>{{ $authUser->getRegionEffective() }}
                </span>
            </div>
            @endif

            <div class="nav-section">Principal</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>

            @if($canManage)
            <div class="nav-section">Gestion</div>
            <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i> Services
            </a>
            <a href="{{ route('agents.index') }}" class="nav-link {{ request()->routeIs('agents.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i> Agents
            </a>
            @endif

            <div class="nav-section">Criminalité</div>
            @if($canManage)
            <a href="{{ route('types-infractions.index') }}" class="nav-link {{ request()->routeIs('types-infractions.*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Types d'infractions
            </a>
            @endif
            <a href="{{ route('infractions.index') }}" class="nav-link {{ request()->routeIs('infractions.*') ? 'active' : '' }}">
                <i class="fas fa-gavel"></i> Infractions
            </a>
            <a href="{{ route('accidents.index') }}" class="nav-link {{ request()->routeIs('accidents.*') ? 'active' : '' }}">
                <i class="fas fa-car-crash"></i> Accidents
            </a>

            @if($canManage || $isAdminRegion)
            <div class="nav-section">Finances</div>
            <a href="{{ route('amendes.index') }}" class="nav-link {{ request()->routeIs('amendes.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i> Amendes
            </a>
            @endif
            @if($canManage)
            <a href="{{ route('services-retribues.index') }}" class="nav-link {{ request()->routeIs('services-retribues.*') ? 'active' : '' }}">
                <i class="fas fa-handshake"></i> Services Rétribués
            </a>
            @endif

            <div class="nav-section">Surveillance</div>
            <a href="{{ route('surveillance.index') }}" class="nav-link {{ request()->routeIs('surveillance.*') ? 'active' : '' }}">
                <i class="fas fa-map-marked-alt"></i> Carte de Surveillance
            </a>
            @role('super_admin|admin|superviseur')
            <a href="{{ route('transports.index') }}" class="nav-link {{ request()->routeIs('transports.*') ? 'active' : '' }}">
                <i class="fas fa-truck-medical"></i> Transports
            </a>
            @endrole

            <div class="nav-section">Immigration</div>
            <a href="{{ route('immigrations.index') }}" class="nav-link {{ request()->routeIs('immigrations.*') ? 'active' : '' }}">
                <i class="fas fa-passport"></i> Immigration Clandestine
            </a>

            {{-- Chat --}}
            @if($canManage || $isAdminRegion)
            <div class="nav-section">Communication</div>
            <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                <i class="fas fa-comments"></i>
                <span>Messagerie</span>
                <span id="chatBadgeSidebar" class="ms-auto badge rounded-pill"
                      style="background:#e63946;font-size:0.65rem;display:none;"></span>
            </a>
            @endif

            {{-- Rapports & Import --}}
            @if($canManage || $isAdminRegion)
            <div class="nav-section">Rapports & Données</div>
            <a href="{{ route('rapports.index') }}" class="nav-link {{ request()->routeIs('rapports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Rapports PDF
            </a>
            <a href="{{ route('imports.index') }}" class="nav-link {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                <i class="fas fa-file-excel"></i> Import Excel
            </a>
            @endif

            @if($canManage || $isAdminRegion)
            <div class="nav-section">Administration</div>
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i> Utilisateurs
            </a>
            @endif
        </nav>
        </div>
        @endauth
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <h5 class="mb-0 fw-bold text-dark">@yield('page-title', 'Tableau de bord')</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:0.8rem;">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            {{-- ✅ CORRECTION 2 : protéger toute la topbar user avec auth()->check() --}}
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">{{ now()->format('d/m/Y H:i') }}</span>

                @auth
                {{-- ── Cloche Notifications ── --}}
                <div class="dropdown" id="notifDropdown">
                    <button class="btn btn-light btn-sm position-relative" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false"
                            onclick="marquerVuDropdown()"
                            style="width:38px;height:38px;padding:0;border-radius:50%;">
                        <i class="fas fa-bell" style="font-size:1rem;"></i>
                        <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              style="font-size:0.6rem;display:none;">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow" style="width:340px;max-height:420px;overflow-y:auto;border-radius:12px;padding:0;">
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="background:#f8f9fa;border-radius:12px 12px 0 0;">
                            <span class="fw-bold small" style="color:#1a3a5c;">Notifications</span>
                            <button class="btn btn-link btn-sm p-0 text-muted" style="font-size:0.75rem;" onclick="lireToutWeb()">
                                Tout marquer lu
                            </button>
                        </div>
                        <div id="notifListe" style="min-height:60px;">
                            <div class="text-center text-muted py-4 small" id="notifEmpty">Aucune notification</div>
                        </div>
                    </div>
                </div>

                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>{{ auth()->user()->name }}
                            @foreach(auth()->user()->roles as $role)
                                <span class="badge bg-primary ms-1" style="font-size:0.65rem;">{{ $role->name }}</span>
                            @endforeach
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-in-alt me-1"></i>Connexion
                    </a>
                @endauth
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    @stack('scripts')

    @auth
    {{-- ── FAB Chat (bas droite) ── --}}
    @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('superviseur'))
    <style>
        .chat-fab {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }
        .chat-fab-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1a3a5c, #2d6a9f);
            color: #fff;
            border: none;
            box-shadow: 0 4px 18px rgba(26,58,92,0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            position: relative;
        }
        .chat-fab-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 24px rgba(26,58,92,0.55);
            color: #fff;
        }
        .chat-fab-btn i { font-size: 1.3rem; }
        .chat-fab-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            min-width: 20px;
            height: 20px;
            background: #e63946;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 800;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid #fff;
            display: none;
        }
        .chat-fab-tooltip {
            background: rgba(26,58,92,0.92);
            color: #fff;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
            white-space: nowrap;
            opacity: 0;
            transform: translateX(10px);
            transition: opacity 0.2s, transform 0.2s;
            pointer-events: none;
        }
        .chat-fab:hover .chat-fab-tooltip {
            opacity: 1;
            transform: translateX(0);
        }
        @keyframes fab-pulse {
            0%, 100% { box-shadow: 0 4px 18px rgba(26,58,92,0.45); }
            50%       { box-shadow: 0 4px 24px rgba(230,57,70,0.6); }
        }
        .chat-fab-btn.has-unread { animation: fab-pulse 2s ease-in-out infinite; }
    </style>
    <div class="chat-fab">
        <div class="d-flex align-items-center gap-2">
            <span class="chat-fab-tooltip" id="chatFabTooltip">Messagerie</span>
            <a href="{{ route('chat.index') }}" class="chat-fab-btn" id="chatFabBtn" title="Messagerie">
                <i class="fas fa-comments"></i>
                <span class="chat-fab-badge" id="chatFabBadge"></span>
            </a>
        </div>
    </div>
    @endif

    <script>
    const NOTIF_URL      = '{{ route("notifications.ajax") }}';
    const NOTIF_LIRE_URL = '{{ url("notifications") }}';
    const CSRF           = '{{ csrf_token() }}';

    const COULEUR_MAP = {
        primary: '#1a3a5c', danger: '#e63946', warning: '#f4a261',
        success: '#2d6a4f', info: '#0077b6'
    };

    async function chargerNotifications() {
        try {
            const r = await fetch(NOTIF_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) return;
            const data = await r.json();
            const badge = document.getElementById('notifBadge');
            const liste = document.getElementById('notifListe');
            const empty = document.getElementById('notifEmpty');

            // Badge
            if (data.non_lues > 0) {
                badge.textContent = data.non_lues > 9 ? '9+' : data.non_lues;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }

            // Liste
            if (!data.notifications || data.notifications.length === 0) {
                liste.innerHTML = '<div class="text-center text-muted py-4 small">Aucune notification</div>';
                return;
            }

            liste.innerHTML = data.notifications.map(n => `
                <div class="notif-item d-flex gap-2 px-3 py-2 border-bottom ${!n.lu ? 'notif-unread' : ''}"
                     style="cursor:pointer;background:${!n.lu ? 'rgba(26,58,92,0.04)' : '#fff'};"
                     onclick="lireNotif(${n.id}, this, '${n.lien || ''}')">
                    <div style="width:32px;height:32px;border-radius:50%;background:${COULEUR_MAP[n.couleur] || '#1a3a5c'}22;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                        <i class="fas ${n.icone || 'fa-bell'}" style="font-size:0.75rem;color:${COULEUR_MAP[n.couleur] || '#1a3a5c'};"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="fw-bold" style="font-size:0.78rem;color:#1a3a5c;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${n.titre}</div>
                        <div style="font-size:0.75rem;color:#6c757d;line-height:1.3;">${n.message}</div>
                        <div style="font-size:0.68rem;color:#adb5bd;margin-top:2px;">${n.created_at}</div>
                    </div>
                    ${!n.lu ? '<div style="width:8px;height:8px;border-radius:50%;background:#e63946;flex-shrink:0;margin-top:6px;"></div>' : ''}
                </div>
            `).join('');
        } catch (e) {}
    }

    function lireNotif(id, el, lien) {
        fetch(`${NOTIF_LIRE_URL}/${id}/lire`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        });
        el.style.background = '#fff';
        el.classList.remove('notif-unread');
        const dot = el.querySelector('div[style*="border-radius:50%;background:#e63946"]');
        if (dot) dot.remove();
        chargerNotifications();
        if (lien) { window.location.href = lien; }
    }

    function lireToutWeb() {
        fetch('{{ route("notifications.lire-tout") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(() => chargerNotifications());
    }

    function marquerVuDropdown() {
        // Ne marque pas automatiquement - l'utilisateur doit cliquer chaque notif
    }

    // Charger au démarrage et toutes les 30 secondes
    chargerNotifications();
    setInterval(chargerNotifications, 30000);

    // ── Badge chat sidebar ──────────────────────────────────────────────
    async function chargerBadgeChat() {
        try {
            const r = await fetch('{{ route("chat.non-lus") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) return;
            const data = await r.json();

            // Badge sidebar
            const badgeSidebar = document.getElementById('chatBadgeSidebar');
            if (badgeSidebar) {
                if (data.non_lus > 0) {
                    badgeSidebar.textContent = data.non_lus > 9 ? '9+' : data.non_lus;
                    badgeSidebar.style.display = '';
                } else {
                    badgeSidebar.style.display = 'none';
                }
            }

            // Badge + animation FAB
            const fabBadge = document.getElementById('chatFabBadge');
            const fabBtn   = document.getElementById('chatFabBtn');
            const fabTip   = document.getElementById('chatFabTooltip');
            if (fabBadge && fabBtn) {
                if (data.non_lus > 0) {
                    fabBadge.textContent = data.non_lus > 9 ? '9+' : data.non_lus;
                    fabBadge.style.display = 'flex';
                    fabBtn.classList.add('has-unread');
                    if (fabTip) fabTip.textContent = `${data.non_lus} message${data.non_lus > 1 ? 's' : ''} non lu${data.non_lus > 1 ? 's' : ''}`;
                } else {
                    fabBadge.style.display = 'none';
                    fabBtn.classList.remove('has-unread');
                    if (fabTip) fabTip.textContent = 'Messagerie';
                }
            }
        } catch(e) {}
    }
    chargerBadgeChat();
    setInterval(chargerBadgeChat, 15000);
    </script>
    @endauth
</body>
</html>