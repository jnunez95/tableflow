<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 6)->unique();
            $table->unsignedInteger('number')->unique();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status')->default('available');
            $table->string('qr_code')->nullable()->unique();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
