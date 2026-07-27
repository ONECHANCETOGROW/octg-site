# Module 2: Knowledge Builder

## Purpose
Transforms generic extracted JSON arrays into a structured, unified Knowledge Graph. This module only organizes and categorizes data; it does *not* interpret, analyze, or score the data. It merely answers: "What data do we have?"

## Dependencies
- `ajv`
- `ajv-formats`

## Inputs
- Reads JSON files from `02-extracted/`.
- Validates them against `schemas/extracted.schema.json`.

## Entity Classification
The Knowledge Builder iterates through every extracted row and maps it to a canonical entity (like `campaigns` or `searchTerms`) by looking at the `plugin.json` manifest. It matches based on `required_columns`.

## Outputs
- `03-knowledge/knowledge.json`
- Includes metadata (engine version, contract version).
- Includes basic descriptive statistics (counts, total spend, total conversions).
- Validates the output against `schemas/knowledge.schema.json`.

## Error Handling
- Invalid datasets are logged but processing continues for valid datasets.
- Schema violations throw fatal errors during development to ensure output consistency.
