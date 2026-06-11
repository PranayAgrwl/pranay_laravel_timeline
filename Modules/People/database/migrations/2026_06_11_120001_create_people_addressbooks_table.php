<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_addressbooks
 *
 * The CardDAV "container" that holds contacts. The protocol requires
 * every contact to belong to one of these. For this single-user setup
 * one row is enough; the migration inserts it inline so a fresh deploy
 * works without manual seeding.
 *
 * sync_token starts at 1 and is bumped by the application on every
 * contact insert/update/delete (sabre/dav uses it for incremental sync).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_addressbooks', function (Blueprint $table) {
            $table->id('addressbook_id');

            $table->string('uri', 255)->unique();
            $table->string('displayname', 150);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('sync_token')->default(1);

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
        });

        // Seed the single, mandatory address book row.
        // created_by/updated_by are nullable-friendly only on a fresh install where
        // no users exist yet, so we fall back to the first available user id.
        $bootstrapUserId = DB::table('users')->orderBy('id')->value('id') ?? 1;

        DB::table('people_addressbooks')->insert([
            'uri'         => 'my-contacts',
            'displayname' => 'My Contacts',
            'description' => 'Personal address book for the People module.',
            'sync_token'  => 1,
            'created_by'  => $bootstrapUserId,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('people_addressbooks');
    }
};
