<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PDM Revisi Kelompok E: Reporting / Tutup Periode & Audit Trail
     */
    public function up(): void
    {
        // 15. ringkasan_periode_penjualan
        Schema::create('ringkasan_periode_penjualan', function (Blueprint $table) {
            $table->uuid('id_ringkasan')->primary();
            $table->foreignUuid('id_client')->constrained('client', 'id_client')->cascadeOnDelete();
            $table->string('nama_client', 150); // Snapshot nama cabang
            $table->string('total_obat_terjual', 50)->default('0'); // Format sediaan / total unit
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->integer('total_pesanan')->default(0);
            $table->decimal('total_pendapatan', 15, 2)->default(0.00);
            $table->integer('total_retur')->default(0);
            $table->boolean('status_terkunci')->default(false); // Flag tutup periode
            $table->timestamp('dibuat_pada')->useCurrent();

            // Constraint idempoten tutup periode per client & rentang tanggal
            $table->unique(['id_client', 'periode_mulai', 'periode_selesai'], 'uq_ringkasan_client_periode');
        });

        // 16. log_audit
        Schema::create('log_audit', function (Blueprint $table) {
            $table->uuid('id_audit')->primary();
            $table->foreignUuid('id_aktor')->nullable()->constrained('pengguna', 'id_pengguna')->nullOnDelete();
            $table->string('aksi', 100); // login, update_stok, verifikasi_resep, tutup_periode, dll
            $table->string('nama_entitas', 100);
            $table->uuid('id_entitas')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['aksi', 'nama_entitas']);
            $table->index(['id_aktor', 'dibuat_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_audit');
        Schema::dropIfExists('ringkasan_periode_penjualan');
    }
};
