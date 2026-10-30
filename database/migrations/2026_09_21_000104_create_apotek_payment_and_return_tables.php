<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PDM Revisi Kelompok D: Pembayaran & Retur Obat
     */
    public function up(): void
    {
        // 13. pembayaran
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->uuid('id_pembayaran')->primary();
            $table->foreignUuid('id_pesanan')->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->string('metode_pembayaran', 50); // transfer, e-wallet, cod, qris
            $table->decimal('jumlah', 12, 2);
            $table->text('url_lampiran')->nullable();
            $table->string('status', 50)->default('menunggu_verifikasi'); // menunggu_verifikasi, lunas, ditolak
            $table->foreignUuid('diverifikasi_oleh')->nullable()->constrained('pengguna', 'id_pengguna')->nullOnDelete();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['id_pesanan', 'status']);
        });

        // 14. permintaan_retur
        Schema::create('permintaan_retur', function (Blueprint $table) {
            $table->uuid('id_retur')->primary();
            $table->foreignUuid('id_pesanan')->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->text('alasan');
            $table->decimal('jumlah_diajukan', 12, 2);
            $table->decimal('jumlah_disetujui', 12, 2)->nullable();
            $table->string('status', 50)->default('diajukan'); // diajukan, disetujui, ditolak
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['id_pesanan', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_retur');
        Schema::dropIfExists('pembayaran');
    }
};
