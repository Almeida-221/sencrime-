<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_transport', function (Blueprint $table) {
            $table->decimal('lat_transporteur', 10, 7)->nullable()->after('longitude_arrivee');
            $table->decimal('lng_transporteur', 10, 7)->nullable()->after('lat_transporteur');
            $table->timestamp('position_updated_at')->nullable()->after('lng_transporteur');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_transport', function (Blueprint $table) {
            $table->dropColumn(['lat_transporteur', 'lng_transporteur', 'position_updated_at']);
        });
    }
};
