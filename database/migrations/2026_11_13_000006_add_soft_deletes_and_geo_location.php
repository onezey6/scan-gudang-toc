<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Soft Deletes untuk items
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // 2. Soft Deletes untuk handover_documents
        Schema::table('handover_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('handover_documents', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // 3. Kolom Geolocation Real-Time untuk access_logs
        Schema::table('access_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('access_logs', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('access_logs', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('access_logs', 'location_name')) {
                $table->string('location_name', 200)->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $cols = array_filter(['latitude', 'longitude', 'location_name'], fn ($c) => Schema::hasColumn('access_logs', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('handover_documents', function (Blueprint $table) {
            if (Schema::hasColumn('handover_documents', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
