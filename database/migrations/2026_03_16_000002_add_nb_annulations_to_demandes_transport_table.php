<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_transport', function (Blueprint $table) {
            $table->unsignedTinyInteger('nb_annulations')->default(0)->after('terminee_at');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_transport', function (Blueprint $table) {
            $table->dropColumn('nb_annulations');
        });
    }
};
