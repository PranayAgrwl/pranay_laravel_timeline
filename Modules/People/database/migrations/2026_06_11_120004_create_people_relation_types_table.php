<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_relation_types  [LOOKUP]
 *
 * Catalogue of relationship kinds (standalone / friend / colleague /
 * parent / child / service / spouse). Starts EMPTY; the user populates
 * via UI or direct SQL.
 *
 * `mirror` is a boolean flag: when true, saving a relation of this
 * type triggers the auto-mirror listener to create the inverse
 * relation. The listener uses the `name` column to decide WHICH
 * inverse to create (parent <-> child, spouse <-> spouse). Renaming
 * those rows in the UI breaks mirror logic - see the Relation model
 * for the exact mapping.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_relation_types', function (Blueprint $table) {
            $table->id('relation_type_id');

            $table->string('name', 50);
            $table->boolean('mirror')->default(false);

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
        Schema::dropIfExists('people_relation_types');
    }
};
