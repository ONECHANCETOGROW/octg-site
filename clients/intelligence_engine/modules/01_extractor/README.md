# Module 1: Data Extraction Engine

## Purpose
The first module in the Intelligence Engine pipeline. It reads raw user uploads (CSV, XLSX), normalizes the column headers according to the active plugin's manifest, and outputs structured, deterministic JSON.

## Dependencies
- `csv-parser`
- `xlsx`

## Inputs
- Files stored in `01-original/` directory of an audit workspace.
- `plugin.json` (defines supported files and header normalizations).

## Outputs
- Clean arrays of objects saved to the `02-extracted/` directory as `.json` files.
- Emits `Extraction Started` and `Extraction Completed` events.
- Writes execution logs to `extraction.log.json`.

## JSON Schema Example
```json
[
  {
    "campaign_name": "Summer Sale",
    "spend": "1000.50",
    "conversions": "15",
    "cpa": "66.7",
    "search_term": "cheap shoes"
  }
]
```

## How to Test
```bash
node index.js --workspace=tests/01_extractor/sample_data --plugin=google_ads
```
