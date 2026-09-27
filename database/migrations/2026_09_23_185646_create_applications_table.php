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
    Schema::create('applications', function (Blueprint $table) {
        $table->id();

        $table->foreignId('event_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('applicant_id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->text('cover_letter')->nullable();

        $table->string('status')->default('pending');

        $table->timestamps();

        $table->unique(['event_id', 'applicant_id']);
        $table->index(['event_id', 'status']);
        $table->index(['applicant_id', 'status']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
