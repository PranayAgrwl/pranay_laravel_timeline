<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_address_link
 *
 * Junction: which addresses are associated with which contacts.
 * UNIQUE prevents adding the same (address, contact) pair twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_address_link', function (Blueprint $table) {
            $table->id('address_link_id');

            $table->foreignId('address_id')
                ->constrained('people_address', 'address_id')
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

            $table->unique(['address_id', 'contact_id'], 'people_address_link_pair_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people_address_link');
    }
};
