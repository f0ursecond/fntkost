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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();

    $table->string('name');
    $table->string('phone_number', 20);
    $table->string('room_number')->unique();

    $table->unsignedBigInteger('monthly_rent');
    $table->unsignedTinyInteger('due_day')->default(10);

    $table->boolean('is_active')->default(true);

    $table->date('move_in_date')->nullable();
    $table->date('move_out_date')->nullable();

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
