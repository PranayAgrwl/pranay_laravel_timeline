<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_group_names
 *
 * Hierarchical (tree-style) groups for organising contacts:
 * e.g. "College Friends" -> "Core" -> "Hostel Wing A".
 *
 * `parent_group_id` is a self-reference. Root groups have it NULL.
 * RESTRICT on the self-FK so a group can't be hard-deleted while it
 * still has children; soft-delete is the proper removal path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_group_names', function (Blueprint $table) {
            $table->id('group_id');

            $table->string('name', 100);
            $table->string('purpose', 255)->nullable();

            // Self-reference must use foreignId(...) only AFTER the table exists,
            // so we add the column without constraint here and patch the FK in
            // a follow-up Schema::table() call.
            $table->unsignedBigInteger('parent_group_id')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('people_group_names', function (Blueprint $table) {
            $table->foreign('parent_group_id')
                ->references('group_id')
                ->on('people_group_names')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people_group_names');
    }
};
