<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_nick_names
 *
 * One-to-many: a contact can have many nicknames (Pranu, PA, Boss).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_nick_names', function (Blueprint $table) {
            $table->id('nick_name_id');

            $table->foreignId('contact_id')
                ->constrained('people_contacts', 'contact_id')
                ->onDelete('restrict');

            $table->string('nick_name', 100);

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
        Schema::dropIfExists('people_nick_names');
    }
};
