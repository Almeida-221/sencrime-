<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->enum('type', ['text', 'audio'])->default('text')->after('contenu');
            $table->string('fichier')->nullable()->after('type'); // chemin storage audio
            $table->unsignedSmallInteger('duree')->nullable()->after('fichier'); // durée en secondes
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['type', 'fichier', 'duree']);
        });
    }
};
