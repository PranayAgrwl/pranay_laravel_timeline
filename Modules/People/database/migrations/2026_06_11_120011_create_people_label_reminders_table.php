<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_label_reminders  [LOOKUP]
 *
 * Reminder presets. `reminder_name` is informational (e.g. "Cake
 * Cutting", "Gift Getting"); `hours_prior` is the actionable number
 * the future reminder system will use to schedule a notification
 * X hours before a date. Starts EMPTY.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_label_reminders', function (Blueprint $table) {
            $table->id('reminder_id');

            $table->string('reminder_name', 100);
            $table->unsignedInteger('hours_prior')->default(0);

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
        Schema::dropIfExists('people_label_reminders');
    }
};
