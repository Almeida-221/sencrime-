@extends('layouts.app')
@section('title', 'Profil Utilisateur')
@section('page-title', 'Profil Utilisateur')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection
@section('content')
@php
    $roleColors = ['super_admin'=>'danger','superviseur'=>'info','agent'=>'success'];
    $roleLabels = ['super_admin'=>'Super Admin','superviseur'=>'Superviseur','agent'=>'Agent'];
@endphp
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:80px;height:80px;background:linear-gradient(135deg,#1a3a5c,#2d6a9f);">
                    <i class="fas fa-user fa-2x text-white"></i>
                </div>
                <h5 class="fw-bold">{{ $user->name }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                <div class="mb-1">
                    @foreach($user->roles as $role)
                        <span class="badge bg-{{ $roleColors[$role->name] ?? 'secondary' }} mb-1">
                            <i class="fas fa-shield-alt me-1"></i>{{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                        </span>
                    @endforeach
                </div>
                @if($user->region)
                <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>{{ $user->region }}</p>
                @endif
                <span class="badge bg-{{ $user->actif ? 'success' : 'secondary' }}">
                    {{ $user->actif ? 'Actif' : 'Inactif' }}
                </span>

                @if(auth()->user()->hasRole(['super_admin','admin','admin_region']))
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                    @if(auth()->user()->hasRole(['super_admin','admin']) && $user->id !== auth()->id() && !$user->hasRole('super_admin'))
                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash me-1"></i>Supprimer</button>
                    </form>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-key me-2"></i>Permissions</div>
            <div class="card-body">
                @php $perms = $user->getAllPermissions(); @endphp
                @if($perms->count() > 0)
                    @foreach($perms as $perm)
                        <span class="badge bg-light text-dark border mb-1" style="font-size:0.7rem;">{{ $perm->name }}</span>
                    @endforeach
                @else
                    <p class="text-muted small mb-0">Aucune permission directe</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Informations du compte</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr><th>Nom:</th><td>{{ $user->name }}</td></tr>
                            <tr><th>Email:</th><td>{{ $user->email }}</td></tr>
                            <tr><th>Téléphone:</th><td>{{ $user->telephone ?? '-' }}</td></tr>
                            <tr><th>Membre depuis:</th><td>{{ $user->created_at->format('d/m/Y') }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th>Rôle:</th>
                                <td>{{ $user->roles->map(fn($r) => $roleLabels[$r->name] ?? ucfirst($r->name))->join(', ') ?: '-' }}</td>
                            </tr>
                            <tr><th>Région:</th><td>{{ $user->getRegionEffective() ?? '-' }}</td></tr>
                            <tr><th>Service:</th><td>{{ $user->service->nom ?? '-' }}</td></tr>
                            <tr><th>Statut:</th><td><span class="badge bg-{{ $user->actif ? 'success' : 'secondary' }}">{{ $user->actif ? 'Actif' : 'Inactif' }}</span></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($user->id === auth()->id())
        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-user-circle me-2"></i>Mon compte</div>
            <div class="card-body">
                <p class="text-muted mb-2">Vous êtes connecté en tant que <strong>{{ $user->name }}</strong>.</p>
                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i>Modifier mon profil
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
