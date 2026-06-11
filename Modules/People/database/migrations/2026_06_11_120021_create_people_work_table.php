<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_work
 *
 * Work history. Multiple rows per contact is fine (one row per job).
 *
 *   address_id  nullable - some jobs are remote / address unknown.
 *   ended_on    NULL means "currently employed here".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_work', function (Blueprint $table) {
            $table->id('work_id');

            $table->foreignId('contact_id')
                ->constrained('people_contacts', 'contact_id')
                ->onDelete('restrict');
            $table->foreignId('address_id')
                ->nullable()
                ->constrained('people_address', 'address_id')
                ->onDelete('restrict');

            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->string('post', 150);

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
        Schema::dropIfExists('people_work');
    }
};
