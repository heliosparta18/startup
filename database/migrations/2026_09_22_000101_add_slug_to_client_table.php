<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('client', 'slug')) {
            Schema::table('client', function (Blueprint $table) {
                $table->string('slug', 150)->nullable()->after('nama_client')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('client', 'slug')) {
            Schema::table('client', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
