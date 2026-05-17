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
        Schema::create('app_menus', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->unsignedTinyInteger('order')->default(1);
            $table->foreignIdFor(AppMod::class)->constrained();
            $table->foreignIdFor(AppRoute::class)->nullable()->constrained();
            $table->foreignIdFor(AppMenu::class, 'parent_id')->nullable()->constrained('app_menus');
            $table->timestamps();

            $table->index(['app_mod_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_menus');
    }
};
