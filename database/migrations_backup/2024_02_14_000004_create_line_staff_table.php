<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role');
            $table->timestamps();

            $table->unique(['line_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_staff');
    }
};
