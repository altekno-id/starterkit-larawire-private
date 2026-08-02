<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('starter_client_logins', function (Blueprint $table): void {
            $table->softDeletes()->index();
        });

        Schema::table('starter_client_roles', function (Blueprint $table): void {
            $table->softDeletes()->index();
        });
    }

    public function down(): void
    {
        Schema::table('starter_client_logins', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('starter_client_roles', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
