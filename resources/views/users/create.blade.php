@extends('layouts.app')
@section('title', 'Nouvel Utilisateur')
@section('page-title', 'Créer un Utilisateur')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-user-plus me-2"></i>Nouvel Utilisateur</div>
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Informations personnelles</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold d-block mb-2">Force du mot de passe</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="password_strength" id="pw-fort" value="fort" checked onchange="togglePasswordStrength()">
                            <label class="form-check-label" for="pw-fort">
                                <span class="badge bg-danger me-1"><i class="fas fa-lock"></i></span>
                                <strong>Fort</strong> <small class="text-muted">(min. 8 caractères)</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="password_strength" id="pw-leger" value="leger" onchange="togglePasswordStrength()">
                            <label class="form-check-label" for="pw-leger">
                                <span class="badge bg-warning text-dark me-1"><i class="fas fa-lock-open"></i></span>
                                <strong>Léger</strong> <small class="text-muted">(min. 4 caractères)</small>
                            </label>
                        </div>
                    </div>
                    <div id="pw-strength-info" class="mt-2">
                        <small class="text-danger fw-bold" id="pw-fort-info">
                            <i class="fas fa-shield-alt me-1"></i>Mot de passe sécurisé : min. 8 caractères, lettres et chiffres recommandés.
                        </small>
                        <small class="text-warning fw-bold d-none" id="pw-leger-info">
                            <i class="fas fa-exclamation-triangle me-1"></i>Mot de passe léger : min. 4 caractères. Moins sécurisé.
                        </small>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Mot de passe <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password" id="pw-input" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePwVisibility()">
                            <i class="fas fa-eye" id="pw-eye"></i>
                        </button>
                    </div>
                    <div id="pw-bar-wrap" class="mt-1" style="height:4px;background:#e5e7eb;border-radius:2px;display:none;">
                        <div id="pw-bar" style="height:100%;width:0;border-radius:2px;transition:all .3s;"></div>
                    </div>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Confirmer mot de passe <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" id="pw-confirm" class="form-control" required>
                    <small id="pw-match-msg" class="d-none mt-1"></small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Téléphone</label>
                    <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror" value="{{ old('telephone') }}">
                    @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Affectation & Rôle</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Région</label>
                    @php $isRegional = auth()->user()->hasRole(['superviseur']); @endphp
                    <select name="region" class="form-select @error('region') is-invalid @enderror"
                        {{ $isRegional ? 'disabled' : '' }}>
                        <option value="">-- Sélectionner --</option>
                        @foreach($regions as $r)
                            @php $selected = old('region', $isRegional ? auth()->user()->getRegionEffective() : '') == $r; @endphp
                            <option value="{{ $r }}" {{ $selected ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                    @if($isRegional)
                        <input type="hidden" name="region" value="{{ auth()->user()->getRegionEffective() }}">
                    @endif
                    @error('region')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Service</label>
                    <select name="service_id" class="form-select @error('service_id') is-invalid @enderror">
                        <option value="">-- Sélectionner --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" {{ old('service_id') == $s->id ? 'selected' : '' }}>{{ $s->nom }} ({{ $s->region }})</option>
                        @endforeach
                    </select>
                    @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Rôle <span class="text-danger">*</span></label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">-- Sélectionner --</option>
                        @php
                            $roleLabels = ['super_admin'=>'Super Admin','superviseur'=>'Superviseur','agent'=>'Agent'];
                        @endphp
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div class="alert alert-info py-2 mb-0">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Super Admin</strong> : accès total toutes régions &nbsp;|&nbsp;
                            <strong>Admin Région</strong> : gestion de sa région uniquement &nbsp;|&nbsp;
                            <strong>Agent</strong> : saisie terrain et consultation
                        </small>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Créer l'utilisateur</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePasswordStrength() {
    const isLeger = document.getElementById('pw-leger').checked;
    const pwInput = document.getElementById('pw-input');
    pwInput.minLength = isLeger ? 4 : 8;

    document.getElementById('pw-fort-info').classList.toggle('d-none', isLeger);
    document.getElementById('pw-leger-info').classList.toggle('d-none', !isLeger);

    document.getElementById('pw-bar-wrap').style.display = pwInput.value ? 'block' : 'none';
    checkStrengthBar();
}

function togglePwVisibility() {
    const input = document.getElementById('pw-input');
    const eye   = document.getElementById('pw-eye');
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkStrengthBar() {
    const val    = document.getElementById('pw-input').value;
    const isLeger = document.getElementById('pw-leger').checked;
    const bar    = document.getElementById('pw-bar');
    const wrap   = document.getElementById('pw-bar-wrap');
    const min    = isLeger ? 4 : 8;

    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';

    let score = 0;
    if (val.length >= min)       score++;
    if (val.length >= min + 4)   score++;
    if (/[A-Z]/.test(val))       score++;
    if (/[0-9]/.test(val))       score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const pct    = Math.min(100, Math.round((score / 5) * 100));
    const colors = ['#e63946', '#f4a261', '#f4a261', '#2d6a4f', '#2d6a4f', '#1a3a5c'];
    bar.style.width = pct + '%';
    bar.style.background = colors[score] ?? '#2d6a4f';
}

document.getElementById('pw-input').addEventListener('input', checkStrengthBar);

document.getElementById('pw-confirm').addEventListener('input', function () {
    const pw  = document.getElementById('pw-input').value;
    const msg = document.getElementById('pw-match-msg');
    if (!this.value) { msg.className = 'd-none'; return; }
    if (this.value === pw) {
        msg.textContent = '✓ Les mots de passe correspondent';
        msg.className = 'small text-success mt-1';
    } else {
        msg.textContent = '✗ Les mots de passe ne correspondent pas';
        msg.className = 'small text-danger mt-1';
    }
});
</script>
@endpush
