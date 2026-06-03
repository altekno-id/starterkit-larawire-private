<?php

use App\Models\Starter\App;
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
        Schema::create('starter_app_mods', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->text('desc')->nullable();
            $table->foreignIdFor(App::class)->constrained('starter_apps');
            $table->timestamps();

            $table->unique(['app_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starter_app_mods');
    }
};
