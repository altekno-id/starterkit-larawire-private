<?php

use App\Models\Starter\AppMenu;
use App\Models\Starter\AppMod;
use App\Models\Starter\AppRoute;
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
        Schema::create('starter_app_menus', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->unsignedTinyInteger('order')->default(1);
            $table->boolean('is_landing_candidate')->default(false);
            $table->foreignIdFor(AppMod::class)->constrained('starter_app_mods');
            $table->foreignIdFor(AppRoute::class)->nullable()->constrained('starter_app_routes');
            $table->foreignIdFor(AppMenu::class, 'parent_id')->nullable()->constrained('starter_app_menus');
            $table->timestamps();

            $table->index(['app_mod_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starter_app_menus');
    }
};
