<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('amendes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_amende')->unique();
            $table->foreignId('infraction_id')->nullable()->constrained('infractions')->onDelete('set null');
            $table->foreignId('type_infraction_id')->nullable()->constrained('types_infractions')->onDelete('set null');
            $table->date('date_amende');
            $table->string('nom_contrevenant');
            $table->string('prenom_contrevenant')->nullable();
            $table->string('adresse_contrevenant')->nullable();
            $table->string('telephone_contrevenant')->nullable();
            $table->decimal('montant', 12, 2);
            $table->string('statut_paiement')->default('impaye'); // impaye, partiel, paye
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->date('date_paiement')->nullable();
            $table->date('date_echeance')->nullable();
            $table->string('mode_paiement')->nullable(); // especes, virement, cheque
            $table->string('reference_paiement')->nullable();
            $table->string('localite')->nullable();
            $table->string('region')->nullable();
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
        Schema::dropIfExists('amendes');
    }
};
