const fs = require('fs');
const path = require('path');
const Ajv = require('ajv');
const addFormats = require('ajv-formats');

class ScoringEngine {
    constructor(logger, pluginManifest) {
        this.logger = logger;
        this.pluginManifest = pluginManifest;
        
        this.ajv = new Ajv({ allErrors: true });
        addFormats(this.ajv);
        
        const schemaPath = path.join(__dirname, '..', '..', 'schemas');
        this.scorecardSchema = JSON.parse(fs.readFileSync(path.join(schemaPath, 'scorecard.schema.json'), 'utf8'));
        this.validateScorecard = this.ajv.compile(this.scorecardSchema);
    }

    calculateGrade(score) {
        if (score >= 90) return 'A';
        if (score >= 80) return 'B';
        if (score >= 70) return 'C';
        if (score >= 60) return 'D';
        return 'F';
    }

    calculateHealthStatus(score) {
        if (score >= 90) return 'Excellent';
        if (score >= 80) return 'Good';
        if (score >= 70) return 'Fair';
        if (score >= 60) return 'Poor';
        return 'Critical';
    }

    run(ruleResultsPath, outputDir) {
        this.logger.info('Module 4: Scoring Engine started.');
        
        if (!fs.existsSync(ruleResultsPath)) {
            throw new Error(`Rule results file not found: ${ruleResultsPath}`);
        }
        
        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
        }

        const ruleResults = JSON.parse(fs.readFileSync(ruleResultsPath, 'utf8'));
        const triggered = ruleResults.triggered_rules || [];

        const categoriesMap = {};
        const penalties = [];
        let totalPenalty = 0;

        for (const rule of triggered) {
            const penalty = rule.score_weight || 5; // Default penalty if none provided
            
            if (!categoriesMap[rule.category]) {
                categoriesMap[rule.category] = { penalty: 0 };
            }
            
            categoriesMap[rule.category].penalty += penalty;
            totalPenalty += penalty;

            penalties.push({
                rule_id: rule.rule_id,
                category: rule.category,
                penalty: penalty,
                evidence: rule.evidence
            });
        }

        const overallScore = Math.max(0, 100 - totalPenalty);
        
        const categories = {};
        for (const [catName, data] of Object.entries(categoriesMap)) {
            const catScore = Math.max(0, 100 - data.penalty);
            categories[catName] = {
                score: catScore,
                grade: this.calculateGrade(catScore),
                penalties_applied: data.penalty
            };
        }

        const scorecard = {
            metadata: {
                contract_version: "1.0",
                engine_version: "1.0",
                plugin: this.pluginManifest.plugin_name,
                generated_at: new Date().toISOString()
            },
            overall_score: overallScore,
            health_status: this.calculateHealthStatus(overallScore),
            grade: this.calculateGrade(overallScore),
            categories: categories,
            penalties: penalties
        };

        if (!this.validateScorecard(scorecard)) {
            this.logger.error('Scoring Engine produced invalid schema', { errors: this.validateScorecard.errors });
            throw new Error('Scoring Engine produced invalid schema');
        }

        const outPath = path.join(outputDir, 'scorecard.json');
        fs.writeFileSync(outPath, JSON.stringify(scorecard, null, 2));

        this.logger.info(`Module 4: Scoring Engine completed. Overall Score: ${overallScore} (${this.calculateGrade(overallScore)})`);
        return scorecard;
    }
}

module.exports = ScoringEngine;
