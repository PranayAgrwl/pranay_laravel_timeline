<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_phones
 *
 * A phone number entry per (contact, label, country, number) combo.
 *
 * `phone` is VARCHAR so leading zeros / international formatting
 * (spaces, hyphens, '+') survive round-tripping.
 *
 * `sim_id` is nullable because not every number ties to one of your
 * own SIMs - sometimes you just have someone's number.
 *
 * UNIQUE prevents storing the exact same (contact, number, country)
 * twice. Note that the same number CAN appear across DIFFERENT
 * contacts (e.g. spouses sharing a landline) - that's intentional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_phones', function (Blueprint $table) {
            $table->id('phone_id');

            $table->foreignId('contact_id')
                ->constrained('people_contacts', 'contact_id')
                ->onDelete('restrict');
            $table->foreignId('phone_label_id')
                ->constrained('people_label_phone', 'phone_label_id')
                ->onDelete('restrict');
            $table->foreignId('country_id')
                ->constrained('people_country_codes', 'country_id')
                ->onDelete('restrict');

            $table->string('phone', 20);

            $table->foreignId('sim_id')
                ->nullable()
                ->constrained('people_sim_types', 'sim_id')
                ->onDelete('restrict');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(
                ['contact_id', 'phone', 'country_id'],
                'people_phones_per_contact_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people_phones');
    }
};
