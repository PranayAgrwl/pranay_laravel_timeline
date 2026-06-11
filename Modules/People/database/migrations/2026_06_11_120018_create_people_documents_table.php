<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_documents
 *
 * File references per contact (scanned PAN, Aadhaar, passport scans).
 *
 * Note: actual file storage path style is intentionally not locked at
 * the DB layer - we just persist `file_address` as a string. The
 * upload-handling code in the Document model / controller is the
 * single source of truth for path conventions when Step 4 (web UI)
 * comes around.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_documents', function (Blueprint $table) {
            $table->id('document_id');

            $table->foreignId('document_type_id')
                ->constrained('people_document_types', 'document_type_id')
                ->onDelete('restrict');
            $table->foreignId('contact_id')
                ->constrained('people_contacts', 'contact_id')
                ->onDelete('restrict');

            $table->string('file_name', 255);
            $table->string('file_address', 500);

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people_documents');
    }
};
