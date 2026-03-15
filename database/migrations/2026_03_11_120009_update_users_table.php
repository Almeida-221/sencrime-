<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone')->nullable()->after('email');
            $table->foreignId('service_id')->nullable()->after('telephone')->constrained('services')->onDelete('set null');
            $table->boolean('actif')->default(true)->after('service_id');
            $table->string('avatar')->nullable()->after('actif');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['telephone', 'service_id', 'actif', 'avatar']);
        });
    }
};
