<?php

use App\Models\Starter\AppMod;
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
        Schema::create('app_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('uri');
            $table->string('method', 10);
            $table->foreignIdFor(AppMod::class)->constrained();
            $table->timestamps();

            $table->unique(['app_mod_id', 'name']);
            $table->unique(['app_mod_id', 'method', 'uri']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_routes');
    }
};
