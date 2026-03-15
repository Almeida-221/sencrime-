<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('infractions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_dossier')->unique();
            $table->foreignId('type_infraction_id')->constrained('types_infractions')->onDelete('restrict');
            $table->date('date_infraction');
            $table->string('localite');
            $table->string('region')->nullable();
            $table->text('description');
            $table->string('nom_contrevenant')->nullable();
            $table->string('prenom_contrevenant')->nullable();
            $table->date('date_naissance_contrevenant')->nullable();
            $table->string('nationalite_contrevenant')->nullable();
            $table->string('adresse_contrevenant')->nullable();
            $table->string('statut')->default('ouvert'); // ouvert, en_cours, ferme, classe
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('infractions');
    }
};
