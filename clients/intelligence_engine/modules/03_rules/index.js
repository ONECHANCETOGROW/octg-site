const fs = require('fs');
const path = require('path');
const Ajv = require('ajv');
const addFormats = require('ajv-formats');

class RuleEngine {
    constructor(logger, pluginManifest) {
        this.logger = logger;
        this.pluginManifest = pluginManifest;
        this.rules = {}; // Grouped by category
        
        this.ajv = new Ajv({ allErrors: true });
        addFormats(this.ajv);
        
        const schemaPath = path.join(__dirname, '..', '..', 'schemas');
        this.ruleSchema = JSON.parse(fs.readFileSync(path.join(schemaPath, 'rule.schema.json'), 'utf8'));
        this.resultsSchema = JSON.parse(fs.readFileSync(path.join(schemaPath, 'rule_results.schema.json'), 'utf8'));
        
        this.validateRule = this.ajv.compile(this.ruleSchema);
        this.validateResults = this.ajv.compile(this.resultsSchema);
    }

    loadRules() {
        const rulesDir = path.join(__dirname, '..', '..', 'rules', this.pluginManifest.plugin_name);
        if (!fs.existsSync(rulesDir)) {
            this.logger.warn(`No rules directory found for plugin: ${this.pluginManifest.plugin_name}`);
            return;
        }

        const files = fs.readdirSync(rulesDir).filter(f => f.endsWith('.json'));
        let ruleCount = 0;

        for (const file of files) {
            const filePath = path.join(rulesDir, file);
            const raw = JSON.parse(fs.readFileSync(filePath, 'utf8'));
            
            if (!this.validateRule(raw)) {
                this.logger.error(`Rule file ${file} failed schema validation.`, { errors: this.validateRule.errors });
                continue;
            }

            for (const rule of raw.rules) {
                if (!rule.enabled) continue;
                
                if (!this.rules[rule.category]) {
                    this.rules[rule.category] = [];
                }
                
                // Pre-compile the condition for speed and safety
                try {
                    rule.evaluate = new Function('entity', `return ${rule.condition};`);
                    this.rules[rule.category].push(rule);
                    ruleCount++;
                } catch (err) {
                    this.logger.error(`Failed to compile condition for rule ${rule.rule_id}`, { error: err.message });
                }
            }
        }
        this.logger.info(`Rule Engine loaded ${ruleCount} rules across ${Object.keys(this.rules).length} categories.`);
    }

    evaluate(knowledgeData) {
        const results = {
            metadata: {
                contract_version: "1.0",
                engine_version: "1.0",
                plugin: this.pluginManifest.plugin_name,
                plugin_version: this.pluginManifest.version,
                generated_at: new Date().toISOString()
            },
            triggered_rules: [],
            passed_rules: [],
            validation_summary: {
                total_rules_evaluated: 0,
                total_triggered: 0,
                total_passed: 0,
                errors: []
            }
        };

        const entities = knowledgeData.entities || {};

        for (const [category, records] of Object.entries(entities)) {
            const categoryRules = this.rules[category] || [];
            
            if (categoryRules.length === 0) continue;

            for (const rule of categoryRules) {
                let triggeredCount = 0;

                for (const entity of records) {
                    try {
                        const isTriggered = rule.evaluate(entity);
                        results.validation_summary.total_rules_evaluated++;

                        if (isTriggered) {
                            triggeredCount++;
                            
                            // Build Evidence
                            const evidence = {};
                            rule.evidence_fields.forEach(field => {
                                evidence[field] = entity[field] !== undefined ? entity[field] : null;
                            });

                            results.triggered_rules.push({
                                rule_id: rule.rule_id,
                                severity: rule.severity,
                                category: rule.category,
                                status: rule.severity === 'Critical' || rule.severity === 'High' ? 'FAIL' : 'WARNING',
                                score_weight: rule.score_weight,
                                evidence
                            });
                        }
                    } catch (err) {
                        results.validation_summary.errors.push(`Error evaluating rule ${rule.rule_id}: ${err.message}`);
                        this.logger.error(`Rule evaluation error: ${rule.rule_id}`, { error: err.message, entity });
                    }
                }

                if (triggeredCount === 0) {
                    results.passed_rules.push(rule.rule_id);
                    results.validation_summary.total_passed++;
                } else {
                    results.validation_summary.total_triggered++;
                }
            }
        }

        return results;
    }

    run(knowledgePath, outputDir) {
        this.logger.info('Module 3: Rule Engine started.');
        
        this.loadRules();

        if (!fs.existsSync(knowledgePath)) {
            throw new Error(`Knowledge file not found: ${knowledgePath}`);
        }
        
        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
        }

        const knowledgeData = JSON.parse(fs.readFileSync(knowledgePath, 'utf8'));
        const results = this.evaluate(knowledgeData);

        if (!this.validateResults(results)) {
            this.logger.error('Rule Engine produced invalid schema', { errors: this.validateResults.errors });
            throw new Error('Rule Engine produced invalid schema');
        }

        const outPath = path.join(outputDir, 'rule_results.json');
        fs.writeFileSync(outPath, JSON.stringify(results, null, 2));

        this.logger.info('Module 3: Rule Engine completed successfully.', { 
            triggered: results.validation_summary.total_triggered,
            passed: results.validation_summary.total_passed
        });
        
        return results;
    }
}

module.exports = RuleEngine;
