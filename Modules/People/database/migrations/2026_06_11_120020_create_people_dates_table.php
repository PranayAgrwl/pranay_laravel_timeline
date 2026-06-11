<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_dates
 *
 * Significant dates per contact (birthdays, anniversaries, etc.).
 *
 *   recurrence = 0  one-shot date (a specific year's event)
 *   recurrence = 1  recurs annually on the same month-day
 *
 * `reminder_id` is nullable - some dates don't need a reminder; the
 * reminder system (future step) will only act on dates that have one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_dates', function (Blueprint $table) {
            $table->id('date_id');

            $table->foreignId('contact_id')
                ->constrained('people_contacts', 'contact_id')
                ->onDelete('restrict');
            $table->foreignId('label_date_id')
                ->constrained('people_label_dates', 'label_date_id')
                ->onDelete('restrict');

            $table->date('date');
            $table->time('time')->nullable();

            $table->foreignId('reminder_id')
                ->nullable()
                ->constrained('people_label_reminders', 'reminder_id')
                ->onDelete('restrict');

            $table->boolean('recurrence')->default(false);

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
        Schema::dropIfExists('people_dates');
    }
};
