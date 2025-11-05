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
        Schema::create('present_units', function (Blueprint $table) {
            $table->id('unit_id'); 
            $table->string('name', 100); 
            $table->text('notes0')->nullable(); 
            $table->text('notes1')->nullable(); 
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->enum('status', ['active', 'inactive'])->default('active'); 
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('present_units');
    }
};
