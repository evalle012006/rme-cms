<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * The `social_facebook_accounts` table is used by both Google and Facebook flows
 * (the name is legacy). This migration:
 *
 *   - Aligns `user_id` with `users.id` (bigint unsigned) so a foreign key is possible.
 *   - Adds a foreign key with ON DELETE CASCADE so deleting a user cleans up
 *     their social links.
 *   - Adds a UNIQUE index on (provider, provider_user_id) — the exact lookup we
 *     perform on every social login. Also protects against duplicate links.
 *   - Adds a plain index on user_id for the reverse lookup (list a user's linked
 *     social accounts).
 *   - Adds a nullable `provider_email` column for observability.
 *
 * All operations are guarded so re-running the migration on a partially-migrated
 * environment is safe.
 */
class HardenSocialFacebookAccounts extends Migration
{
    public function up()
    {
        Schema::table('social_facebook_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });

        if (!Schema::hasColumn('social_facebook_accounts', 'provider_email')) {
            Schema::table('social_facebook_accounts', function (Blueprint $table) {
                $table->string('provider_email')->nullable()->after('provider');
            });
        }

        if (!$this->indexExists('social_facebook_accounts', 'social_facebook_accounts_provider_provider_user_id_unique')) {
            Schema::table('social_facebook_accounts', function (Blueprint $table) {
                $table->unique(['provider', 'provider_user_id']);
            });
        }

        if (!$this->indexExists('social_facebook_accounts', 'social_facebook_accounts_user_id_index')) {
            Schema::table('social_facebook_accounts', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        if (!$this->foreignKeyExists('social_facebook_accounts', 'social_facebook_accounts_user_id_foreign')) {
            Schema::table('social_facebook_accounts', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::table('social_facebook_accounts', function (Blueprint $table) {
            if ($this->foreignKeyExists('social_facebook_accounts', 'social_facebook_accounts_user_id_foreign')) {
                $table->dropForeign('social_facebook_accounts_user_id_foreign');
            }
            if ($this->indexExists('social_facebook_accounts', 'social_facebook_accounts_user_id_index')) {
                $table->dropIndex('social_facebook_accounts_user_id_index');
            }
            if ($this->indexExists('social_facebook_accounts', 'social_facebook_accounts_provider_provider_user_id_unique')) {
                $table->dropUnique('social_facebook_accounts_provider_provider_user_id_unique');
            }
            if (Schema::hasColumn('social_facebook_accounts', 'provider_email')) {
                $table->dropColumn('provider_email');
            }
        });

        Schema::table('social_facebook_accounts', function (Blueprint $table) {
            $table->integer('user_id')->change();
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = DB::connection()->getDatabaseName();
        $count = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->count();
        return $count > 0;
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $database = DB::connection()->getDatabaseName();
        $count = DB::table('information_schema.table_constraints')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('constraint_name', $constraint)
            ->where('constraint_type', 'FOREIGN KEY')
            ->count();
        return $count > 0;
    }
}
