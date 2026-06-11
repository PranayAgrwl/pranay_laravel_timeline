<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_group_links
 *
 * Junction: which contacts belong to which groups (many-to-many).
 * The (group_id, contact_id) pair is unique to prevent duplicate
 * membership.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_group_links', function (Blueprint $table) {
            $table->id('group_link_id');

            $table->foreignId('group_id')
                ->constrained('people_group_names', 'group_id')
                ->onDelete('restrict');
            $table->foreignId('contact_id')
                ->constrained('people_contacts', 'contact_id')
                ->onDelete('restrict');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['group_id', 'contact_id'], 'people_group_links_pair_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people_group_links');
    }
};
