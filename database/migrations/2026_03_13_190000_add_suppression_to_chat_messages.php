<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('supprime_pour_tous')->default(false)->after('contenu');
            $table->json('supprimes_pour')->nullable()->after('supprime_pour_tous');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['supprime_pour_tous', 'supprimes_pour']);
        });
    }
};
