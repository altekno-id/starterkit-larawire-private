<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('starter_clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_id');
            $table->dropIndex(['subscription_status']);
            $table->dropIndex(['payment_reference']);
            $table->dropColumn([
                'subscription_status',
                'payment_method',
                'payment_reference',
                'trial_ends_at',
                'subscribed_at',
                'subscription_ends_at',
                'payment_approved_at',
            ]);
        });

        Schema::dropIfExists('x_packages');

        Schema::table('starter_client_roles', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('desc');
        });

        DB::table('starter_client_roles')
            ->where('code', 'admin')
            ->update([
                'code' => 'superuser',
                'name' => 'Superuser',
                'is_system' => true,
            ]);

        Schema::table('starter_client_logins', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
            $table->string('status', 20)->default('active')->after('password');
            $table->boolean('must_change_password')->default(false)->after('status');
            $table->timestamp('password_changed_at')->nullable()->after('must_change_password');
            $table->unsignedSmallInteger('failed_login_count')->default(0)->after('password_changed_at');
            $table->timestamp('locked_until')->nullable()->after('failed_login_count');
        });

        DB::table('starter_client_logins')
            ->orderBy('id')
            ->get(['id', 'email'])
            ->each(function (object $login): void {
                $base = Str::of((string) $login->email)->before('@')->slug('_')->lower()->value() ?: 'user';
                $username = $base;
                $suffix = 1;

                while (DB::table('starter_client_logins')->where('username', $username)->exists()) {
                    $username = $base.'_'.++$suffix;
                }

                DB::table('starter_client_logins')->where('id', $login->id)->update(['username' => $username]);
            });

        Schema::table('starter_client_logins', function (Blueprint $table) {
            $table->unique('username');
            $table->dropUnique(['google_id']);
            $table->dropColumn(['google_id', 'google_avatar', 'last_login_provider']);
            $table->index(['status', 'locked_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('starter_client_logins', function (Blueprint $table) {
            $table->dropIndex(['status', 'locked_until']);
            $table->dropUnique(['username']);
            $table->dropColumn([
                'username',
                'status',
                'must_change_password',
                'password_changed_at',
                'failed_login_count',
                'locked_until',
            ]);
            $table->string('google_id')->nullable()->unique();
            $table->string('google_avatar')->nullable();
            $table->string('last_login_provider', 20)->nullable();
        });

        Schema::table('starter_client_roles', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
