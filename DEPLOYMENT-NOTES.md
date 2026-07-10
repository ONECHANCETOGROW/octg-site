# One Chance To Grow — Deployment Notes (Phase 13: Website Knowledge Engine)

## Built on top of the optimizer, not duplicating it
Per your instruction, nothing from the Website Optimizer was rebuilt.
Specifically: the Internal Link Graph reads from a new `page_links` table
that `admin/cron/run-audit.php` now populates using data it was **already
computing internally** (the same inbound-link-counting pass that feeds the
Internal Linking score) — I extended that one existing loop to also persist
what it finds, rather than writing a second crawler. Content gap detection
for "weak internal linking" and "missing schema" queries the optimizer's
own `audit_page_scores` / `audit_issues` tables directly instead of
re-scanning anything.

## "Never fabricate relationships" — how I actually enforced that
Before writing any code, I greped the real catalog files to confirm exactly
which relationship fields exist (`related`, `related_product`, `pairs_with`,
`services_used`, `related_services`, and industries.php's inline `service`
field) — the graph is built from those, live, every time it's viewed. Nothing
is hand-typed or duplicated into a database table that could drift out of
sync with the real content (same principle as the optimizer's page
inventory).

Every relationship is tagged **direct** (an explicit field in the source
data) or **derived** (computed by following a chain of direct ones — e.g.
Product → Case Study only exists via Product → Service → Case Study, never
asserted on its own). The admin UI shows this tag on every edge, not just
internally.

## Two real inconsistencies I almost shipped, caught by testing against
## actual data before finalizing
1. **Entity consistency phone check**: my first version would have flagged
   `(555) 123-4567` — the example format text in Book a Demo's phone input
   `placeholder` — as a second, conflicting business phone number. Caught
   by running the checker against the real files before shipping it, not
   by assumption. Fixed by excluding placeholder attribute content.
2. **Taxonomy mismatch**: `services-catalog.php` categorizes services into
   6 groups; `resources-catalog.php` uses a different 4-group taxonomy that
   was never unified with it. Rather than silently guessing a mapping, it's
   an explicit, documented function (`octg_topic_taxonomy_map()` in
   `graph-builder.php`) — clearly labeled as an interpretive bridge I made,
   not a fact found in the data, kept in exactly one place so you (or I)
   can audit or change it easily.

## Content Freshness — an honest proxy, not a perfect one
Uses real `filemtime()` timestamps on the actual catalog files — genuinely
measured, not invented "last reviewed" dates. The file-header comment in
`includes/knowledge/freshness.php` states plainly what this does and
doesn't tell you: it reflects when a file last changed on disk, not
necessarily when a human last confirmed the content is still accurate.
A true "last reviewed by a person" field is a reasonable future addition
once content management moves further into the database — noted as a real
gap, not silently glossed over.

## New admin section: Website Intelligence
`/admin/intelligence.php` — one file, six tabbed views (Overview, Content
Gaps, Entity Consistency, Topical Authority, Internal Link Graph, Content
Freshness), consolidated deliberately rather than one file per view, to
avoid the sidebar and codebase sprawl "avoid dashboard clutter" warned
against earlier in this project.

## SQL migration
`sql/010_knowledge_engine.sql` — two tables only. The graph's entities and
relationships are deliberately **not** duplicated into tables (they live in
the real catalog files, which is the actual source of truth); only the
*output* of analysis (persisted link edges, generated recommendations) is
stored.

## Verified before calling this done
- Traced the exact regex used to extract industries from `industries.php`
  against the real file and confirmed all 8 extract correctly.
- Ran the entity-consistency phone/company-name checks against the real
  codebase (not assumed) — found and fixed the placeholder false-positive
  above.
- Full brace/paren/PHP-tag balance across all 6 new Knowledge Engine
  modules plus every modified admin file.
- Full HTML tag-balance simulation across all 10 admin pages and all 19
  main site pages after these changes — zero regressions.
- Cross-checked that `team-catalog.php`'s fields match what
  `graph-builder.php` reads from it.

## One real dependency to flag
The Internal Link Graph tab needs an audit run **with this version** of
`run-audit.php` to have data — any audit reports from before this phase
won't have `page_links` rows, since that persistence didn't exist yet.
Run a fresh audit after deploying this update.

## Uploading to Hostinger
Same as every phase — I don't touch FTP or database credentials. Import
`sql/010_knowledge_engine.sql` after 001–009, then run a fresh audit to
populate the link graph.
