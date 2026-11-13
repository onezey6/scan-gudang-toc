<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 50)->unique();
            $table->string('barcode', 100)->unique()->index();
            $table->string('name', 200)->index();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('item_type', 30)->default('consumable')->index(); // consumable, asset
            $table->string('brand', 100)->nullable();
            $table->string('unit', 30)->default('Unit');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('storage_location', 120)->nullable();
            $table->string('condition_status', 50)->default('Baik');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('handover_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_no', 50)->unique();
            $table->string('document_type', 20)->index(); // bast (Serah Terima), bap (Pengembalian)
            $table->foreignId('related_bast_id')->nullable()->constrained('handover_documents')->nullOnDelete();
            $table->date('document_date')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Petugas pembuat
            $table->string('first_party_name', 150);
            $table->string('first_party_nip', 60)->nullable();
            $table->string('first_party_title', 120)->nullable();
            $table->string('first_party_unit', 120)->nullable();
            $table->string('second_party_name', 150);
            $table->string('second_party_nip', 60)->nullable();
            $table->string('second_party_title', 120)->nullable();
            $table->string('second_party_unit', 120)->nullable();
            $table->string('second_party_phone', 40)->nullable();
            $table->string('status', 30)->default('active')->index(); // active, returned, completed
            $table->boolean('sync_stock')->default(true);
            $table->longText('first_party_signature')->nullable();
            $table->longText('second_party_signature')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('handover_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handover_document_id')->constrained('handover_documents')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->string('condition_status', 60)->default('Baik');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 50)->unique();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Petugas scan masuk/keluar
            $table->string('movement_type', 20)->index(); // in, out
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('source_destination', 200)->nullable();
            $table->string('partner_name', 150)->nullable();
            $table->string('scan_method', 30)->default('scanner_device'); // camera, scanner_device, manual, handover
            $table->foreignId('handover_document_id')->nullable()->constrained('handover_documents')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('scanned_at')->useCurrent()->index();
            $table->timestamps();
        });

        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 150)->nullable();
            $table->string('user_email', 150)->nullable();
            $table->string('user_role', 30)->nullable();
            $table->string('event_type', 40)->index(); // login, logout, failed_login, activity
            $table->string('ip_address', 45)->nullable();
            $table->string('device_browser', 150)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('description', 255);
            $table->timestamp('accessed_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('handover_items');
        Schema::dropIfExists('handover_documents');
        Schema::dropIfExists('items');
        Schema::dropIfExists('categories');
    }
};
