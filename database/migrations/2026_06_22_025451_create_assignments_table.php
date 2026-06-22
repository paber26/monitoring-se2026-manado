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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_created')->nullable();
            $table->dateTime('date_modified')->nullable();
            $table->string('assignment_status_alias')->nullable();
            $table->string('level_3_name')->nullable();
            $table->string('level_4_name')->nullable();
            $table->string('level_5_name')->nullable();
            $table->string('level_6_full_code')->nullable();
            $table->integer('sum_clean')->default(0);
            $table->integer('sum_error')->default(0);
            $table->integer('sum_remark')->default(0);
            $table->integer('kk_open_pbi')->default(0);
            $table->string('assigned_ppl_name')->nullable();
            $table->string('assigned_pml_name')->nullable();
            $table->string('current_user_survey_role_name')->nullable();
            $table->string('source_from')->nullable();
            $table->string('real_name')->nullable();
            $table->string('current_user_username')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
