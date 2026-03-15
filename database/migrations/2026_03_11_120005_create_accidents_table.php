<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accidents', function (Blueprint $table) {
            $table->id();
            $table->string('numero_rapport')->unique();
            $table->date('date_accident');
            $table->time('heure_accident')->nullable();
            $table->string('localite');
            $table->string('region')->nullable();
            $table->string('lieu_exact')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('type_accident'); // collision, renversement, chute, etc.
            $table->text('description');
            $table->integer('nombre_victimes')->default(0);
            $table->integer('nombre_blesses')->default(0);
            $table->integer('nombre_morts')->default(0);
            $table->string('gravite')->default('leger'); // leger, grave, mortel
            $table->text('causes')->nullable();
            $table->string('statut')->default('ouvert'); // ouvert, en_cours, ferme
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
        Schema::dropIfExists('accidents');
    }
};
