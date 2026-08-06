<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The standard Laravel sessions table.
 *
 * Upstream Laravel ships this inside the default create_users_table migration.
 * This demo replaced that migration with its own users schema and dropped the
 * sessions table along the way, while config/session.php kept the framework
 * default of `database`. Anyone running the demo on that default therefore got
 * an unservable site: every request died with
 *
 *     SQLSTATE[42S02]: Base table or view not found: 1146
 *     Table 'laravel.sessions' doesn't exist
 *
 * Production is unaffected because the Helm chart sets SESSION_DRIVER=file, but
 * the committed default should not be a trap. With this migration both drivers
 * work.
 *
 * The existence guard is deliberate. Deployment loads the committed dump and
 * then runs `php artisan migrate --force`, so on any database whose dump already
 * carries a sessions table an unguarded create would abort the deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sessions')) {
            return;
        }

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
