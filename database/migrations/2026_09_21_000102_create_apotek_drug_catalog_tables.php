<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PDM Revisi Kelompok B: Katalog Obat & Multi-Klien Inventory
     */
    public function up(): void
    {
        // 7. kategori_obat
        Schema::create('kategori_obat', function (Blueprint $table) {
            $table->uuid('id_kategori')->primary();
            $table->string('nama_kategori', 100);
            $table->boolean('wajib_resep')->default(false);
            $table->text('deskripsi')->nullable();
            $table->foreignUuid('id_client')->nullable()->constrained('client', 'id_client')->nullOnDelete();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['wajib_resep']);
        });

        // 8. obat
        Schema::create('obat', function (Blueprint $table) {
            $table->uuid('id_obat')->primary();
            $table->string('nama_obat', 150);
            $table->string('merek', 100)->nullable();
            $table->string('jenis_sediaan', 50)->default('tablet'); // tablet, sirup, kapsul, salep, dll
            $table->integer('jumlah_obat')->default(0);
            $table->foreignUuid('id_kategori')->constrained('kategori_obat', 'id_kategori')->cascadeOnDelete();
            $table->decimal('harga', 12, 2);
            $table->date('expired');
            $table->string('batch_number', 50)->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->index('expired');
            $table->index(['id_kategori', 'nama_obat']);
        });

        // 9. stok_client
        Schema::create('stok_client', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_obat')->constrained('obat', 'id_obat')->cascadeOnDelete();
            $table->foreignUuid('id_client')->constrained('client', 'id_client')->cascadeOnDelete();
            $table->integer('jumlah_stok')->default(0);
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['id_obat', 'id_client'], 'uq_stok_obat_client');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_client');
        Schema::dropIfExists('obat');
        Schema::dropIfExists('kategori_obat');
    }
};
