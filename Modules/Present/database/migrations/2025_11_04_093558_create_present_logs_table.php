<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('present_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('habit_id')->constrained('present_habits', 'habit_id')->onDelete('restrict');
            $table->enum('outcome', ['yes', 'no'])->default('no');
            $table->decimal('value', 10, 2)->nullable();
            $table->date('log_date');
            $table->dateTime('log_time')->nullable();
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('restrict');

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            
            $table->timestamps();

            $table->unique(['habit_id', 'log_date', 'created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('present_logs');
    }
};
