<?php

use App\Models\Starter\AppMod;
use App\Models\Starter\App;
use App\Models\Starter\AppMenu;
use App\Models\Starter\UserRole;
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
        Schema::create('rel_user_roles_app_mods', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(UserRole::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AppMod::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_role_id', 'app_mod_id']);
        });

        Schema::create('rel_user_roles_app_landings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(UserRole::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(App::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AppMenu::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_role_id', 'app_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_user_roles_app_landings');
        Schema::dropIfExists('rel_user_roles_app_mods');
    }
};
