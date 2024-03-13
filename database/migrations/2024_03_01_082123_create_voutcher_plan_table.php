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
        Schema::create('voutcher_plan', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('number_of_points');
            $table->integer('order_number');
            $table->integer('status');
            $table->decimal('value_in_pounds', 10, 2);
            $table->integer('number_of_days_to_expire');
            $table->string('image', 300);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voutcher_plan');
    }
};
