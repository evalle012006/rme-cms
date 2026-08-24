-- =============================================================================
-- Merge duplicate company_details rows (case/spacing-insensitive name match)
-- =============================================================================
-- Match key: LOWER(TRIM(company_name)) among live rows (deleted_at IS NULL)
-- Keeper: prefer highest status (approved=1), then lowest id
-- Reassigns: users.company_id, properties.developer_name, advertise.company_id
-- Then soft-deletes source company rows
--
-- Compatible with MySQL 5.7+ and sql_mode=ONLY_FULL_GROUP_BY
-- Uses utf8mb4_unicode_ci so temp tables match company_details collation
--
-- BEFORE RUNNING:
--   1. Backup company_details, users, properties, advertise (or full DB)
--   2. Run SECTION A (discovery) — highlight each statement and execute separately
--   3. Run SECTION B (build map) in order, then review the mapping SELECT
--   4. Run SECTION C (dry-run counts)
--   5. Run SECTION D (merge) only after the mapping looks correct
--   6. Run SECTION E (verify)
--
-- Tip: In Workbench, select ONE statement at a time (including its trailing ;)
--      and click Execute. Do not run the whole file at once.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- SECTION A — Discovery: live duplicate name groups
-- -----------------------------------------------------------------------------
SELECT
    LOWER(TRIM(company_name)) COLLATE utf8mb4_unicode_ci AS name_key,
    COUNT(*) AS dup_count,
    GROUP_CONCAT(id ORDER BY status DESC, id ASC) AS ids,
    GROUP_CONCAT(company_name ORDER BY status DESC, id ASC SEPARATOR ' | ') AS names,
    GROUP_CONCAT(status ORDER BY status DESC, id ASC) AS statuses
FROM company_details
WHERE deleted_at IS NULL
GROUP BY LOWER(TRIM(company_name)) COLLATE utf8mb4_unicode_ci
HAVING COUNT(*) > 1
ORDER BY dup_count DESC, name_key;

-- Optional detail: each duplicate row with related counts
SELECT
    c.id,
    c.company_name,
    c.status,
    c.is_from,
    c.created_at,
    LOWER(TRIM(c.company_name)) COLLATE utf8mb4_unicode_ci AS name_key,
    (SELECT COUNT(*) FROM users u WHERE u.company_id = c.id) AS user_count,
    (SELECT COUNT(*) FROM properties p WHERE p.developer_name = c.id) AS property_count,
    (SELECT COUNT(*) FROM advertise a WHERE a.company_id = c.id) AS advertise_count
FROM company_details c
INNER JOIN (
    SELECT LOWER(TRIM(company_name)) COLLATE utf8mb4_unicode_ci AS name_key
    FROM company_details
    WHERE deleted_at IS NULL
    GROUP BY LOWER(TRIM(company_name)) COLLATE utf8mb4_unicode_ci
    HAVING COUNT(*) > 1
) d ON d.name_key = LOWER(TRIM(c.company_name)) COLLATE utf8mb4_unicode_ci
WHERE c.deleted_at IS NULL
ORDER BY name_key, c.status DESC, c.id ASC;

-- -----------------------------------------------------------------------------
-- SECTION B — Build source → keeper map (review before merging)
-- Run each statement in this section in order (same Workbench session).
-- -----------------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_dup_name_keys;
DROP TEMPORARY TABLE IF EXISTS tmp_company_keepers;
DROP TEMPORARY TABLE IF EXISTS tmp_company_merge_map;

