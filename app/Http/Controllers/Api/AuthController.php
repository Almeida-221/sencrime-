<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ── Connexion classique email/password (admin web) ─────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        $user = Auth::user();

        if (!$user->actif) {
            Auth::logout();
            return response()->json(['message' => 'Compte désactivé'], 403);
        }

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    // ── Étape 1 : vérifier numéro de téléphone ─────────────────────
    public function verifyPhone(Request $request)
    {
        $request->validate(['telephone' => 'required|string']);

        $user = User::where('telephone', $request->telephone)->first();

        if (!$user) {
            return response()->json(['message' => 'Numéro non reconnu'], 404);
        }

        if (!$user->actif) {
            return response()->json(['message' => 'Compte désactivé'], 403);
        }

        return response()->json([
            'name'    => $user->name,
            'pin_set' => !is_null($user->pin),
        ]);
    }

    // ── Étape 2a : première connexion — créer le PIN ───────────────
    public function setupPin(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
            'pin'       => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        $user = User::where('telephone', $request->telephone)->first();

        if (!$user) {
            return response()->json(['message' => 'Numéro non reconnu'], 404);
        }

        if (!$user->actif) {
            return response()->json(['message' => 'Compte désactivé'], 403);
        }

        if (!is_null($user->pin)) {
            return response()->json(['message' => 'PIN déjà configuré'], 409);
        }

        $user->pin = Hash::make($request->pin);
        $user->save();

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user->load('service')),
        ]);
    }

    // ── Étape 2b : connexion avec PIN ──────────────────────────────
    public function loginPin(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
            'pin'       => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        $user = User::where('telephone', $request->telephone)->first();

        if (!$user || is_null($user->pin)) {
            return response()->json(['message' => 'Numéro non reconnu'], 404);
        }

        if (!$user->actif) {
            return response()->json(['message' => 'Compte désactivé'], 403);
        }

        if (!Hash::check($request->pin, $user->pin)) {
            return response()->json(['message' => 'PIN incorrect'], 401);
        }

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user->load('service')),
        ]);
    }

    // ── Changer le PIN (authentifié) ───────────────────────────────
    public function changePin(Request $request)
    {
        $request->validate([
            'pin_actuel'  => 'required|string|size:4',
            'nouveau_pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        $user = $request->user();

        if (is_null($user->pin) || !Hash::check($request->pin_actuel, $user->pin)) {
            return response()->json(['message' => 'PIN actuel incorrect'], 401);
        }

        $user->pin = Hash::make($request->nouveau_pin);
        $user->save();

        return response()->json(['message' => 'PIN modifié avec succès']);
    }

    // ── Changer le numéro de téléphone (authentifié) ───────────────
    public function changeTelephone(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string|unique:users,telephone,' . $request->user()->id,
        ]);

        $request->user()->update(['telephone' => $request->telephone]);

        return response()->json(['message' => 'Numéro mis à jour', 'telephone' => $request->telephone]);
    }

    // ── Déconnexion ────────────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    public function me(Request $request)
    {
        return response()->json($this->formatUser($request->user()->load('service')));
    }

    private function formatUser($user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'telephone'  => $user->telephone,
            'region'     => $user->region,
            'service_id' => $user->service_id,
            'service'    => $user->service ? [
                'id'     => $user->service->id,
                'nom'    => $user->service->nom,
                'region' => $user->service->region,
            ] : null,
            'roles'  => $user->getRoleNames(),
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
        ];
    }
}
