<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'availability_status')) {
                $table->string('availability_status', 30)->default('sedia')->index()->after('condition_status');
            }
        });

        Schema::table('handover_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('handover_documents', 'loan_type')) {
                $table->string('loan_type', 50)->default('pinjam_pakai')->after('document_type');
                $table->string('item_status_target', 30)->default('sedang_dipinjam')->after('loan_type');
                $table->string('purpose', 255)->nullable()->after('document_date');
                $table->string('reference_letter_no', 120)->nullable()->after('purpose');
                $table->string('operation_location', 180)->nullable()->after('reference_letter_no');
                $table->date('expected_return_date')->nullable()->after('operation_location');
                $table->text('terms_conditions')->nullable()->after('notes');
                $table->string('approver_name', 150)->nullable()->after('terms_conditions');
                $table->string('approver_nip', 60)->nullable()->after('approver_name');
                $table->string('approver_title', 120)->nullable()->after('approver_nip');
            }
        });

        Schema::table('handover_items', function (Blueprint $table) {
            if (!Schema::hasColumn('handover_items', 'serial_number')) {
                $table->string('serial_number', 120)->nullable()->after('quantity');
                $table->string('accessories', 255)->nullable()->after('serial_number');
                $table->string('availability_status', 30)->default('sedang_dipinjam')->after('condition_status');
            }
        });

        if (!Schema::hasTable('handover_photos')) {
            Schema::create('handover_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('handover_document_id')->constrained('handover_documents')->cascadeOnDelete();
                $table->string('photo_path', 255);
                $table->string('caption', 255)->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_photos');

        Schema::table('handover_items', function (Blueprint $table) {
            $cols = array_filter(['serial_number', 'accessories', 'availability_status'], fn ($c) => Schema::hasColumn('handover_items', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('handover_documents', function (Blueprint $table) {
            $cols = array_filter([
                'loan_type',
                'item_status_target',
                'purpose',
                'reference_letter_no',
                'operation_location',
                'expected_return_date',
                'terms_conditions',
                'approver_name',
                'approver_nip',
                'approver_title',
            ], fn ($c) => Schema::hasColumn('handover_documents', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'availability_status')) {
                $table->dropColumn('availability_status');
            }
        });
    }
};
