const fs = require('fs');
const path = require('path');
const Ajv = require('ajv');
const addFormats = require('ajv-formats');
const crypto = require('crypto');

class OpportunityEngine {
    constructor(logger, pluginManifest) {
        this.logger = logger;
        this.pluginManifest = pluginManifest;
        
        this.ajv = new Ajv({ allErrors: true });
        addFormats(this.ajv);
        
        const schemaPath = path.join(__dirname, '..', '..', 'schemas');
        this.opportunitiesSchema = JSON.parse(fs.readFileSync(path.join(schemaPath, 'opportunities.schema.json'), 'utf8'));
        this.validateOpportunities = this.ajv.compile(this.opportunitiesSchema);
    }

    calculateROI(evidence, score_weight) {
        // Simple deterministic heuristic for V1:
        // If we know spend, and they triggered a rule, saving that spend is the ROI.
        if (evidence && evidence.spend) {
            return `Potential saving of $${evidence.spend.toFixed(2)}`;
        }
        return score_weight > 10 ? 'High' : 'Medium';
    }

    determineDifficulty(category) {
        // Dummy deterministic difficulty for V1
        const mapping = {
            'campaigns': 'Medium',
            'searchTerms': 'Easy',
            'keywords': 'Easy',
            'budget': 'Easy',
            'tracking': 'Hard'
        };
        return mapping[category] || 'Medium';
    }

    run(ruleResultsPath, outputDir) {
        this.logger.info('Module 5: Opportunity Engine started.');
        
        if (!fs.existsSync(ruleResultsPath)) {
            throw new Error(`Rule results file not found: ${ruleResultsPath}`);
        }
        
        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
        }

        const ruleResults = JSON.parse(fs.readFileSync(ruleResultsPath, 'utf8'));
        const triggered = ruleResults.triggered_rules || [];
        
        const opportunitiesData = {
            metadata: {
                contract_version: "1.0",
                engine_version: "1.0",
                plugin: this.pluginManifest.plugin_name,
                generated_at: new Date().toISOString()
            },
            opportunities: []
        };

        // Merge AI-generated opportunities from knowledge.json if they exist
        const knowledgePath = path.join(ruleResultsPath, '..', '..', '03-knowledge', 'knowledge.json');
        if (fs.existsSync(knowledgePath)) {
            const knowledge = JSON.parse(fs.readFileSync(knowledgePath, 'utf8'));
            if (knowledge.entities && knowledge.entities.ai_opportunities) {
                for (const aiOpp of knowledge.entities.ai_opportunities) {
                    opportunitiesData.opportunities.push({
                        opportunity_id: `OPP-AI-${crypto.randomBytes(4).toString('hex').toUpperCase()}`,
                        rule_id: 'AI_GENERATED',
                        problem: aiOpp.opportunity || aiOpp.problem || '',
                        evidence: (aiOpp.evidence && typeof aiOpp.evidence === 'object' && !Array.isArray(aiOpp.evidence)) ? aiOpp.evidence : {},
                        business_impact: aiOpp.business_impact || '',
                        estimated_roi: aiOpp.roi_impact || aiOpp.estimated_roi || 'Medium',
                        priority: aiOpp.priority || 'Medium',
                        difficulty: aiOpp.effort || aiOpp.difficulty || 'Medium'
                    });
                }
            }
        }

        for (const rule of triggered) {
            const priority = rule.severity === 'Critical' || rule.severity === 'High' ? 'High' : 
                             (rule.severity === 'Medium' ? 'Medium' : 'Low');

            opportunitiesData.opportunities.push({
                opportunity_id: `OPP-${crypto.randomBytes(4).toString('hex').toUpperCase()}`,
                rule_id: rule.rule_id,
                problem: `Triggered ${rule.rule_id} in ${rule.category}`,
                evidence: (rule.evidence && typeof rule.evidence === 'object' && !Array.isArray(rule.evidence)) ? rule.evidence : {},
                business_impact: `Negative impact on ${rule.category} performance`,
                estimated_roi: this.calculateROI(rule.evidence, rule.score_weight),
                priority: priority,
                difficulty: this.determineDifficulty(rule.category)
            });
        }

        if (!this.validateOpportunities(opportunitiesData)) {
            this.logger.error('Opportunity Engine produced invalid schema', { errors: this.validateOpportunities.errors });
            throw new Error('Opportunity Engine produced invalid schema');
        }

        const outPath = path.join(outputDir, 'opportunities.json');
        fs.writeFileSync(outPath, JSON.stringify(opportunitiesData, null, 2));

        this.logger.info(`Module 5: Opportunity Engine completed. Generated ${opportunitiesData.opportunities.length} opportunities.`);
        return opportunitiesData;
    }
}

module.exports = OpportunityEngine;
