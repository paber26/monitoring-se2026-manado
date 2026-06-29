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
        Schema::table('wilkerstats', function (Blueprint $table) {
            $table->integer('target_fasih')->default(0)->after('nmsubsls');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wilkerstats', function (Blueprint $table) {
            $table->dropColumn('target_fasih');
        });
    }
};
