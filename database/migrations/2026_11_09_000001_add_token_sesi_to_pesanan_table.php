<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pesanan', 'token_sesi')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->string('token_sesi', 64)->nullable()->after('metode_layanan')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pesanan', 'token_sesi')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->dropIndex(['token_sesi']);
                $table->dropColumn('token_sesi');
            });
        }
    }
};
