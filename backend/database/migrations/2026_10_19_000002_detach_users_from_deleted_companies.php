<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Users who were attached to a company that has since been soft-deleted still
 * carried its id in `users.company_id`. Deleting a company now clears that
 * pointer; this one-off brings existing rows in line.
 *
 * Accounts are untouched; only the dangling company reference is removed.
 */
class DetachUsersFromDeletedCompanies extends Migration
{
    public function up()
    {
        DB::table('users')
            ->whereIn('company_id', function ($query) {
                $query->select('id')
                    ->from('company_details')
                    ->whereNotNull('deleted_at');
            })
            ->update(['company_id' => null]);
    }

    public function down()
    {
        // The original pointers cannot be reconstructed; nothing to undo.
    }
}
