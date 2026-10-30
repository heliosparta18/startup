<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PDM Revisi Kelompok A: Klien, Pengguna, dan Hierarki Penugasan Staf
     */
    public function up(): void
    {
        // 1. client (Cabang Apotek Multi-Klien)
        Schema::create('client', function (Blueprint $table) {
            $table->uuid('id_client')->primary();
            $table->string('nama_client', 150);
            $table->text('alamat');
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->index('aktif');
        });

        // 2. pengguna (Users: pelanggan, apoteker, admin, super_admin)
        Schema::create('pengguna', function (Blueprint $table) {
            $table->uuid('id_pengguna')->primary();
            $table->string('nama_lengkap', 150);
            $table->string('peran', 50)->default('pelanggan'); // pelanggan, apoteker, admin, super_admin
            $table->string('telepon_email', 150)->unique();
            $table->string('password')->nullable();
            $table->date('terdaftar_pada')->useCurrent();
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->index(['peran', 'aktif']);
        });

        // 3. super_admin (Admin Pusat / Multi-Klien)
        Schema::create('super_admin', function (Blueprint $table) {
            $table->uuid('id_super_admin')->primary();
            $table->foreignUuid('id_pengguna')->constrained('pengguna', 'id_pengguna')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('email', 150);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 4. admin_client (Pengelola Cabang / Klien Spesifik)
        Schema::create('admin_client', function (Blueprint $table) {
            $table->uuid('id_admin')->primary();
            $table->foreignUuid('id_pengguna')->constrained('pengguna', 'id_pengguna')->cascadeOnDelete();
            $table->foreignUuid('id_client')->constrained('client', 'id_client')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('email', 150);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['id_pengguna', 'id_client']);
        });

        // 5. peran_client_pengguna (Relasi Penugasan Staf / Preferensi Pelanggan Klien)
        Schema::create('peran_client_pengguna', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_pengguna')->constrained('pengguna', 'id_pengguna')->cascadeOnDelete();
            $table->foreignUuid('id_client')->constrained('client', 'id_client')->cascadeOnDelete();
            $table->string('peran', 50); // pelanggan_tetap, staf_klien
            $table->date('ditugaskan_dari');
            $table->date('ditugaskan_sampai')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['id_client', 'peran']);
        });

        // 6. peta_pengawas_staf (Hierarki Pengawas Apoteker dan Staf Teknis Klien)
        Schema::create('peta_pengawas_staf', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_staf')->constrained('pengguna', 'id_pengguna')->cascadeOnDelete();
            $table->foreignUuid('id_pengawas')->constrained('pengguna', 'id_pengguna')->cascadeOnDelete();
            $table->foreignUuid('id_client')->constrained('client', 'id_client')->cascadeOnDelete();
            $table->date('berlaku_dari');
            $table->date('berlaku_sampai')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->unique(['id_staf', 'id_pengawas', 'id_client'], 'uq_pengawas_staf_client');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peta_pengawas_staf');
        Schema::dropIfExists('peran_client_pengguna');
        Schema::dropIfExists('admin_client');
        Schema::dropIfExists('super_admin');
        Schema::dropIfExists('pengguna');
        Schema::dropIfExists('client');
    }
};
