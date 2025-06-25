<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::create('favorites', function (Blueprint $table) {
                $table->string('id', 128)->primary();
                $table->string('user_id', 128);

                $table->string('word', 258);
                $table->json('phonetics')->nullable();
                $table->json('definitions')->nullable();
                $table->string('partOfSpeech')->nullable();
                $table->json('examples')->nullable();
                $table->json('synonyms')->nullable();

                $table->timestamp('created_at');
                $table->datetime('updated_at')->nullable();
                $table->string('created_by', 128)->nullable();
                $table->string('updated_by', 128)->nullable();
                $table->datetime('deleted_at')->nullable();
                $table->string('deleted_by', 128)->nullable();
                $table->tinyInteger('is_deleted')->default(0);
            });
        } catch (\Exception $e) {
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
