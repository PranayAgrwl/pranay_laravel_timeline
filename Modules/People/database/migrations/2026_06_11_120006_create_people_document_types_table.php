<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_document_types  [LOOKUP]
 *
 * Catalogue of document kinds (PAN, Aadhaar, Passport, DL, etc.).
 * Starts EMPTY; populate via UI or SQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_document_types', function (Blueprint $table) {
            $table->id('document_type_id');

            $table->string('name', 100);

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
        Schema::dropIfExists('people_document_types');
    }
};
