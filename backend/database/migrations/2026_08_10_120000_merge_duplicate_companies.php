<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Merge live company_details duplicates matched by LOWER(TRIM(company_name)).
 *
 * Keeper: prefer status = 1, then lowest id.
 * Reassign users.company_id, properties.developer_name, advertise.company_id,
 * backfill empty keeper fields, then soft-delete source rows.
 *
 * Not safely reversible without a backup — down() is a no-op.
 */
class MergeDuplicateCompanies extends Migration
{
    public function up()
    {
        DB::transaction(function () {
            $duplicates = DB::table('company_details')
                ->selectRaw('LOWER(TRIM(company_name)) as name_key')
                ->whereNull('deleted_at')
                ->groupBy(DB::raw('LOWER(TRIM(company_name))'))
                ->havingRaw('COUNT(*) > 1')
                ->pluck('name_key');

            foreach ($duplicates as $nameKey) {
                $rows = DB::table('company_details')
                    ->whereNull('deleted_at')
                    ->whereRaw('LOWER(TRIM(company_name)) = ?', [$nameKey])
                    ->orderByDesc('status')
                    ->orderBy('id')
                    ->get();

                if ($rows->count() < 2) {
                    continue;
                }

                $keeper = $rows->first();
                $sources = $rows->slice(1);

                foreach ($sources as $source) {
                    $this->backfillKeeperFromSource($keeper->id, $source);
                    $this->reassignCompanyReferences($source->id, $keeper->id);

                    DB::table('company_details')
                        ->where('id', $source->id)
                        ->whereNull('deleted_at')
                        ->update([
                            'deleted_at' => now(),
                            'updated_at' => now(),
                        ]);
                }

                // Ensure keeper is approved if any row in the group was approved.
                $maxStatus = (int) $rows->max('status');
                if ($maxStatus > (int) $keeper->status) {
                    DB::table('company_details')
                        ->where('id', $keeper->id)
                        ->update([
                            'status' => $maxStatus,
                            'updated_at' => now(),
                        ]);
                }
            }
        });
    }

    public function down()
    {
        // Data merge is not safely reversible without a backup.
    }

    private function reassignCompanyReferences(int $sourceId, int $keeperId): void
    {
        DB::table('users')
            ->where('company_id', $sourceId)
            ->update(['company_id' => $keeperId]);

        DB::table('properties')
            ->where('developer_name', $sourceId)
            ->update([
                'developer_name' => $keeperId,
                'updated_at' => now(),
            ]);

        DB::table('advertise')
            ->where('company_id', $sourceId)
            ->update([
                'company_id' => $keeperId,
                'updated_at' => now(),
            ]);
    }

    private function backfillKeeperFromSource(int $keeperId, $source): void
    {
        $keeper = DB::table('company_details')->where('id', $keeperId)->first();
        if (!$keeper) {
            return;
        }

        $updates = [];
        foreach (['website', 'bio', 'logo', 'logo_thumb', 'contact_number', 'location'] as $field) {
            $keeperValue = isset($keeper->{$field}) ? trim((string) $keeper->{$field}) : '';
            $sourceValue = isset($source->{$field}) ? trim((string) $source->{$field}) : '';
            if ($keeperValue === '' && $sourceValue !== '') {
                $updates[$field] = $source->{$field};
            }
        }

        if (!empty($updates)) {
            $updates['updated_at'] = now();
            DB::table('company_details')->where('id', $keeperId)->update($updates);
        }
    }
}
