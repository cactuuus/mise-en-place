<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->json('ingredients');
            $table->json('instructions');
            $table->text('notes')->nullable();
            $table->string('source_url')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_public')->default(true);
            $table->foreignId('forked_from_recipe_id')->nullable()->constrained('recipes')->onDelete('set null');
            $table->integer('prep_time')->nullable(); // in minutes
            $table->integer('cook_time')->nullable(); // in minutes
            $table->integer('serves')->nullable();
            $table->integer('difficulty_level')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
