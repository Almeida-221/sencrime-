@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des Utilisateurs')
@section('breadcrumb')
    <li class="breadcrumb-item active">Utilisateurs</li>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Nom ou email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">Tous les rôles</option>
                    @foreach($roles as $role)
                        @php $roleLabels = ['super_admin'=>'Super Admin','superviseur'=>'Superviseur','agent'=>'Agent']; @endphp
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(!auth()->user()->hasRole(['superviseur']))
            <div class="col-md-2">
                <select name="region" class="form-select form-select-sm">
                    <option value="">Toutes régions</option>
                    @foreach($regions as $r)
                        <option value="{{ $r }}" {{ request('region') == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Filtrer</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm w-100">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users me-2"></i>Utilisateurs ({{ $users->total() }})</span>
        @if(auth()->user()->hasRole(['super_admin','superviseur']))
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouvel Utilisateur</a>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Région</th>
                        <th>Service</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="fw-bold">{{ $user->name }}</td>
                        <td><small>{{ $user->email }}</small></td>
                        <td><small>{{ $user->region ?? $user->service?->region ?? '-' }}</small></td>
                        <td><small>{{ $user->service->nom ?? '-' }}</small></td>
                        <td>
                            @php
                                $roleColors = [
                                    'super_admin' => 'danger',
                                    'superviseur' => 'info',
                                    'agent'       => 'success',
                                ];
                                $roleLabels = [
                                    'super_admin' => 'Super Admin',
                                    'superviseur' => 'Superviseur',
                                    'agent'       => 'Agent',
                                ];
                            @endphp
                            @foreach($user->roles as $role)
                                <span class="badge bg-{{ $roleColors[$role->name] ?? 'secondary' }}">
                                    {{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge bg-{{ $user->actif ? 'success' : 'secondary' }}">
                                {{ $user->actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i></a>
                            @if(auth()->user()->hasRole(['super_admin','superviseur']))
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning btn-action"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(auth()->user()->hasRole(['super_admin','admin']) && $user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun utilisateur trouvé</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer">{{ $users->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
