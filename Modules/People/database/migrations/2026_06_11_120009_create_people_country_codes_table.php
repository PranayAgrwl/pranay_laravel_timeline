<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_country_codes  [LOOKUP]
 *
 * Dial codes (e.g. "+91" India, "+1" USA). VARCHAR because of the
 * leading "+" and dashes some country codes carry. Starts EMPTY.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_country_codes', function (Blueprint $table) {
            $table->id('country_id');

            $table->string('country_code', 7);
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
        Schema::dropIfExists('people_country_codes');
    }
};
