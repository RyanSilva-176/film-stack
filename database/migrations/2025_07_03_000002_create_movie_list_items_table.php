<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_list_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('tmdb_movie_id'); // ID do filme no TMDB
            $table->timestamp('watched_at')->nullable(); // Para lista "assistidos"
            $table->tinyInteger('rating')->nullable(); // Rating pessoal do usuário (1-10)
            $table->text('notes')->nullable(); // Notas pessoais sobre o filme
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['movie_list_id', 'tmdb_movie_id']);
            $table->unique(['movie_list_id', 'tmdb_movie_id'], 'list_movie_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_list_items');
    }
};
