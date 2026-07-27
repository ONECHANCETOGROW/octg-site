# Module 3: Rule Engine

## Purpose
The Rule Engine introduces the first layer of business intelligence. It consumes the canonical `knowledge.json`, loads all JSON-based rules dynamically, and evaluates the data to determine what business conditions exist. It does NOT score, prioritize, or generate recommendations; it solely outputs whether a rule triggered or not, alongside evidence.

## Architecture & Extensibility
Rules are stored outside the engine logic in the `rules/{plugin}/` directory. The Rule Engine (using a Rule Registry pattern) auto-discovers these JSON files on execution. This means a new rule can be added simply by dropping a new `.json` file—no code changes required.

## Input & Dependencies
- Reads `03-knowledge/knowledge.json`.
- Uses `ajv` to strictly validate all loaded rules against `schemas/rule.schema.json`.
- Safely evaluates string-based conditionals via a functional context (`new Function`).

## Output
- `04-rules/rule_results.json`
- Validated against `schemas/rule_results.schema.json`.
- Each triggered rule includes specific `evidence` fields extracted from the row that triggered it, ensuring full transparency.

## Rule Structure Example
```json
{
    "rule_id": "GA-CAMP-002",
    "rule_name": "Zero Conversion Spend",
    "category": "campaigns",
    "severity": "Critical",
    "condition": "entity.conversions == 0 && entity.spend > 100",
    "evidence_fields": ["campaign_name", "spend", "conversions"]
}
```

## Resilience
If a single rule's conditional logic fails or throws an exception, the Rule Engine logs the error in the `validation_summary` and continues evaluating all other rules. One bad rule will never stop an audit.
