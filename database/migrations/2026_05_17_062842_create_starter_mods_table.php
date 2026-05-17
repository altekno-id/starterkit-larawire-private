<?php

use App\Models\StarterApp;
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
        Schema::create('starter_mods', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StarterApp::class)->constrained();
            $table->string('code');
            $table->string('name');
            $table->text('desc');

            $table->unique(['starter_app_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starter_mods');
    }
};
