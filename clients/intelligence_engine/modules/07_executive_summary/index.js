const fs = require('fs');
const path = require('path');
const Ajv = require('ajv');
const addFormats = require('ajv-formats');

class ExecutiveSummaryEngine {
    constructor(logger, pluginManifest) {
        this.logger = logger;
        this.pluginManifest = pluginManifest;
        
        this.ajv = new Ajv({ allErrors: true });
        addFormats(this.ajv);
        
        const schemaPath = path.join(__dirname, '..', '..', 'schemas');
        this.summarySchema = JSON.parse(fs.readFileSync(path.join(schemaPath, 'executive_summary.schema.json'), 'utf8'));
        this.validateSummary = this.ajv.compile(this.summarySchema);
    }

    run(scorecardPath, recommendationsPath, outputDir) {
        this.logger.info('Module 7: Executive Summary Engine started.');
        
        if (!fs.existsSync(scorecardPath) || !fs.existsSync(recommendationsPath)) {
            throw new Error(`Required inputs for Executive Summary not found.`);
        }
        
        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
        }

        const scorecard = JSON.parse(fs.readFileSync(scorecardPath, 'utf8'));
        const recData = JSON.parse(fs.readFileSync(recommendationsPath, 'utf8'));
        
        const highPriorityRecs = recData.recommendations.filter(r => r.priority === 'High' || r.priority === 'Critical');
        
        const summaryData = {
            metadata: {
                contract_version: "1.0",
                engine_version: "1.0",
                plugin: this.pluginManifest.plugin_name,
                generated_at: new Date().toISOString()
            },
            executive_summary: `The marketing account has achieved an overall health score of ${scorecard.overall_score} (${scorecard.grade}). While there are performing segments, immediate attention is required in key areas to prevent budget waste.`,
            biggest_wins: [
                "Active campaigns driving baseline conversions."
            ],
            biggest_risks: highPriorityRecs.map(r => r.why_it_matters),
            immediate_actions: highPriorityRecs.map(r => r.what_to_change),
            long_term_strategy: "Focus on reallocating wasted spend from poor performing segments into top converting campaigns to scale ROI.",
            overall_business_assessment: scorecard.overall_score > 70 ? "Stable but needs optimization." : "At Risk - Requires immediate turnaround."
        };

        // Merge AI-generated executive summary from knowledge.json if it exists
        const knowledgePath = path.join(scorecardPath, '..', '..', '03-knowledge', 'knowledge.json');
        if (fs.existsSync(knowledgePath)) {
            const knowledge = JSON.parse(fs.readFileSync(knowledgePath, 'utf8'));
            if (knowledge.entities && knowledge.entities.ai_executive_summary && knowledge.entities.ai_executive_summary.length > 0) {
                const aiSummary = knowledge.entities.ai_executive_summary[0];
                
                const parseArr = (val) => {
                    if (Array.isArray(val)) return val;
                    if (typeof val === 'string' && val.startsWith('[')) {
                        try { return JSON.parse(val); } catch(e) { return [val]; }
                    }
                    return val ? [val] : [];
                };

                if (aiSummary.executive_summary) summaryData.executive_summary = aiSummary.executive_summary;
                
                const wins = parseArr(aiSummary.biggest_wins);
                if (wins.length > 0) summaryData.biggest_wins = wins;
                
                const risks = parseArr(aiSummary.biggest_risks);
                if (risks.length > 0) summaryData.biggest_risks = summaryData.biggest_risks.concat(risks);
                
                const actions = parseArr(aiSummary.immediate_actions);
                if (actions.length > 0) summaryData.immediate_actions = summaryData.immediate_actions.concat(actions);
                
                if (aiSummary.long_term_strategy) summaryData.long_term_strategy = aiSummary.long_term_strategy;
            }
        }

        // Fallbacks if empty
        if (summaryData.biggest_risks.length === 0) summaryData.biggest_risks.push("No immediate high-priority risks detected.");
        if (summaryData.immediate_actions.length === 0) summaryData.immediate_actions.push("Continue monitoring campaign performance.");

        if (!this.validateSummary(summaryData)) {
            this.logger.error('Executive Summary Engine produced invalid schema', { errors: this.validateSummary.errors });
            throw new Error('Executive Summary Engine produced invalid schema');
        }

        const outPath = path.join(outputDir, 'executive_summary.json');
        fs.writeFileSync(outPath, JSON.stringify(summaryData, null, 2));

        this.logger.info(`Module 7: Executive Summary Engine completed.`);
        return summaryData;
    }
}

module.exports = ExecutiveSummaryEngine;
