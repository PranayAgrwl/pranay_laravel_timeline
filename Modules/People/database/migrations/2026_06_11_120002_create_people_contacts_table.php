<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_contacts
 *
 * The central entity of the module. Every person you know lives here.
 *
 * The `uuid` column is REQUIRED by CardDAV (Step 3): every contact must
 * have a stable, immutable, globally-unique identifier independent of
 * the auto-increment PK. The Contact model fills this on insert via
 * Laravel's HasUuids trait, never UI-exposed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_contacts', function (Blueprint $table) {
            $table->id('contact_id');

            $table->uuid('uuid')->unique();

            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('gender', 20)->nullable();

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
        Schema::dropIfExists('people_contacts');
    }
};
