<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->date('birth_date')
                ->nullable()
                ->change();

            $table->string('gender')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->date('birth_date')
                ->nullable(false)
                ->change();

            $table->string('gender')
                ->nullable(false)
                ->change();
        });
    }
};
