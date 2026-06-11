<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_sim_types  [LOOKUP]
 *
 * Your SIMs (typically: Work, Personal). Used by people_phones.sim_id
 * to record which of your SIMs a contact is associated with -
 * e.g. "this number lives in my Work SIM's address book". Starts EMPTY.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_sim_types', function (Blueprint $table) {
            $table->id('sim_id');

            $table->string('sim_name', 50);

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
        Schema::dropIfExists('people_sim_types');
    }
};
