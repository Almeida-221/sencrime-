<?php

namespace App\Traits;

use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;

trait ScopeByRole
{
    /** Rôles ayant accès global (toutes régions, tous services) */
    private function isGlobalAdmin(): bool
    {
        return auth()->user()->hasRole(['super_admin']);
    }

    /** Rôles agissant comme admin de région (superviseur = admin_region) */
    private function isRegionalAdmin(): bool
    {
        return auth()->user()->hasRole(['superviseur']);
    }

    /**
     * Applique le filtre région/service selon le rôle de l'utilisateur connecté.
     *
     * - Super Admin / Admin → aucun filtre
     * - Superviseur / Admin Région → filtre par région
     * - Agent → filtre par service_id (ou région si pas de service)
     */
    protected function applyScopeFilters(
        Builder $query,
        string $regionColumn  = 'region',
        string $serviceColumn = 'service_id'
    ): Builder {
        $user = auth()->user();

        if ($this->isGlobalAdmin()) {
            return $query;
        }

        if ($this->isRegionalAdmin()) {
            $region = $user->getRegionEffective();
            if ($region) {
                $query->where($regionColumn, $region);
            }
            return $query;
        }

        // Agent (et tout autre rôle)
        if ($user->service_id) {
            $query->where($serviceColumn, $user->service_id);
        } elseif ($user->getRegionEffective()) {
            $query->where($regionColumn, $user->getRegionEffective());
        }

        return $query;
    }

    /**
     * Retourne les services visibles selon le rôle de l'utilisateur.
     */
    protected function scopedServices()
    {
        $user  = auth()->user();
        $query = Service::where('actif', true)->orderBy('nom');

        if ($this->isGlobalAdmin()) {
            return $query->get();
        }

        if ($this->isRegionalAdmin()) {
            $region = $user->getRegionEffective();
            if ($region) {
                $query->where('region', $region);
            }
            return $query->get();
        }

        // Agent
        if ($user->service_id) {
            $query->where('id', $user->service_id);
        } elseif ($user->getRegionEffective()) {
            $query->where('region', $user->getRegionEffective());
        }

        return $query->get();
    }
}
