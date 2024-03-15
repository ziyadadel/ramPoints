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
        Schema::table('user_voutcher', function (Blueprint $table) {
            $table->string('voutcher_plan_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_voutcher', function (Blueprint $table) {
            $table->dropColumn('voutcher_plan_name');
        });
    }
};
