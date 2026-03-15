<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accident_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accident_id')->constrained()->onDelete('cascade');
            $table->string('chemin');
            $table->string('nom_original')->nullable();
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accident_photos');
    }
};
