<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anchor each review to a locality record instead of three free-text columns.
 *
 * Storing the FK is what permanently resolves RME-198: admin filtering becomes a
 * join rather than a string comparison.
 *
 * Nullable at database level so existing rows survive until
 * `reviews:backfill-localities` has run and the client has cleared the exception
 * report. New submissions require it through request validation instead.
 *
 * Legacy `state`, `suburb` and `post_code` are deliberately retained; dropping
 * them is a follow-up gated on client sign-off.
 */
class AddLocalityIdToReviews extends Migration
{
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('locality_id')->nullable()->after('suburb');
            $table->index('locality_id');
            $table->foreign('locality_id')
                ->references('id')
                ->on('suburbs')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['locality_id']);
            $table->dropIndex(['locality_id']);
            $table->dropColumn('locality_id');
        });
    }
}
