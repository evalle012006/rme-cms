<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Record why and by whom a review was removed.
 *
 * Reviews are soft deleted, so `deleted_at` already captures when. The admin
 * portal now requires a free-text reason on delete; both it and the acting
 * admin are stored alongside the row so the decision can be audited later.
 */
class AddDeleteAuditToReviews extends Migration
{
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->text('delete_reason')->nullable()->after('deleted_at');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('delete_reason');
            $table->index('deleted_by');
        });
    }

    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['deleted_by']);
            $table->dropColumn(['delete_reason', 'deleted_by']);
        });
    }
}
