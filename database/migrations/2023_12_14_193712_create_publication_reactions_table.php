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
        Schema::create('publication_reactions', function (Blueprint $table) {
            $table->unsignedBigInteger('publication_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['publication_id', 'user_id']);
            $table->string('type');

            $table->foreign('publication_id')->references('id')->on('publications');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publication_reactions');
    }
};
