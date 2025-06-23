<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public $table = '';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->string('id', 128)->primary();

            $table->timestamp('created_at');
            $table->datetime('updated_at')->nullable();
            $table->string('created_by', 128)->nullable();
            $table->string('updated_by', 128)->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->string('deleted_by', 128)->nullable();
            $table->tinyInteger('is_deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
