<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: People  |  Table: people_other_socials
 *
 * Misc social presences per contact - email addresses, Instagram
 * handles, Twitter URLs, LinkedIn, WhatsApp numbers, etc.
 *
 * `link` is TEXT to accept either a full URL or a handle/username.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_other_socials', function (Blueprint $table) {
            $table->id('other_social_id');

            $table->foreignId('other_social_label_id')
                ->constrained('people_label_other_socials', 'other_social_label_id')
                ->onDelete('restrict');
            $table->foreignId('contact_id')
                ->constrained('people_contacts', 'contact_id')
                ->onDelete('restrict');

            $table->text('link');

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
        Schema::dropIfExists('people_other_socials');
    }
};
