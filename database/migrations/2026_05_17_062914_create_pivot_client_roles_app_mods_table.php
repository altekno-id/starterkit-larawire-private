<?php

use App\Models\Starter\AppMod;
use App\Models\Starter\App;
use App\Models\Starter\AppMenu;
use App\Models\Starter\ClientRole;
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
        Schema::create('pivot_client_roles_app_mods', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ClientRole::class)->constrained('starter_client_roles')->cascadeOnDelete();
            $table->foreignIdFor(AppMod::class)->constrained('starter_app_mods')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['client_role_id', 'app_mod_id']);
        });

        Schema::create('pivot_client_roles_app_landings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ClientRole::class)->constrained('starter_client_roles')->cascadeOnDelete();
            $table->foreignIdFor(App::class)->constrained('starter_apps')->cascadeOnDelete();
            $table->foreignIdFor(AppMenu::class)->constrained('starter_app_menus')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['client_role_id', 'app_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pivot_client_roles_app_landings');
        Schema::dropIfExists('pivot_client_roles_app_mods');
    }
};
