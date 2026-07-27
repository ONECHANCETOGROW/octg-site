const fs = require('fs');
const path = require('path');
const Logger = require('./logger');
const events = require('./events');
const Extractor = require('../modules/01_extractor');
const KnowledgeBuilder = require('../modules/02_knowledge');
const RuleEngine = require('../modules/03_rules');
const ScoringEngine = require('../modules/04_scoring');
const OpportunityEngine = require('../modules/05_opportunities');
const RecommendationEngine = require('../modules/06_recommendations');
const ExecutiveSummaryEngine = require('../modules/07_executive_summary');
const ContractBuilder = require('../modules/08_contract');

class Pipeline {
    constructor(workspaceRoot, pluginName = 'google_ads', skipExtraction = false) {
        this.workspaceRoot = workspaceRoot;
        this.pluginName = pluginName;
        this.skipExtraction = skipExtraction;
        this.logger = new Logger(path.basename(workspaceRoot));
        this.originalDir = path.join(workspaceRoot, '01-original');
        this.extractedDir = path.join(workspaceRoot, '02-extracted');
        this.knowledgeDir = path.join(workspaceRoot, '03-knowledge');
        this.rulesDir = path.join(workspaceRoot, '04-rules');
        this.scoringDir = path.join(workspaceRoot, '05-scoring');
        this.opportunitiesDir = path.join(workspaceRoot, '06-opportunities');
        this.recommendationsDir = path.join(workspaceRoot, '07-recommendations');
        this.summaryDir = path.join(workspaceRoot, '08-executive_summary');
        this.contractDir = path.join(workspaceRoot, '09-contract');
        
        // Ensure directories
        if (!fs.existsSync(this.extractedDir)) {
            fs.mkdirSync(this.extractedDir, { recursive: true });
        }
    }

    loadManifest() {
        const manifestPath = path.join(__dirname, '..', 'plugins', this.pluginName, 'plugin.json');
        if (!fs.existsSync(manifestPath)) {
            throw new Error(`Plugin manifest not found: ${manifestPath}`);
        }
        return JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
    }

    async runExtraction() {
        events.emit('Extraction Started');
        this.logger.info('Pipeline started: Module 1 Data Extraction');

        try {
            const pluginManifest = this.loadManifest();
            this.logger.info(`Loaded plugin manifest: ${this.pluginName} v${pluginManifest.version}`);

            const extractor = new Extractor(this.logger);
            
            if (!this.skipExtraction) {
                if (!fs.existsSync(this.originalDir)) {
                    throw new Error(`Original data directory not found: ${this.originalDir}`);
                }

                const files = fs.readdirSync(this.originalDir);
                let extractedCount = 0;

                for (const file of files) {
                    const ext = '.' + file.split('.').pop().toLowerCase();
                    if (pluginManifest.supported_file_types.includes(ext)) {
                        const filePath = path.join(this.originalDir, file);
                        const data = await extractor.extractFile(filePath, pluginManifest);
                        
                        // Save normalized output to 02-extracted
                        const outPath = path.join(this.extractedDir, file + '.json');
                        fs.writeFileSync(outPath, JSON.stringify(data, null, 2));
                        extractedCount++;
                    } else {
                        this.logger.warn(`Skipping unsupported file: ${file}`);
                    }
                }

                this.logger.info(`Extraction completed. Processed ${extractedCount} files.`);
                events.emit('Extraction Completed');
                
                // --- MODULE 2: KNOWLEDGE BUILDER ---
                events.emit('Knowledge Generation Started');
                const kb = new KnowledgeBuilder(this.logger, pluginManifest);
                const knowledge = kb.build(this.extractedDir, this.knowledgeDir);
                events.emit('Knowledge Generation Completed');
            } else {
                this.logger.info('Skipping extraction and knowledge generation phase (AI Data Collection System generated knowledge.json).');
            }

            // --- MODULE 3: RULE ENGINE ---
            events.emit('Rule Evaluation Started');
            const ruleEngine = new RuleEngine(this.logger, pluginManifest);
            const knowledgePath = path.join(this.knowledgeDir, 'knowledge.json');
            ruleEngine.run(knowledgePath, this.rulesDir);
            events.emit('Rule Evaluation Completed');

            // --- MODULE 4: SCORING ENGINE ---
            events.emit('Scoring Started');
            const scoringEngine = new ScoringEngine(this.logger, pluginManifest);
            const rulesPath = path.join(this.rulesDir, 'rule_results.json');
            scoringEngine.run(rulesPath, this.scoringDir);
            events.emit('Scoring Completed');

            // --- MODULE 5: OPPORTUNITY ENGINE ---
            events.emit('Opportunities Started');
            const oppEngine = new OpportunityEngine(this.logger, pluginManifest);
            const opportunitiesPath = path.join(this.opportunitiesDir, 'opportunities.json');
            oppEngine.run(rulesPath, this.opportunitiesDir);
            events.emit('Opportunities Completed');

            // --- MODULE 6: RECOMMENDATION ENGINE ---
            events.emit('Recommendations Started');
            const recEngine = new RecommendationEngine(this.logger, pluginManifest);
            const recommendationsPath = path.join(this.recommendationsDir, 'recommendations.json');
            recEngine.run(opportunitiesPath, this.rulesDir, this.recommendationsDir);
            events.emit('Recommendations Completed');

            // --- MODULE 7: EXECUTIVE SUMMARY ENGINE ---
            events.emit('Executive Summary Started');
            const summaryEngine = new ExecutiveSummaryEngine(this.logger, pluginManifest);
            const summaryPath = path.join(this.summaryDir, 'executive_summary.json');
            summaryEngine.run(path.join(this.scoringDir, 'scorecard.json'), recommendationsPath, this.summaryDir);
            events.emit('Executive Summary Completed');

            // --- MODULE 8: CONTRACT BUILDER ---
            events.emit('Contract Builder Started');
            const contractBuilder = new ContractBuilder(this.logger, pluginManifest);
            contractBuilder.run(
                knowledgePath, 
                path.join(this.scoringDir, 'scorecard.json'), 
                opportunitiesPath, 
                recommendationsPath, 
                summaryPath, 
                this.contractDir
            );
            events.emit('Contract Builder Completed');

        } catch (error) {
            this.logger.error('Pipeline Failed', { error: error.message });
            throw error;
        } finally {
            this.logger.export(this.workspaceRoot);
        }
    }
}

module.exports = Pipeline;
