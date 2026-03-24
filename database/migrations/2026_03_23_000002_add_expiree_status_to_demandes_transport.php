<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL : modifier l'enum pour ajouter 'expiree'
        DB::statement("ALTER TABLE demandes_transport MODIFY COLUMN statut ENUM('en_attente','acceptee','en_cours','terminee','annulee','expiree') NOT NULL DEFAULT 'en_attente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE demandes_transport MODIFY COLUMN statut ENUM('en_attente','acceptee','en_cours','terminee','annulee') NOT NULL DEFAULT 'en_attente'");
    }
};
