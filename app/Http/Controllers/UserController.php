<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    const REGIONS = [
        'Dakar','Thiès','Diourbel','Fatick','Kaolack','Kaffrine',
        'Louga','Saint-Louis','Matam','Tambacounda','Kédougou',
        'Kolda','Sédhiou','Ziguinchor',
    ];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin')->only(['destroy']);
        $this->middleware('role:super_admin|superviseur')->only(['create', 'store', 'edit', 'update']);
    }

    public function index(Request $request)
    {
        $query = User::with(['roles', 'service']);

        // superviseur/admin_region ne voit que les utilisateurs de sa région
        if (auth()->user()->hasRole(['superviseur'])) {
            $region = auth()->user()->getRegionEffective();
            $query->where(function ($q) use ($region) {
                $q->where('region', $region)
                  ->orWhereHas('service', fn($sq) => $sq->where('region', $region));
            });
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        $users = $query->paginate(15);
        $roles = Role::all();
        $regions = self::REGIONS;
        return view('users.index', compact('users', 'roles', 'regions'));
    }

    public function create()
    {
        $roles = $this->getRolesForCreation();
        $services = Service::where('actif', true)->orderBy('nom')->get();
        $regions = self::REGIONS;
        return view('users.create', compact('roles', 'services', 'regions'));
    }

    public function store(Request $request)
    {
        $pwMin = $request->password_strength === 'leger' ? 4 : 8;

        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => "required|string|min:{$pwMin}|confirmed",
            'telephone'  => 'nullable|string|max:20',
            'service_id' => 'nullable|exists:services,id',
            'region'     => 'nullable|string|max:100',
            'role'       => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'telephone'      => $request->telephone,
            'service_id'     => $request->service_id,
            'region'         => $request->region,
            'actif'          => true,
            'modules_actifs' => $request->modules_actifs ?? ['accident', 'infraction', 'immigration'],
        ]);

        // Sécurité : superviseur/admin_region ne peut attribuer que le rôle agent
        $allowedRoles = collect($this->getRolesForCreation())->pluck('name')->toArray();
        $role = in_array($request->role, $allowedRoles) ? $request->role : 'agent';

        // Pour superviseur/admin_region : forcer la région de l'utilisateur créé
        if (auth()->user()->hasRole(['superviseur'])) {
            $user->region = auth()->user()->getRegionEffective();
            $user->save();
        }

        $user->assignRole($role);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function show(User $user)
    {
        $user->load(['roles', 'service']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorizeRegionAccess($user);
        $roles = $this->getRolesForCreation();
        $services = Service::where('actif', true)->orderBy('nom')->get();
        $regions = self::REGIONS;
        return view('users.edit', compact('user', 'roles', 'services', 'regions'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeRegionAccess($user);

        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'   => 'nullable|string|min:8|confirmed',
            'telephone'  => 'nullable|string|max:20',
            'service_id' => 'nullable|exists:services,id',
            'region'     => 'nullable|string|max:100',
            'role'       => 'required|exists:roles,name',
        ]);

        $data = [
            'name'           => $request->name,
            'email'          => $request->email,
            'telephone'      => $request->telephone,
            'service_id'     => $request->service_id,
            'region'         => $request->region,
            'actif'          => $request->has('actif'),
            'modules_actifs' => $request->modules_actifs ?? ['accident', 'infraction', 'immigration'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        if ($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            return redirect()->route('users.index')->with('error', 'Impossible de supprimer un Super Administrateur.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    private function getRolesForCreation()
    {
        if (auth()->user()->hasRole(['super_admin'])) {
            return Role::all();
        }
        return Role::whereIn('name', ['agent'])->get();
    }

    private function authorizeRegionAccess(User $user): void
    {
        if (auth()->user()->hasRole(['superviseur'])) {
            $myRegion = auth()->user()->getRegionEffective();
            $targetRegion = $user->getRegionEffective();
            if ($myRegion && $myRegion !== $targetRegion) {
                abort(403, 'Accès non autorisé à cet utilisateur.');
            }
        }
    }
}
