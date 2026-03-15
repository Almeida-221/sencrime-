<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('services_retribues', function (Blueprint $table) {
            $table->id();
            $table->string('numero_mission')->unique();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('type_mission'); // escorte, garde, surveillance, etc.
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('localite')->nullable();
            $table->string('region')->nullable();
            $table->string('client_nom');
            $table->string('client_telephone')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_adresse')->nullable();
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->string('statut_paiement')->default('impaye'); // impaye, partiel, paye
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->date('date_paiement')->nullable();
            $table->string('mode_paiement')->nullable();
            $table->string('statut')->default('planifie'); // planifie, en_cours, termine, annule
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_retribue_agent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_retribue_id')->constrained('services_retribues')->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->string('role')->nullable();
            $table->decimal('remuneration', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_retribue_agent');
        Schema::dropIfExists('services_retribues');
    }
};
