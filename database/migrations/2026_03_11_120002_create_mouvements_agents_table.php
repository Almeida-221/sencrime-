<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mouvements_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->foreignId('service_origine_id')->nullable()->constrained('services')->onDelete('set null');
            $table->foreignId('service_destination_id')->nullable()->constrained('services')->onDelete('set null');
            $table->string('type_mouvement'); // affectation, mutation, detachement, retour
            $table->date('date_mouvement');
            $table->string('motif')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mouvements_agents');
    }
};
