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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('logo')->nullable();
            $table->enum('account_status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->enum('subscription_status', ['none', 'trialing', 'pending_approval', 'active', 'past_due', 'canceled', 'expired'])->default('none');
            $table->string('payment_method', 30)->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamp('payment_approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('account_status');
            $table->index('subscription_status');
            $table->index('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
