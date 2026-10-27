# Locality reference data — quarterly refresh runbook

Reviews are anchored to a locality record rather than free-text location fields.
The `suburbs` table is the canonical locality reference, seeded from a
Geoscape G-NAF derived extract and refreshed each quarter.

**Ticket context:** RME-197, RME-198 and RME-200 all trace back to location being
captured as free text. `reviews.locality_id` is the fix — admin filtering became
a foreign key join instead of a string comparison that could never match.

---

## What lives where

| Thing | Path |
|---|---|
| Committed extract | `backend/database/data/localities-gnaf.json` |
| Import command | `backend/app/Console/Commands/ImportLocalities.php` |
| Backfill command | `backend/app/Console/Commands/BackfillReviewLocalities.php` |
| Read API | `backend/app/Http/Controllers/LocalityController.php` |
| Public form control | `backend/public/js/locality-select.js` |
| Admin control | `cms/src/components/atoms/LocalitySelect.tsx` |

Both UI controls read the same four endpoints, so their behaviour cannot drift:

| Endpoint | Purpose |
|---|---|
| `GET /api/localities/states` | The eight states and territories |
| `GET /api/localities?state=QLD` | Every active locality in one state, alphabetical |
| `GET /api/localities/search?state=QLD&q=asc` | Keyword search, minimum 2 characters, max 25 results |
| `GET /api/localities/by-postcode?postcode=4007` | Every locality sharing a postcode |

All four are cached for 24 hours. The cache key includes a token derived from the
`suburbs` row count and latest `updated_at`, so an import invalidates the cached
lists without the command having to flush anything.

---

## Quarterly refresh

Geoscape publishes a new G-NAF release each quarter (February, May, August,
November). The refresh is data-only — no code change is required.

### 1. Rebuild the extract

The committed file is derived from
[joelkoen/postcodes-au](https://github.com/joelkoen/postcodes-au)
(`data/localities-au.json`), which computes one row per locality + postcode from
G-NAF address records. Because it is built from physical addresses, PO Box, large
volume receiver and mail centre postcodes are absent by construction.

Download the current upstream file and keep these fields, renaming nothing:
`locality`, `state`, `postcode`, `latitude`, `longitude`. Wrap them in the same
envelope as the existing file and bump `version` to the new upstream commit, for
example `postcodes-au@a1b2c3d`.

The command also accepts a bare upstream array, so a freshly downloaded file can
be passed straight through with `--file` to preview a release before committing it.
In that case pass `--source-version=` as well, since a bare array carries no
envelope to read the version from. The option is `--source-version`, not
`--version`, which Artisan reserves for printing the framework version.

### 2. Dry run

```bash
php artisan localities:import --dry-run
```

Check the reported counts before writing. `Skipped` is expected to be non-zero:
the extract includes four external territory rows (`OT`) which have no
`review_states` row and are intentionally out of scope.

### 3. Import

```bash
php artisan localities:import
```

The import upserts on `(name_normalised, postcode, state_code)`:

- new localities are inserted
- changed localities are updated
- localities absent from the release are set `is_active = false`

**Nothing is ever deleted.** A retired locality keeps its row so reviews that
reference it still resolve, and it stops appearing in the dropdowns because every
query applies the `active` scope.

### 4. Backfill any reviews still without a locality

```bash
php artisan reviews:backfill-localities --dry-run
php artisan reviews:backfill-localities
```

Matching is attempted in three passes, and a locality is written only when
exactly one row matches:

1. normalised name + state code
2. normalised name + postcode
3. postcode + normalised name prefix

Anything unresolved goes to a CSV exception report rather than being guessed.
The report is also available in the admin portal from the
**Unresolved locations** button on either reviews list
(`GET /api/reviews/locality-exceptions`).

---

## Data licence

Non-negotiable: the attribution below must ship in the same release as the data.
It lives in the `btm-footer` block of
`backend/resources/views/layouts/app-footer.blade.php`.

> Incorporates or developed using G-NAF © Geoscape Australia licensed by the
> Commonwealth of Australia under the Open Geo-coded National Address File
> (G-NAF) End User Licence Agreement.

---

## Things worth knowing before changing this

- **Postcodes are strings, end to end.** NT postcodes lead with a zero, so
  `0800` must never pass through an integer cast.
- **`name` and `state` keep their original free-text form.** `SuburbStateLookup`,
  `HomeController::getMapData`, `getSuburbsByState` and the property forms all
  still read them. `name_normalised` and `state_code` were added alongside for
  matching, not as replacements.
- **Upstream casing is naive** — it ships `Mckinnon` and `O'connor`. The import
  writes the corrected form via `Suburbs::displayName()`, and the accessor of the
  same name covers rows written by the older seeders.
- **`Suburbs::normalise()` has a twin in JavaScript** in both
  `locality-select.js` and `LocalitySelect.tsx`. Change one and you must change
  all three, or the client and server will disagree about what matches.
- **A submitted location must resolve to exactly one locality.** The server
  re-derives `state`, `suburb` and `post_code` from the locality row and
  overwrites whatever was posted, so the legacy text columns cannot drift or be
  spoofed. Request validation requires `locality_id`; the column itself is
  nullable so existing rows survive.
- **`AuSuburbsSeeder` and `SuburbsSeeder` are kept for back-compat only.**
  `localities:import` is the maintained path.
