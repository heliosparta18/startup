<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konsultasi', function (Blueprint $table) {
            $table->uuid('id_konsultasi')->primary();
            $table->foreignUuid('id_client')->constrained('client', 'id_client')->cascadeOnDelete();
            $table->foreignUuid('id_pengguna')->nullable()->constrained('pengguna', 'id_pengguna')->nullOnDelete();
            $table->string('token_sesi', 64)->nullable()->index();
            $table->string('nama_pasien', 150);
            $table->string('status', 30)->default('aktif');
            $table->text('pesan_terakhir')->nullable();
            $table->timestamp('pesan_terakhir_pada')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->index(['id_client', 'status']);
            $table->index('id_pengguna');
        });

        Schema::create('pesan_konsultasi', function (Blueprint $table) {
            $table->uuid('id_pesan')->primary();
            $table->foreignUuid('id_konsultasi')->constrained('konsultasi', 'id_konsultasi')->cascadeOnDelete();
            $table->foreignUuid('id_pengguna')->nullable()->constrained('pengguna', 'id_pengguna')->nullOnDelete();
            $table->string('pengirim_tipe', 20);
            $table->text('pesan');
            $table->string('lampiran_url', 255)->nullable();
            $table->string('lampiran_tipe', 50)->nullable();
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['id_konsultasi', 'dibuat_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesan_konsultasi');
        Schema::dropIfExists('konsultasi');
    }
};
