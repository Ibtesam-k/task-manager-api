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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            $table->string('type');

            $table->foreignId('project_id')->constrained();

            $table->unsignedBigInteger('actor_id');

            $table->string('trackable_type');
            $table->unsignedBigInteger('trackable_id');

            $table->json('metadata');

            $table->timestamp('created_at');

            $table->index(['project_id', 'created_at']);
            $table->index(['trackable_type', 'trackable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
