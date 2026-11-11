<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                if (! Schema::hasColumn('pesanan', 'slot_waktu')) {
                    $table->string('slot_waktu', 100)->nullable()->after('metode_layanan');
                }
                if (! Schema::hasColumn('pesanan', 'nomor_antrean')) {
                    $table->string('nomor_antrean', 50)->nullable()->after('slot_waktu');
                }
                if (! Schema::hasColumn('pesanan', 'catatan_pesanan')) {
                    $table->text('catatan_pesanan')->nullable()->after('nomor_antrean');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                if (Schema::hasColumn('pesanan', 'catatan_pesanan')) {
                    $table->dropColumn('catatan_pesanan');
                }
                if (Schema::hasColumn('pesanan', 'nomor_antrean')) {
                    $table->dropColumn('nomor_antrean');
                }
                if (Schema::hasColumn('pesanan', 'slot_waktu')) {
                    $table->dropColumn('slot_waktu');
                }
            });
        }
    }
};
