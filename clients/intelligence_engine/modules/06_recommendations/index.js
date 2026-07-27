const fs = require('fs');
const path = require('path');
const Ajv = require('ajv');
const addFormats = require('ajv-formats');
const crypto = require('crypto');

class RecommendationEngine {
    constructor(logger, pluginManifest) {
        this.logger = logger;
        this.pluginManifest = pluginManifest;
        
        this.ajv = new Ajv({ allErrors: true });
        addFormats(this.ajv);
        
        const schemaPath = path.join(__dirname, '..', '..', 'schemas');
        this.recommendationsSchema = JSON.parse(fs.readFileSync(path.join(schemaPath, 'recommendations.schema.json'), 'utf8'));
        this.validateRecommendations = this.ajv.compile(this.recommendationsSchema);
        
        // Load recommendation registry mapping
        this.registry = {};
        const registryPath = path.join(__dirname, '..', '..', 'recommendations', this.pluginManifest.plugin_name, 'registry.json');
        if (fs.existsSync(registryPath)) {
            const raw = JSON.parse(fs.readFileSync(registryPath, 'utf8'));
            for (const item of raw.registry) {
                this.registry[item.reference_id] = item;
            }
        } else {
            this.logger.warn(`No recommendation registry found for plugin: ${this.pluginManifest.plugin_name}`);
        }
    }

    run(opportunitiesPath, rulesDir, outputDir) {
        this.logger.info('Module 6: Recommendation Engine started.');
        
        if (!fs.existsSync(opportunitiesPath)) {
            throw new Error(`Opportunities file not found: ${opportunitiesPath}`);
        }
        
        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
        }

        const oppData = JSON.parse(fs.readFileSync(opportunitiesPath, 'utf8'));
        
        // We need the rules registry to look up the recommendation_reference for each rule
        const ruleResultsPath = path.join(rulesDir, 'rule_results.json');
        const ruleResults = JSON.parse(fs.readFileSync(ruleResultsPath, 'utf8'));
        
        // Let's build a map of triggered rule_ids to their configs.
        // Wait, rule_results doesn't contain recommendation_reference!
        // We should just load the rules configs directly to build the map!
        const ruleMap = {};
        const pluginRulesDir = path.join(__dirname, '..', '..', 'rules', this.pluginManifest.plugin_name);
        if (fs.existsSync(pluginRulesDir)) {
            const files = fs.readdirSync(pluginRulesDir).filter(f => f.endsWith('.json'));
            for (const f of files) {
                const fData = JSON.parse(fs.readFileSync(path.join(pluginRulesDir, f), 'utf8'));
                for (const r of fData.rules) {
                    ruleMap[r.rule_id] = r;
                }
            }
        }

        const recData = {
            metadata: {
                contract_version: "1.0",
                engine_version: "1.0",
                plugin: this.pluginManifest.plugin_name,
                generated_at: new Date().toISOString()
            },
            recommendations: []
        };

        // Merge AI-generated recommendations from knowledge.json if they exist
        const knowledgePath = path.join(opportunitiesPath, '..', '..', '03-knowledge', 'knowledge.json');
        if (fs.existsSync(knowledgePath)) {
            const knowledge = JSON.parse(fs.readFileSync(knowledgePath, 'utf8'));
            if (knowledge.entities && knowledge.entities.ai_recommendations) {
                for (const aiRec of knowledge.entities.ai_recommendations) {
                    recData.recommendations.push({
                        recommendation_id: `REC-AI-${crypto.randomBytes(4).toString('hex').toUpperCase()}`,
                        opportunity_id: `OPP-AI-${crypto.randomBytes(4).toString('hex').toUpperCase()}`,
                        what_to_change: aiRec.what_to_change || aiRec.recommendation || '',
                        why_it_matters: aiRec.why_it_matters || '',
                        expected_outcome: aiRec.expected_outcome || 'Improve overall performance',
                        effort: aiRec.effort || 'Medium',
                        priority: aiRec.priority || 'Medium'
                    });
                }
            }
        }

        for (const opp of oppData.opportunities) {
            const ruleDef = ruleMap[opp.rule_id];
            if (!ruleDef || !ruleDef.recommendation_reference) {
                this.logger.warn(`No recommendation reference found for rule ${opp.rule_id}`);
                continue;
            }

            const recDef = this.registry[ruleDef.recommendation_reference];
            if (!recDef) {
                this.logger.warn(`Recommendation definition not found in registry for reference ${ruleDef.recommendation_reference}`);
                continue;
            }

            recData.recommendations.push({
                recommendation_id: `REC-${crypto.randomBytes(4).toString('hex').toUpperCase()}`,
                opportunity_id: opp.opportunity_id,
                what_to_change: recDef.what_to_change,
                why_it_matters: recDef.why_it_matters,
                expected_outcome: recDef.expected_outcome,
                effort: recDef.effort,
                priority: opp.priority // Inherited from opportunity priority
            });
        }

        if (!this.validateRecommendations(recData)) {
            this.logger.error('Recommendation Engine produced invalid schema', { errors: this.validateRecommendations.errors });
            throw new Error('Recommendation Engine produced invalid schema');
        }

        const outPath = path.join(outputDir, 'recommendations.json');
        fs.writeFileSync(outPath, JSON.stringify(recData, null, 2));

        this.logger.info(`Module 6: Recommendation Engine completed. Generated ${recData.recommendations.length} recommendations.`);
        return recData;
    }
}

module.exports = RecommendationEngine;
