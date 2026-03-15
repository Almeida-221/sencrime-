<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_transport', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accident_id')->constrained()->onDelete('cascade');
            $table->foreignId('demandeur_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transporteur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('statut', ['en_attente', 'acceptee', 'en_cours', 'terminee', 'annulee'])->default('en_attente');
            $table->decimal('latitude_depart', 10, 8)->nullable();
            $table->decimal('longitude_depart', 11, 8)->nullable();
            $table->decimal('latitude_arrivee', 10, 8)->nullable();
            $table->decimal('longitude_arrivee', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('acceptee_at')->nullable();
            $table->timestamp('terminee_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_transport');
    }
};
