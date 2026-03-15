<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('immigrations_clandestines', function (Blueprint $table) {
            $table->id();
            $table->string('numero_cas')->unique();
            $table->date('date_interception');
            $table->string('localite');
            $table->string('region')->nullable();
            $table->string('lieu_interception')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('nombre_personnes')->default(1);
            $table->integer('nombre_hommes')->default(0);
            $table->integer('nombre_femmes')->default(0);
            $table->integer('nombre_mineurs')->default(0);
            $table->string('nationalites')->nullable(); // JSON ou liste séparée par virgule
            $table->string('pays_origine')->nullable();
            $table->string('pays_destination')->nullable();
            $table->string('moyen_transport')->nullable(); // pirogue, vehicule, a_pied, etc.
            $table->string('type_operation'); // interception, arrestation, rapatriement
            $table->string('statut')->default('ouvert'); // ouvert, en_cours, ferme, rapatrie
            $table->text('description')->nullable();
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
        Schema::dropIfExists('immigrations_clandestines');
    }
};
