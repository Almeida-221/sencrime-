<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\TypeInfraction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ─── Permissions ────────────────────────────────────────────
        $permissions = [
            // Statistiques
            'voir_statistiques',
            // Cas (infractions, accidents, amendes, immigration)
            'ajouter_cas',
            'modifier_cas',
            'supprimer_cas',
            // Agents
            'voir_agents',
            'creer_agents',
            'modifier_agents',
            'supprimer_agents',
            // Services
            'voir_services',
            'gerer_services',
            // Types infractions
            'voir_types_infractions',
            'gerer_types_infractions',
            // Services rétribués
            'voir_services_retribues',
            'gerer_services_retribues',
            // Utilisateurs
            'voir_utilisateurs',
            'creer_utilisateurs',
            'modifier_utilisateurs',
            'supprimer_utilisateurs',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ─── Rôles ──────────────────────────────────────────────────

        // Super Admin : accès total, toutes les régions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Superviseur (= Admin Région) : gère sa région uniquement
        $superviseurPermissions = [
            'voir_statistiques',
            'ajouter_cas', 'modifier_cas', 'supprimer_cas',
            'voir_agents', 'creer_agents', 'modifier_agents',
            'voir_services', 'gerer_services',
            'voir_types_infractions',
            'voir_services_retribues', 'gerer_services_retribues',
            'voir_utilisateurs', 'creer_utilisateurs', 'modifier_utilisateurs',
        ];

        $superviseur = Role::firstOrCreate(['name' => 'superviseur', 'guard_name' => 'web']);
        $superviseur->syncPermissions($superviseurPermissions);

        // Agent : saisie terrain, consultation de ses données
        $agentRole = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
        $agentRole->syncPermissions([
            'voir_statistiques',
            'ajouter_cas', 'modifier_cas',
            'voir_agents',
            'voir_services',
            'voir_types_infractions',
        ]);

        // Transporteur / Ambulance : gestion des demandes de transport
        $transporteurRole = Role::firstOrCreate(['name' => 'transporteur', 'guard_name' => 'web']);
        $transporteurRole->syncPermissions(['voir_statistiques']);

        // ─── Comptes par défaut ─────────────────────────────────────

        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@sencrime.sn'],
            [
                'name'     => 'Super Administrateur',
                'password' => Hash::make('SuperAdmin@1234'),
                'actif'    => true,
            ]
        );
        $superAdminUser->syncRoles(['super_admin']);

        $superviseurDakar = User::firstOrCreate(
            ['email' => 'superviseur.dakar@sencrime.sn'],
            [
                'name'     => 'Superviseur Dakar',
                'password' => Hash::make('Superviseur@1234'),
                'region'   => 'Dakar',
                'actif'    => true,
            ]
        );
        $superviseurDakar->syncRoles(['superviseur']);

        $agentUser = User::firstOrCreate(
            ['email' => 'agent@sencrime.sn'],
            [
                'name'     => 'Agent Terrain',
                'password' => Hash::make('Agent@1234'),
                'region'   => 'Dakar',
                'actif'    => true,
            ]
        );
        $agentUser->syncRoles(['agent']);

        $transporteurUser = User::firstOrCreate(
            ['email' => 'transporteur@sencrime.sn'],
            [
                'name'     => 'Transporteur Ambulance',
                'password' => Hash::make('Transport@1234'),
                'region'   => 'Dakar',
                'actif'    => true,
            ]
        );
        $transporteurUser->syncRoles(['transporteur']);

        // ─── Services ───────────────────────────────────────────────
        $services = [
            ['nom' => 'Brigade Criminelle',                  'code' => 'BC-DKR', 'localite' => 'Dakar',       'region' => 'Dakar'],
            ['nom' => 'Brigade de Gendarmerie de Thiès',     'code' => 'BG-THS', 'localite' => 'Thiès',       'region' => 'Thiès'],
            ['nom' => 'Commissariat Central de Saint-Louis', 'code' => 'CC-SL',  'localite' => 'Saint-Louis', 'region' => 'Saint-Louis'],
            ['nom' => 'Brigade de Ziguinchor',               'code' => 'BG-ZIG', 'localite' => 'Ziguinchor',  'region' => 'Ziguinchor'],
            ['nom' => 'Commissariat de Kaolack',             'code' => 'CK-KAO', 'localite' => 'Kaolack',     'region' => 'Kaolack'],
        ];

        foreach ($services as $serviceData) {
            Service::firstOrCreate(['code' => $serviceData['code']], $serviceData);
        }

        // ─── Types d'infractions ────────────────────────────────────
        $typesInfractions = [
            ['nom' => 'Vol simple',               'code' => 'VOL-S',  'categorie' => 'delit',         'amende_min' => 50000,   'amende_max' => 500000],
            ['nom' => 'Vol avec violence',         'code' => 'VOL-V',  'categorie' => 'crime',         'amende_min' => 500000,  'amende_max' => 5000000],
            ['nom' => 'Trafic de drogue',          'code' => 'DRUG',   'categorie' => 'crime',         'amende_min' => 1000000, 'amende_max' => 10000000],
            ['nom' => 'Agression physique',        'code' => 'AGR-P',  'categorie' => 'delit',         'amende_min' => 100000,  'amende_max' => 1000000],
            ['nom' => 'Fraude',                    'code' => 'FRAUD',  'categorie' => 'delit',         'amende_min' => 200000,  'amende_max' => 2000000],
            ['nom' => 'Homicide involontaire',     'code' => 'HOM-I',  'categorie' => 'crime',         'amende_min' => 2000000, 'amende_max' => 20000000],
            ['nom' => 'Conduite en état d\'ivresse','code' => 'CEI',   'categorie' => 'contravention', 'amende_min' => 25000,   'amende_max' => 150000],
            ['nom' => 'Excès de vitesse',          'code' => 'EXV',    'categorie' => 'contravention', 'amende_min' => 10000,   'amende_max' => 75000],
            ['nom' => 'Escroquerie',               'code' => 'ESC',    'categorie' => 'delit',         'amende_min' => 150000,  'amende_max' => 1500000],
            ['nom' => 'Cybercriminalité',          'code' => 'CYBER',  'categorie' => 'crime',         'amende_min' => 500000,  'amende_max' => 5000000],
        ];

        foreach ($typesInfractions as $typeData) {
            TypeInfraction::firstOrCreate(['code' => $typeData['code']], $typeData);
        }

        $this->command->info('✅ Base de données initialisée avec succès!');
        $this->command->info('─────────────────────────────────────────');
        $this->command->info('Super Admin  : superadmin@sencrime.sn / SuperAdmin@1234');
        $this->command->info('Superviseur  : superviseur.dakar@sencrime.sn / Superviseur@1234');
        $this->command->info('Agent        : agent@sencrime.sn / Agent@1234');
    }
}
