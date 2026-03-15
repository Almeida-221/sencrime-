<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('titre');
            $table->text('message');
            $table->string('type', 50)->default('system'); // accident, transport_demande, transport_accepte, transport_termine, infraction, system
            $table->string('icone', 50)->nullable();        // nom icône FontAwesome
            $table->string('couleur', 20)->default('primary'); // primary, danger, warning, success, info
            $table->json('data')->nullable();               // données supplémentaires (ex: id accident)
            $table->string('lien')->nullable();             // URL de redirection optionnelle
            $table->boolean('lu')->default(false);
            $table->timestamp('lu_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'lu']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
