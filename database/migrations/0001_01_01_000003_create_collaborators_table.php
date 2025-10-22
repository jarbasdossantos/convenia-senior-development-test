<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('cpf');
            $table->string('city');
            $table->string('state');
            $table->timestamps();

            $table->unique('email');
            $table->unique('cpf');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborators');
    }
};
