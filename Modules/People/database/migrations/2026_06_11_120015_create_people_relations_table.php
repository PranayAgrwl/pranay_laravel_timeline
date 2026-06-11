<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_relations
 *
 * Directed graph of inter-contact relationships.
 *
 * Constraints (enforced at the DB level for safety):
 *   - UNIQUE(from, to, type)  prevents accidental duplicate relations.
 *   - CHECK(from <> to)       prevents self-relations entirely.
 *
 * Auto-mirroring is handled in the Relation model's saved event, NOT
 * here at the DB level.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_relations', function (Blueprint $table) {
            $table->id('relation_id');

            $table->foreignId('contact_from_id')
                ->constrained('people_contacts', 'contact_id')
                ->onDelete('restrict');
            $table->foreignId('contact_to_id')
                ->constrained('people_contacts', 'contact_id')
                ->onDelete('restrict');
            $table->foreignId('relation_type_id')
                ->constrained('people_relation_types', 'relation_type_id')
                ->onDelete('restrict');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(
                ['contact_from_id', 'contact_to_id', 'relation_type_id'],
                'people_relations_triplet_unique'
            );
        });

        // CHECK constraints are not natively expressible via Schema builder
        // in a portable way across all DB drivers, so we add it as raw SQL.
        // MariaDB 10.2+/MySQL 8+ both enforce CHECK clauses.
        DB::statement(
            'ALTER TABLE people_relations '.
            'ADD CONSTRAINT people_relations_no_self CHECK (contact_from_id <> contact_to_id)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('people_relations');
    }
};
