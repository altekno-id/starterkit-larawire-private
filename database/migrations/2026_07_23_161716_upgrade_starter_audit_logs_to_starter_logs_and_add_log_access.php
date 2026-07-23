<?php

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
        Schema::rename('starter_audit_logs', 'starter_logs');

        Schema::table('starter_logs', function (Blueprint $table) {
            $table->ulid('action_id')->after('client_login_id');
            $table->ulid('request_id')->nullable()->after('action_id');
            $table->unsignedSmallInteger('sequence')->default(1)->after('request_id');
            $table->string('action_key', 100)->after('sequence');
            $table->string('action_label')->nullable()->after('action_key');
            $table->string('actor_name')->nullable()->after('client_login_id');
            $table->string('actor_username')->nullable()->after('actor_name');
            $table->string('actor_role')->nullable()->after('actor_username');
            $table->boolean('actor_is_superuser')->default(false)->after('actor_role');
            $table->string('table_name')->nullable()->after('event');
            $table->string('auditable_label')->nullable()->after('auditable_id');
            $table->json('metadata')->nullable()->after('new_values');
            $table->string('app_key', 100)->nullable()->after('metadata');
            $table->string('source', 20)->default('web')->after('user_agent');

            $table->index(['action_id', 'sequence'], 'starter_logs_action_sequence_index');
            $table->index(['client_id', 'action_key', 'created_at'], 'starter_logs_client_action_created_index');
            $table->index(['client_id', 'table_name', 'created_at'], 'starter_logs_client_table_created_index');
            $table->index(['client_id', 'app_key', 'created_at'], 'starter_logs_client_app_created_index');
        });

        Schema::table('starter_logs', function (Blueprint $table) {
            $table->renameColumn('request_url', 'request_path');
        });

        Schema::table('starter_client_roles', function (Blueprint $table) {
            $table->boolean('can_view_logs')->default(false)->after('can_manage_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('starter_client_roles', function (Blueprint $table) {
            $table->dropColumn('can_view_logs');
        });

        Schema::table('starter_logs', function (Blueprint $table) {
            $table->renameColumn('request_path', 'request_url');
        });

        Schema::table('starter_logs', function (Blueprint $table) {
            $table->dropIndex('starter_logs_action_sequence_index');
            $table->dropIndex('starter_logs_client_action_created_index');
            $table->dropIndex('starter_logs_client_table_created_index');
            $table->dropIndex('starter_logs_client_app_created_index');
            $table->dropColumn([
                'action_id',
                'request_id',
                'sequence',
                'action_key',
                'action_label',
                'actor_name',
                'actor_username',
                'actor_role',
                'actor_is_superuser',
                'table_name',
                'auditable_label',
                'metadata',
                'app_key',
                'source',
            ]);
        });

        Schema::rename('starter_logs', 'starter_audit_logs');
    }
};
