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
        Schema::create('transactions', function (Blueprint $table) {
        $table->uuid('id')->primary();

        $table->foreignId('tenant_id');

        // Tagihan untuk bulan apa
        $table->date('billing_month');

        // Nominal tagihan bulan tersebut
        $table->unsignedBigInteger('amount');

        // Batas pembayaran
        $table->date('due_date');

        // Null = belum bayar
        $table->timestampTz('paid_at')->nullable();

        // Tracking reminder WhatsApp
        $table->boolean('reminder_h3_sent')->default(false);
        $table->boolean('reminder_h1_sent')->default(false);
        $table->boolean('reminder_due_sent')->default(false);

        $table->timestamps();

        $table->foreign('tenant_id')
            ->references('id')
            ->on('tenants')
            ->cascadeOnDelete();

        // Satu penghuni cuma boleh punya satu tagihan per bulan
        $table->unique(['tenant_id', 'billing_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
