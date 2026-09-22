<?php

namespace Tests\Feature\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Builds the locality reference tables on the in-memory sqlite connection.
 *
 * The full migration set cannot run here — several older migrations use MySQL
 * only features (`information_schema` lookups, column `change()`), which is
 * unrelated to this feature. These tests exercise the controller, model scopes
 * and matching logic, so they only need the two reference tables plus enough of
 * `reviews` to resolve a foreign key.
 */
trait CreatesLocalitySchema
{
    protected function createLocalitySchema()
    {
        Schema::dropIfExists('properties');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('suburbs');
        Schema::dropIfExists('review_states');

        Schema::create('review_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('abbreviation');
        });

        Schema::create('suburbs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('name_normalised', 150)->nullable();
            $table->string('state');
            $table->string('state_code', 3)->nullable();
            $table->string('postcode');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('source_version')->nullable();
            $table->timestamps();
            $table->unique(['name_normalised', 'postcode', 'state_code']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->integer('state')->nullable();
            $table->string('suburb')->nullable();
            $table->string('post_code')->nullable();
            $table->unsignedBigInteger('locality_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('state')->nullable();
            $table->string('suburb')->nullable();
            $table->string('post_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        $this->seedReviewStates();
    }

    protected function seedReviewStates()
    {
        DB::table('review_states')->insert([
            ['name' => 'Australian Capital Territory', 'abbreviation' => 'ACT'],
            ['name' => 'New South Wales', 'abbreviation' => 'NSW'],
            ['name' => 'Northern Territory', 'abbreviation' => 'NT'],
            ['name' => 'Queensland', 'abbreviation' => 'QLD'],
            ['name' => 'South Australia', 'abbreviation' => 'SA'],
            ['name' => 'Tasmania', 'abbreviation' => 'TAS'],
            ['name' => 'Victoria', 'abbreviation' => 'VIC'],
            ['name' => 'Western Australia', 'abbreviation' => 'WA'],
        ]);
    }

    /**
     * @param array $attributes
     * @return int inserted id
     */
    protected function createLocality(array $attributes)
    {
        $name = $attributes['name'];
        $stateCode = $attributes['state_code'];

        $stateName = DB::table('review_states')
            ->where('abbreviation', $stateCode)
            ->value('name');

        return DB::table('suburbs')->insertGetId(array_merge([
            'name' => $name,
            'name_normalised' => \App\Models\Suburbs::normalise($name),
            'state' => $stateName,
            'postcode' => $attributes['postcode'],
            'latitude' => '-27.4',
            'longitude' => '153.0',
            'is_active' => true,
            'source_version' => 'test',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }
}
