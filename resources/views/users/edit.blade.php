@extends('layouts.app')
@section('title', 'Modifier Utilisateur')
@section('page-title', 'Modifier un Utilisateur')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-user-edit me-2"></i>Modifier : {{ $user->name }}</div>
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf @method('PUT')
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Informations personnelles</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nouveau mot de passe <small class="text-muted fw-normal">(laisser vide pour ne pas changer)</small></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Confirmer mot de passe</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Complexité du mot de passe</label>
                    <select name="password_strength" class="form-select">
                        <option value="fort">Fort (min. 8 caractères)</option>
                        <option value="leger">Léger (min. 4 caractères)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Téléphone</label>
                    <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror" value="{{ old('telephone', $user->telephone) }}">
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
                            <option value="{{ $r }}" {{ old('region', $user->region) == $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                    @if($isRegional)
                        <input type="hidden" name="region" value="{{ $user->region }}">
                    @endif
                    @error('region')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Service</label>
                    <select name="service_id" class="form-select @error('service_id') is-invalid @enderror">
                        <option value="">-- Sélectionner --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" {{ old('service_id', $user->service_id) == $s->id ? 'selected' : '' }}>{{ $s->nom }} ({{ $s->region }})</option>
                        @endforeach
                    </select>
                    @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Rôle <span class="text-danger">*</span></label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required
                        {{ $user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin') ? 'disabled' : '' }}>
                        <option value="">-- Sélectionner --</option>
                        @php
                            $roleLabels = ['super_admin'=>'Super Admin','superviseur'=>'Superviseur','agent'=>'Agent'];
                        @endphp
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                {{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @if($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin'))
                        <input type="hidden" name="role" value="{{ $user->roles->first()?->name }}">
                    @endif
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="actif" id="actif" {{ old('actif', $user->actif) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="actif">Compte actif</label>
                    </div>
                </div>

                {{-- Modules actifs — visible uniquement si rôle = agent --}}
                @php $isAgent = $user->hasRole('agent'); @endphp
                <div class="col-12" id="modules-section" style="{{ $isAgent ? '' : 'display:none;' }}">
                    <div class="card border-primary border-opacity-25">
                        <div class="card-header bg-primary bg-opacity-10 py-2">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="fas fa-mobile-alt me-2"></i>Modules actifs (Application mobile)
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <p class="text-muted small mb-3">Sélectionnez les modules auxquels cet agent aura accès dans l'application mobile.</p>
                            @php
                                $currentModules = old('modules_actifs', $user->modules_actifs ?? ['accident','infraction','immigration']);
                            @endphp
                            <div class="row g-2">
                                @foreach(['accident' => ['Accidents', 'fa-car-crash', 'danger'], 'infraction' => ['Infractions', 'fa-gavel', 'warning'], 'immigration' => ['Immigration Clandestine', 'fa-passport', 'success']] as $key => $info)
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline border rounded p-2 w-100">
                                        <input class="form-check-input" type="checkbox"
                                               name="modules_actifs[]"
                                               id="module_edit_{{ $key }}"
                                               value="{{ $key }}"
                                               {{ in_array($key, $currentModules) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="module_edit_{{ $key }}">
                                            <i class="fas {{ $info[1] }} text-{{ $info[2] }} me-1"></i>
                                            {{ $info[0] }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Mettre à jour</button>
                <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Afficher/masquer la section modules selon le rôle sélectionné
document.querySelector('select[name="role"]').addEventListener('change', function () {
    document.getElementById('modules-section').style.display =
        this.value === 'agent' ? 'block' : 'none';
});
</script>
@endpush
