<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_locations
 *
 * Geographic place records, reusable across multiple addresses (e.g.
 * an apartment building has one location row, many flats reference it
 * via people_address.loc_id).
 *
 * pin_code/lat/lon/everything is nullable: the table is permissive
 * because real-world addresses are rarely complete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_locations', function (Blueprint $table) {
            $table->id('location_id');

            $table->string('name', 150)->nullable();
            $table->string('street', 255)->nullable();
            $table->string('area', 150)->nullable();
            $table->string('near', 150)->nullable();
            $table->string('pin_code', 15)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lon', 10, 7)->nullable();
            $table->text('g_maps_link')->nullable();

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
        Schema::dropIfExists('people_locations');
    }
};
