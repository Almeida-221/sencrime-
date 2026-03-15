<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SenCrime — Connexion</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex;
            font-family: 'Nunito', sans-serif;
            background: #0a1628;
        }

        /* ── Panneau gauche ── */
        .left-panel {
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(160deg, #0d1f3c 0%, #1a3a5c 40%, #0d2137 100%);
        }

        /* Motif géométrique de fond */
        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(
                    45deg,
                    rgba(255,255,255,0.015) 0px,
                    rgba(255,255,255,0.015) 1px,
                    transparent 1px,
                    transparent 40px
                );
        }

        /* Cercles décoratifs */
        .circle-deco {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .circle-deco.c1 { width: 500px; height: 500px; top: -150px; left: -150px; }
        .circle-deco.c2 { width: 350px; height: 350px; bottom: -100px; right: -80px; }
        .circle-deco.c3 { width: 200px; height: 200px; top: 40%; left: 5%; }

        /* Bande tricolore sénégalaise (haut) */
        .flag-stripe {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            display: flex;
        }
        .flag-stripe span { flex: 1; }
        .flag-stripe .green  { background: #00853F; }
        .flag-stripe .yellow { background: #FDEF42; }
        .flag-stripe .red    { background: #E31B23; }

        .left-content {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 40px;
        }

        /* Badge emblème */
        .emblem {
            width: 160px;
            height: 160px;
            margin: 0 auto 25px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .emblem-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 3px solid rgba(244, 162, 97, 0.6);
            animation: pulse-ring 3s ease-in-out infinite;
        }
        .emblem-ring.r2 {
            inset: -8px;
            border-color: rgba(244, 162, 97, 0.3);
            animation-delay: 0.5s;
        }

        @keyframes pulse-ring {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50%       { transform: scale(1.04); opacity: 1; }
        }

        .emblem-inner {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 0 30px rgba(244, 162, 97, 0.4), 0 8px 24px rgba(0,0,0,0.4);
            z-index: 1;
        }

        .emblem-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .left-content h1 {
            color: #fff;
            font-size: 2.4rem;
            font-weight: 900;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .left-content .subtitle {
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 40px;
        }

        /* Carte infos */
        .info-badges {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .info-badge {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 30px;
            padding: 8px 18px;
            color: rgba(255,255,255,0.75);
            font-size: 0.78rem;
            display: flex;
            align-items: center;
            gap: 7px;
            backdrop-filter: blur(4px);
        }

        .info-badge i { color: #f4a261; font-size: 0.9rem; }

        /* Stats */
        .stats-row {
            display: flex;
            gap: 30px;
            justify-content: center;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .stat-item { text-align: center; }
        .stat-item .num { color: #f4a261; font-size: 1.6rem; font-weight: 800; }
        .stat-item .lbl { color: rgba(255,255,255,0.45); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; }

        /* ── Panneau droit (formulaire) ── */
        .right-panel {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 50px 45px;
            position: relative;
            box-shadow: -10px 0 60px rgba(0,0,0,0.3);
        }

        .right-panel form,
        .right-panel .login-header,
        .right-panel .alert-error,
        .right-panel .login-footer {
            width: 100%;
            max-width: 420px;
        }

        .right-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1a3a5c, #f4a261, #e63946);
        }

        .login-header { margin-bottom: 35px; }
        .login-header h2 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1a3a5c;
            margin-bottom: 5px;
        }
        .login-header p { color: #9ca3af; font-size: 0.875rem; }

        /* Champs */
        .form-group { margin-bottom: 22px; }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.9rem;
        }
        .input-wrapper input {
            width: 100%;
            padding: 13px 14px 13px 42px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #1f2937;
            background: #f9fafb;
            transition: all 0.2s;
            outline: none;
            font-family: inherit;
        }
        .input-wrapper input:focus {
            border-color: #1a3a5c;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(26,58,92,0.08);
        }
        .input-wrapper input.is-invalid { border-color: #e63946; }
        .invalid-msg { color: #e63946; font-size: 0.78rem; margin-top: 5px; display: block; }

        /* Toggle password */
        .input-wrapper .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 0;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .input-wrapper .toggle-pw:hover { color: #1a3a5c; }

        /* Remember + forgot */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            font-size: 0.83rem;
        }
        .form-options label { display: flex; align-items: center; gap: 7px; color: #6b7280; cursor: pointer; }
        .form-options input[type=checkbox] { accent-color: #1a3a5c; width: 15px; height: 15px; }
        .form-options a { color: #1a3a5c; text-decoration: none; font-weight: 600; }
        .form-options a:hover { text-decoration: underline; }

        /* Bouton */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1a3a5c, #2d6a9f);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.5px;
            font-family: inherit;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #0d2137, #1a3a5c);
            box-shadow: 0 8px 20px rgba(26,58,92,0.35);
            transform: translateY(-1px);
        }
        .btn-login:active { transform: translateY(0); }

        /* Footer */
        .login-footer {
            margin-top: 35px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
            text-align: center;
        }
        .login-footer p { color: #9ca3af; font-size: 0.75rem; }
        .login-footer .flag-mini {
            display: inline-flex;
            gap: 2px;
            margin-bottom: 8px;
            border-radius: 2px;
            overflow: hidden;
        }
        .login-footer .flag-mini span { width: 14px; height: 9px; display: block; }

        /* Alert erreur */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 12px 15px;
            color: #dc2626;
            font-size: 0.85rem;
            margin-bottom: 22px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-error i { margin-top: 1px; flex-shrink: 0; }

        /* Responsive */
        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { flex: 1; padding: 40px 25px; }
        }
    </style>
</head>
<body>

    <!-- ── Panneau gauche ── -->
    <div class="left-panel">
        <div class="flag-stripe">
            <span class="green"></span>
            <span class="yellow"></span>
            <span class="red"></span>
        </div>

        <div class="circle-deco c1"></div>
        <div class="circle-deco c2"></div>
        <div class="circle-deco c3"></div>

        <div class="left-content">
            <!-- Emblème Police Nationale -->
            <div class="emblem">
                <div class="emblem-ring"></div>
                <div class="emblem-ring r2"></div>
                <div class="emblem-inner">
                    <img src="{{ asset('images/police_nationale.jpg') }}" alt="Police Nationale Sénégal">
                </div>
            </div>

            <h1>SenCrime</h1>
            <p class="subtitle">Police Nationale du Sénégal</p>

            <div class="info-badges">
                <div class="info-badge">
                    <i class="fas fa-lock"></i>
                    Accès sécurisé
                </div>
                <div class="info-badge">
                    <i class="fas fa-map-marker-alt"></i>
                    14 Régions
                </div>
                <div class="info-badge">
                    <i class="fas fa-database"></i>
                    Données chiffrées
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="num">14</div>
                    <div class="lbl">Régions</div>
                </div>
                <div class="stat-item">
                    <div class="num">360°</div>
                    <div class="lbl">Couverture</div>
                </div>
                <div class="stat-item">
                    <div class="num">24/7</div>
                    <div class="lbl">Disponible</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Panneau droit (formulaire) ── -->
    <div class="right-panel">
        <div class="login-header">
            <h2>Connexion</h2>
            <p>Entrez vos identifiants pour accéder à la plateforme</p>
        </div>

        @if ($errors->any())
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
        @endif

        @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 15px;color:#16a34a;font-size:0.85rem;margin-bottom:22px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Adresse email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="vous@exemple.sn"
                        required
                        autocomplete="email"
                        autofocus
                        class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword()" id="toggleBtn">
                        <i class="fas fa-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label>
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Se souvenir de moi
                </label>
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                @endif
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
        </form>

        <div class="login-footer">
            <div class="flag-mini">
                <span style="background:#00853F;"></span>
                <span style="background:#FDEF42;"></span>
                <span style="background:#E31B23;"></span>
            </div>
            <p>Système de Gestion Criminelle — République du Sénégal</p>
            <p style="margin-top:4px;">Accès réservé au personnel autorisé</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('pwIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>
