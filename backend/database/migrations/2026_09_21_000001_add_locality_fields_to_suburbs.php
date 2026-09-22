<?php

use App\Models\Suburbs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Promote `suburbs` to the canonical locality reference table.
 *
 * Location on reviews was captured as free text, which is the root cause behind
 * RME-197, RME-198 and RME-200. `suburbs` already held the AU suburb list, so it
 * becomes the referenced locality record rather than standing up a second table.
 *
 * Adds the columns the import and search paths need, then deduplicates and
 * indexes. The dedupe must happen before reviews.locality_id exists, which is
 * why it lives in this migration and not the next one.
 *
 * Index key lengths are capped with prefixes so the composite key stays inside
 * the 767-byte InnoDB limit on older MySQL 5.7 row formats.
 */
class AddLocalityFieldsToSuburbs extends Migration
{
    const UNIQUE_INDEX = 'suburbs_locality_unique';
    const POSTCODE_INDEX = 'suburbs_postcode_index';

    public function up()
    {
        Schema::table('suburbs', function (Blueprint $table) {
            if (!Schema::hasColumn('suburbs', 'name_normalised')) {
                $table->string('name_normalised', 150)->nullable()->after('name');
            }
            if (!Schema::hasColumn('suburbs', 'state_code')) {
                $table->string('state_code', 3)->nullable()->after('state');
            }
            if (!Schema::hasColumn('suburbs', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('suburbs', 'source_version')) {
                $table->string('source_version')->nullable();
            }
            if (!Schema::hasColumn('suburbs', 'created_at')) {
                $table->timestamps();
            }
        });

        // Retired localities keep their row but lose their centroid, and the
        // import excludes PO Boxes which have no coordinates at all.
        Schema::table('suburbs', function (Blueprint $table) {
            $table->string('latitude')->nullable()->change();
            $table->string('longitude')->nullable()->change();
        });

        $this->backfillNormalisedColumns();
        $this->removeDuplicateLocalities();
        $this->addLocalityIndexes();
    }

    public function down()
    {
        if ($this->indexExists('suburbs', self::UNIQUE_INDEX)) {
            DB::statement('ALTER TABLE suburbs DROP INDEX ' . self::UNIQUE_INDEX);
        }
        if ($this->indexExists('suburbs', self::POSTCODE_INDEX)) {
            DB::statement('ALTER TABLE suburbs DROP INDEX ' . self::POSTCODE_INDEX);
        }

        Schema::table('suburbs', function (Blueprint $table) {
            foreach (['state_code', 'name_normalised', 'is_active'] as $column) {
                if ($this->indexExists('suburbs', 'suburbs_' . $column . '_index')) {
                    $table->dropIndex([$column]);
                }
            }
            $table->dropColumn([
                'name_normalised',
                'state_code',
                'is_active',
                'source_version',
                'created_at',
                'updated_at',
            ]);
        });
    }

    /**
     * Derive name_normalised and state_code from the existing free-text rows.
     */
    private function backfillNormalisedColumns()
    {
        $stateCodes = DB::table('review_states')
            ->get(['name', 'abbreviation'])
            ->mapWithKeys(function ($row) {
                return [strtolower(trim($row->name)) => $row->abbreviation];
            });

        DB::table('suburbs')->orderBy('id')->chunkById(500, function ($rows) use ($stateCodes) {
            foreach ($rows as $row) {
                $stateKey = strtolower(trim((string) $row->state));

                DB::table('suburbs')->where('id', $row->id)->update([
                    'name_normalised' => Suburbs::normalise($row->name),
                    // External territories have no review_states row; they stay
                    // null and the import retires them on first run.
                    'state_code' => $stateCodes->get($stateKey),
                ]);
            }
        });
    }

    /**
     * Drop duplicates on the composite key, keeping the lowest id. Nothing
     * references suburbs.id yet, so this cannot orphan a review.
     */
    private function removeDuplicateLocalities()
    {
        $keepers = DB::table('suburbs')
            ->selectRaw('MIN(id) as keep_id')
            ->groupBy('name_normalised', 'postcode', 'state_code')
            ->pluck('keep_id');

        DB::table('suburbs')->whereNotIn('id', $keepers)->delete();
    }

    private function addLocalityIndexes()
    {
        // Guarded individually: this migration deduplicates a 15,000 row table,
        // and MySQL cannot roll DDL back, so a re-run after a partial failure
        // has to be able to pick up where it stopped.
        Schema::table('suburbs', function (Blueprint $table) {
            foreach (['state_code', 'name_normalised', 'is_active'] as $column) {
                if (!$this->indexExists('suburbs', 'suburbs_' . $column . '_index')) {
                    $table->index($column);
                }
            }
        });

        // Raw SQL so `postcode` (varchar 255) can carry a prefix length.
        if (!$this->indexExists('suburbs', self::UNIQUE_INDEX)) {
            DB::statement(
                'ALTER TABLE suburbs ADD UNIQUE INDEX ' . self::UNIQUE_INDEX
                . ' (name_normalised(150), postcode(10), state_code(3))'
            );
        }

        if (!$this->indexExists('suburbs', self::POSTCODE_INDEX)) {
            DB::statement(
                'ALTER TABLE suburbs ADD INDEX ' . self::POSTCODE_INDEX . ' (postcode(10))'
            );
        }
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
