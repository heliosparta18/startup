<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom catatan pada tabel resep untuk pesan/kebutuhan pelanggan saat unggah.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('resep', 'catatan')) {
            Schema::table('resep', function (Blueprint $table) {
                $table->text('catatan')->nullable()->after('nama_pasien');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('resep', 'catatan')) {
            Schema::table('resep', function (Blueprint $table) {
                $table->dropColumn('catatan');
            });
        }
    }
};
