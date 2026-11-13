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
        // 1. Tambah kolom pendukung katalog pada kategori_obat
        Schema::table('kategori_obat', function (Blueprint $table) {
            $table->string('slug', 100)->nullable()->unique()->after('nama_kategori');
            $table->string('icon_url', 255)->nullable()->after('deskripsi');
            $table->integer('urutan')->default(0)->after('icon_url');
        });

        // 2. Buat tabel subkategori_obat
        Schema::create('subkategori_obat', function (Blueprint $table) {
            $table->uuid('id_subkategori')->primary();
            $table->foreignUuid('id_kategori')->constrained('kategori_obat', 'id_kategori')->cascadeOnDelete();
            $table->string('nama_subkategori', 100);
            $table->string('slug', 120)->unique();
            $table->string('icon_url', 255)->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['id_kategori', 'urutan']);
        });

        // 3. Tambah kolom atribut katalog pada obat
        Schema::table('obat', function (Blueprint $table) {
            $table->foreignUuid('id_subkategori')->nullable()->after('id_kategori')
                ->constrained('subkategori_obat', 'id_subkategori')->nullOnDelete();
            $table->string('slug', 200)->nullable()->unique()->after('nama_obat');
            $table->string('satuan_kemasan', 100)->nullable()->after('jenis_sediaan');
            $table->string('badge_status', 50)->default('Obat Bebas')->after('satuan_kemasan');
            $table->string('gambar_url', 500)->nullable()->after('badge_status');
            $table->string('nomor_ijin_edar', 100)->nullable()->after('batch_number');
            $table->text('indikasi')->nullable()->after('nomor_ijin_edar');
            $table->text('deskripsi')->nullable()->after('indikasi');
            $table->text('dosis')->nullable()->after('deskripsi');
            $table->text('komposisi')->nullable()->after('dosis');
            $table->text('aturan_pakai')->nullable()->after('komposisi');

            $table->index(['id_subkategori', 'nama_obat']);
            $table->index('badge_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropForeign(['id_subkategori']);
            $table->dropIndex(['id_subkategori', 'nama_obat']);
            $table->dropIndex(['badge_status']);
            $table->dropColumn([
                'id_subkategori',
                'slug',
                'satuan_kemasan',
                'badge_status',
                'gambar_url',
                'nomor_ijin_edar',
                'indikasi',
                'deskripsi',
                'dosis',
                'komposisi',
                'aturan_pakai',
            ]);
        });

        Schema::dropIfExists('subkategori_obat');

        Schema::table('kategori_obat', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'icon_url', 'urutan']);
        });
    }
};
