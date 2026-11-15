<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client', function (Blueprint $table): void {
            if (! Schema::hasColumn('client', 'radius_km')) {
                $table->unsignedSmallInteger('radius_km')->default(5)->after('alamat');
            }
            if (! Schema::hasColumn('client', 'jam_buka')) {
                $table->string('jam_buka', 60)->default('07:00 - 22:00 WIB')->after('radius_km');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client', function (Blueprint $table): void {
            $columns = array_filter(['radius_km', 'jam_buka'], fn (string $col) => Schema::hasColumn('client', $col));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
