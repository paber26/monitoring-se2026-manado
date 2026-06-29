<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('petugas', function (Blueprint $table) {
            $table->string('nama_pengawas')->nullable()->after('email');
            $table->string('email_pengawas')->nullable()->after('nama_pengawas');
        });
        
        // Reset role column back to Pencacah for all (since we're no longer using separate rows)
        DB::table('petugas')->update(['role' => 'Pencacah']);
    }

    public function down(): void
    {
        Schema::table('petugas', function (Blueprint $table) {
            $table->dropColumn(['nama_pengawas', 'email_pengawas']);
        });
    }
};
