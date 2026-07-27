ALTER TABLE `clients` ADD COLUMN `slug` VARCHAR(160) NULL AFTER `business_name`;

UPDATE `clients`
SET `slug` = LOWER(
    TRIM(BOTH '-' FROM
        REGEXP_REPLACE(REGEXP_REPLACE(`business_name`, '[^a-zA-Z0-9]+', '-'), '-+', '-')
    )
)
WHERE `slug` IS NULL OR `slug` = '';

-- Disambiguate any duplicate slugs produced by the backfill above by
-- appending the client id. Safe to run repeatedly (idempotent per row).
UPDATE `clients` c1
JOIN (
    SELECT `slug`, MIN(`id`) AS keep_id
    FROM `clients`
    GROUP BY `slug`
    HAVING COUNT(*) > 1
) dupes ON c1.slug = dupes.slug AND c1.id <> dupes.keep_id
SET c1.slug = CONCAT(c1.slug, '-', c1.id);

ALTER TABLE `clients` ADD UNIQUE KEY `clients_slug_unique` (`slug`);
