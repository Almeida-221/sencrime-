<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accidents', function (Blueprint $table) {
            $table->string('note_vocale')->nullable()->after('causes'); // chemin fichier audio
        });

        Schema::table('infractions', function (Blueprint $table) {
            $table->string('note_vocale')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('accidents', function (Blueprint $table) {
            $table->dropColumn('note_vocale');
        });
        Schema::table('infractions', function (Blueprint $table) {
            $table->dropColumn('note_vocale');
        });
    }
};
