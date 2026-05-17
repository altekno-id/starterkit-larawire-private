<?php

use App\Models\StarterMod;
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
        Schema::create('starter_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StarterMod::class)->constrained();
            $table->string('name')->unique();
            $table->string('uri');
            $table->string('method', 10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starter_routes');
    }
};
