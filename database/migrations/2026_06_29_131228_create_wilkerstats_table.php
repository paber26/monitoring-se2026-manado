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
        Schema::create('wilkerstats', function (Blueprint $table) {
            $table->string('idsubsls')->primary();
            $table->string('nmprov')->nullable();
            $table->string('nmkab')->nullable();
            $table->string('nmkec')->nullable();
            $table->string('nmdesa')->nullable();
            $table->string('nmsls')->nullable();
            $table->string('nmsubsls')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilkerstats');
    }
};
