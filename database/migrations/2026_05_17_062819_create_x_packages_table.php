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
        Schema::create('x_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('desc')->nullable();
            $table->enum('type', ['free', 'trial', 'paid', 'custom'])->default('paid');
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('setup_fee')->default(0);
            $table->enum('billing_cycle', ['none', 'once', 'monthly', 'yearly', 'custom'])->default('monthly');
            $table->unsignedSmallInteger('trial_days')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('x_packages');
    }
};