-- B1. Duplicate name keys
CREATE TEMPORARY TABLE tmp_dup_name_keys (
    name_key VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (name_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_dup_name_keys (name_key)
SELECT LOWER(TRIM(company_name)) COLLATE utf8mb4_unicode_ci AS name_key
FROM company_details
WHERE deleted_at IS NULL
GROUP BY LOWER(TRIM(company_name)) COLLATE utf8mb4_unicode_ci
HAVING COUNT(*) > 1;

-- B2. Keeper per name (max status, then lowest id)
CREATE TEMPORARY TABLE tmp_company_keepers (
    name_key VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    keeper_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (name_key),
    KEY idx_keeper (keeper_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_company_keepers (name_key, keeper_id)
SELECT
    d.name_key,
    MIN(c.id) AS keeper_id
FROM tmp_dup_name_keys d
INNER JOIN company_details c
    ON LOWER(TRIM(c.company_name)) COLLATE utf8mb4_unicode_ci = d.name_key
   AND c.deleted_at IS NULL
INNER JOIN (
    SELECT
        LOWER(TRIM(company_name)) COLLATE utf8mb4_unicode_ci AS name_key,
        MAX(status) AS max_status
    FROM company_details
    WHERE deleted_at IS NULL
    GROUP BY LOWER(TRIM(company_name)) COLLATE utf8mb4_unicode_ci
) s ON s.name_key = d.name_key
   AND c.status = s.max_status
GROUP BY d.name_key;

-- B3. Source → keeper map (every non-keeper in a duplicate group)
CREATE TEMPORARY TABLE tmp_company_merge_map (
    source_id BIGINT UNSIGNED NOT NULL,
    keeper_id BIGINT UNSIGNED NOT NULL,
    name_key VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (source_id),
    KEY idx_keeper (keeper_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_company_merge_map (source_id, keeper_id, name_key)
SELECT
    c.id AS source_id,
    k.keeper_id,
    k.name_key
FROM company_details c
INNER JOIN tmp_company_keepers k
    ON LOWER(TRIM(c.company_name)) COLLATE utf8mb4_unicode_ci = k.name_key
WHERE c.deleted_at IS NULL
  AND c.id <> k.keeper_id;

-- Review mapping (source will be soft-deleted; keeper kept)
SELECT
    m.name_key,
    m.source_id,
    src.company_name AS source_name,
    src.status AS source_status,
    m.keeper_id,
    k.company_name AS keeper_name,
    k.status AS keeper_status
FROM tmp_company_merge_map m
INNER JOIN company_details src ON src.id = m.source_id
INNER JOIN company_details k ON k.id = m.keeper_id
ORDER BY m.name_key, m.source_id;

-- -----------------------------------------------------------------------------
-- SECTION C — Dry-run: rows that would be reassigned
-- Run each SELECT separately (MySQL cannot reopen a TEMPORARY table in UNION).
-- -----------------------------------------------------------------------------
SELECT 'users' AS tbl, COUNT(*) AS rows_to_update
FROM users u
INNER JOIN tmp_company_merge_map m ON u.company_id = m.source_id;

SELECT 'properties' AS tbl, COUNT(*) AS rows_to_update
FROM properties p
INNER JOIN tmp_company_merge_map m ON p.developer_name = m.source_id;

SELECT 'advertise' AS tbl, COUNT(*) AS rows_to_update
FROM advertise a
INNER JOIN tmp_company_merge_map m ON a.company_id = m.source_id;

SELECT 'company_details_to_soft_delete' AS tbl, COUNT(*) AS rows_to_update
FROM tmp_company_merge_map;

-- -----------------------------------------------------------------------------
-- SECTION D — Merge (run only after reviewing SECTION B / C)
-- Workbench "Safe Updates" blocks JOIN updates without a key WHERE — disable
-- for this session, then re-enable after COMMIT.
-- -----------------------------------------------------------------------------
SET SQL_SAFE_UPDATES = 0;

START TRANSACTION;

-- Backfill empty keeper fields from sources (before soft-delete)
UPDATE company_details k
INNER JOIN (
    SELECT
        m.keeper_id,
        MAX(CASE WHEN s.website IS NOT NULL AND TRIM(s.website) <> '' THEN s.website END) AS website,
        MAX(CASE WHEN s.bio IS NOT NULL AND TRIM(s.bio) <> '' THEN s.bio END) AS bio,
        MAX(CASE WHEN s.logo IS NOT NULL AND TRIM(s.logo) <> '' THEN s.logo END) AS logo,
        MAX(CASE WHEN s.logo_thumb IS NOT NULL AND TRIM(s.logo_thumb) <> '' THEN s.logo_thumb END) AS logo_thumb,
        MAX(CASE WHEN s.contact_number IS NOT NULL AND TRIM(s.contact_number) <> '' THEN s.contact_number END) AS contact_number,
        MAX(CASE WHEN s.location IS NOT NULL AND TRIM(s.location) <> '' THEN s.location END) AS location,
        MAX(s.status) AS max_status
    FROM tmp_company_merge_map m
    INNER JOIN company_details s ON s.id = m.source_id
    GROUP BY m.keeper_id
) src ON src.keeper_id = k.id
SET
    k.website = COALESCE(NULLIF(TRIM(k.website), ''), src.website, k.website),
    k.bio = COALESCE(NULLIF(TRIM(k.bio), ''), src.bio, k.bio),
    k.logo = COALESCE(NULLIF(TRIM(k.logo), ''), src.logo, k.logo),
    k.logo_thumb = COALESCE(NULLIF(TRIM(k.logo_thumb), ''), src.logo_thumb, k.logo_thumb),
    k.contact_number = COALESCE(NULLIF(TRIM(k.contact_number), ''), src.contact_number, k.contact_number),
    k.location = COALESCE(NULLIF(TRIM(k.location), ''), src.location, k.location),
    k.status = GREATEST(COALESCE(k.status, 0), COALESCE(src.max_status, 0)),
    k.updated_at = NOW()
WHERE k.id > 0;

UPDATE users u
INNER JOIN tmp_company_merge_map m ON u.company_id = m.source_id
SET u.company_id = m.keeper_id
WHERE u.id > 0;

UPDATE properties p
INNER JOIN tmp_company_merge_map m ON p.developer_name = m.source_id
SET p.developer_name = m.keeper_id,
    p.updated_at = NOW()
WHERE p.id > 0;

UPDATE advertise a
INNER JOIN tmp_company_merge_map m ON a.company_id = m.source_id
SET a.company_id = m.keeper_id,
    a.updated_at = NOW()
WHERE a.id > 0;

UPDATE company_details c
INNER JOIN tmp_company_merge_map m ON c.id = m.source_id
SET c.deleted_at = NOW(),
    c.updated_at = NOW()
WHERE c.deleted_at IS NULL
  AND c.id > 0;

COMMIT;

SET SQL_SAFE_UPDATES = 1;

-- -----------------------------------------------------------------------------
-- SECTION E — Verification
-- -----------------------------------------------------------------------------
-- Expect 0 rows
SELECT
    LOWER(TRIM(company_name)) COLLATE utf8mb4_unicode_ci AS name_key,
    COUNT(*) AS dup_count,
    GROUP_CONCAT(id ORDER BY id) AS ids
FROM company_details
WHERE deleted_at IS NULL
GROUP BY LOWER(TRIM(company_name)) COLLATE utf8mb4_unicode_ci
HAVING COUNT(*) > 1;

-- Expect 0 (or only unrelated historical soft-deletes)
SELECT 'users_pointing_at_deleted' AS check_name, COUNT(*) AS cnt
FROM users u
INNER JOIN company_details c ON c.id = u.company_id
WHERE c.deleted_at IS NOT NULL
UNION ALL
SELECT 'properties_pointing_at_deleted', COUNT(*)
FROM properties p
INNER JOIN company_details c ON c.id = p.developer_name
WHERE c.deleted_at IS NOT NULL
UNION ALL
SELECT 'advertise_pointing_at_deleted', COUNT(*)
FROM advertise a
INNER JOIN company_details c ON c.id = a.company_id
WHERE c.deleted_at IS NOT NULL;
