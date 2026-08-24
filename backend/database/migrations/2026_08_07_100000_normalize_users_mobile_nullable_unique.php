<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Registration often leaves mobile blank. Placeholder values like "" or the
 * literal string "null" were stored and collided with users_mobile_unique.
 *
 * Ensure mobile is nullable SQL NULL when absent, and unique when present.
 */
class NormalizeUsersMobileNullableUnique extends Migration
{
    public function up()
    {
        // Convert placeholders to real NULL before enforcing uniqueness.
        DB::table('users')
            ->where(function ($query) {
                $query->where('mobile', '')
                    ->orWhereRaw('LOWER(mobile) = ?', ['null']);
            })
            ->update(['mobile' => null]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile')->nullable()->change();
        });

        // Recreate unique index so multiple NULLs are allowed and real numbers stay unique.
        if ($this->indexExists('users', 'users_mobile_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_mobile_unique');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('mobile');
        });
    }

    public function down()
    {
        // Keep nullable + unique; data cleanup is not reversed.
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();
        $result = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index]
        );

        return !empty($result);
    }
}
