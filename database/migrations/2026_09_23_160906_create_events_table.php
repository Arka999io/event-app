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
    Schema::create('events', function (Blueprint $table) {
        $table->id();

        $table->foreignId('organizer_id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->foreignId('category_id')
            ->constrained()
            ->restrictOnDelete();

        $table->string('title');
        $table->text('description');

        $table->timestamp('starts_at');
        $table->timestamp('ends_at')->nullable();

        $table->string('meeting_address')->nullable();
        $table->string('venue_address');

        $table->unsignedSmallInteger('min_age')->nullable();
        $table->unsignedSmallInteger('max_age')->nullable();

        $table->string('gender_preference')->default('any');

        $table->unsignedInteger('spots_total');
        $table->unsignedInteger('spots_taken')->default(0);

        $table->string('status')->default('draft');
        $table->timestamp('published_at')->nullable();

        $table->timestamps();

        $table->index(['status', 'starts_at']);
        $table->index(['category_id', 'status']);
        $table->index(['organizer_id', 'status']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
