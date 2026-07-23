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
        if (Schema::hasColumn('starter_client_roles', 'client_id')) {
            Schema::table('starter_client_roles', function (Blueprint $table) {
                $table->dropForeign('starter_client_roles_client_id_foreign');
                $table->dropUnique('starter_client_roles_client_id_code_unique');
                $table->dropColumn('client_id');
                $table->unique('code');
            });
        }

        if (Schema::hasColumn('starter_client_logins', 'client_id')) {
            Schema::table('starter_client_logins', function (Blueprint $table) {
                $table->dropForeign('starter_client_logins_client_id_foreign');
                $table->dropIndex('starter_client_logins_client_id_client_role_id_index');
                $table->dropColumn('client_id');
            });
        }

        if (Schema::hasColumn('starter_logs', 'client_id')) {
            Schema::table('starter_logs', function (Blueprint $table) {
                $table->dropForeign('starter_audit_logs_client_id_foreign');
                $table->dropIndex('starter_audit_logs_client_id_created_at_index');
                $table->dropIndex('starter_logs_client_action_created_index');
                $table->dropIndex('starter_logs_client_table_created_index');
                $table->dropIndex('starter_logs_client_app_created_index');
                $table->dropColumn('client_id');

                $table->index(['action_key', 'created_at'], 'starter_logs_action_created_index');
                $table->index(['table_name', 'created_at'], 'starter_logs_table_created_index');
                $table->index(['app_key', 'created_at'], 'starter_logs_app_created_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('starter_logs', 'client_id')) {
            Schema::table('starter_logs', function (Blueprint $table) {
                $table->dropIndex('starter_logs_action_created_index');
                $table->dropIndex('starter_logs_table_created_index');
                $table->dropIndex('starter_logs_app_created_index');
                $table->foreignId('client_id')->nullable()->constrained('starter_clients')->nullOnDelete();
                $table->index(['client_id', 'created_at'], 'starter_audit_logs_client_id_created_at_index');
                $table->index(['client_id', 'action_key', 'created_at'], 'starter_logs_client_action_created_index');
                $table->index(['client_id', 'table_name', 'created_at'], 'starter_logs_client_table_created_index');
                $table->index(['client_id', 'app_key', 'created_at'], 'starter_logs_client_app_created_index');
            });
        }

        if (! Schema::hasColumn('starter_client_logins', 'client_id')) {
            Schema::table('starter_client_logins', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->constrained('starter_clients')->cascadeOnDelete();
                $table->index(['client_id', 'client_role_id']);
            });
        }

        if (! Schema::hasColumn('starter_client_roles', 'client_id')) {
            Schema::table('starter_client_roles', function (Blueprint $table) {
                $table->dropUnique('starter_client_roles_code_unique');
                $table->foreignId('client_id')->nullable()->constrained('starter_clients')->cascadeOnDelete();
                $table->unique(['client_id', 'code']);
            });
        }
    }
};
