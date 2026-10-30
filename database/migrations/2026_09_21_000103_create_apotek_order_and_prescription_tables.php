<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PDM Revisi Kelompok C: Transaksi Pesanan, Resep Dokter & Item Snapshot
     */
    public function up(): void
    {
        // 10. pesanan
        Schema::create('pesanan', function (Blueprint $table) {
            $table->uuid('id_pesanan')->primary();
            $table->foreignUuid('id_pengguna')->constrained('pengguna', 'id_pengguna')->cascadeOnDelete();
            $table->foreignUuid('id_client')->constrained('client', 'id_client')->cascadeOnDelete();
            $table->string('nomor_pesanan', 50)->unique();
            $table->string('status', 50)->default('menunggu_verifikasi'); // menunggu_verifikasi, diproses, dikirim, selesai, dibatalkan
            $table->string('metode_layanan', 50)->default('click_collect'); // click_collect, delivery
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->index(['id_client', 'status']);
            $table->index(['id_pengguna', 'status']);
        });

        // 11. resep
        Schema::create('resep', function (Blueprint $table) {
            $table->uuid('id_resep')->primary();
            $table->foreignUuid('id_pesanan')->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->text('url_gambar');
            $table->string('status_verifikasi', 50)->default('menunggu'); // menunggu, disetujui, ditolak
            $table->string('nama_dokter', 150)->nullable();
            $table->string('sip_dokter', 100)->nullable();
            $table->string('nama_pasien', 150)->nullable();
            $table->boolean('sip_valid')->nullable();
            $table->boolean('dosis_valid')->nullable();
            $table->text('catatan_apoteker')->nullable();
            $table->foreignUuid('diverifikasi_oleh')->nullable()->constrained('pengguna', 'id_pengguna')->nullOnDelete();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['status_verifikasi']);
        });

        // 12. item_pesanan
        Schema::create('item_pesanan', function (Blueprint $table) {
            $table->uuid('id_item')->primary();
            $table->foreignUuid('id_pesanan')->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->foreignUuid('id_obat')->constrained('obat', 'id_obat')->cascadeOnDelete();
            $table->integer('kuantitas');
            $table->string('nama_obat', 150); // Snapshot nama obat saat transaksi
            $table->decimal('harga_satuan', 12, 2); // Snapshot harga obat saat transaksi
            $table->decimal('subtotal', 12, 2);
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['id_pesanan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_pesanan');
        Schema::dropIfExists('resep');
        Schema::dropIfExists('pesanan');
    }
};
