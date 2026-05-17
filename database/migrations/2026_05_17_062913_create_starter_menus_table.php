<?php

use App\Models\StarterMenu;
use App\Models\StarterMod;
use App\Models\StarterRoute;
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
        Schema::create('starter_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StarterMod::class)->constrained();
            $table->foreignIdFor(StarterRoute::class)->nullable()->constrained();
            $table->foreignIdFor(StarterMenu::class, 'parent_id')->nullable()->constrained('starter_menus');
            $table->string('label');
            $table->string('icon')->nullable();
            $table->unsignedTinyInteger('order')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starter_menus');
    }
};
