<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_address
 *
 * A specific address-at-a-location (e.g. "Flat 3A" at a building).
 *
 *   type = 0  residence
 *   type = 1  work
 *   type = 2  other
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_address', function (Blueprint $table) {
            $table->id('address_id');

            $table->foreignId('loc_id')
                ->constrained('people_locations', 'location_id')
                ->onDelete('restrict');

            $table->string('name', 150)->nullable();
            $table->string('floor', 20)->nullable();
            $table->tinyInteger('type')->default(0);

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
        Schema::dropIfExists('people_address');
    }
};
