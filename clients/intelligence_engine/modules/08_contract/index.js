const fs = require('fs');
const path = require('path');
const Ajv = require('ajv');
const addFormats = require('ajv-formats');

class ContractBuilder {
    constructor(logger, pluginManifest) {
        this.logger = logger;
        this.pluginManifest = pluginManifest;
        
        this.ajv = new Ajv({ allErrors: true });
        addFormats(this.ajv);
        
        const schemaPath = path.join(__dirname, '..', '..', 'schemas');
        this.contractSchema = JSON.parse(fs.readFileSync(path.join(schemaPath, 'intelligence.schema.json'), 'utf8'));
        this.validateContract = this.ajv.compile(this.contractSchema);
    }

    run(knowledgePath, scorecardPath, opportunitiesPath, recommendationsPath, summaryPath, outputDir) {
        this.logger.info('Module 8: Contract Builder started.');
        
        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
        }

        const contract = {
            metadata: {
                contract_version: "1.0",
                engine_version: "1.0",
                plugin: this.pluginManifest.plugin_name,
                generated_at: new Date().toISOString()
            },
            knowledge: JSON.parse(fs.readFileSync(knowledgePath, 'utf8')),
            scorecard: JSON.parse(fs.readFileSync(scorecardPath, 'utf8')),
            opportunities: JSON.parse(fs.readFileSync(opportunitiesPath, 'utf8')),
            recommendations: JSON.parse(fs.readFileSync(recommendationsPath, 'utf8')),
            executive_summary: JSON.parse(fs.readFileSync(summaryPath, 'utf8'))
        };

        if (!this.validateContract(contract)) {
            this.logger.error('Contract Builder produced invalid schema', { errors: this.validateContract.errors });
            throw new Error('Contract Builder produced invalid schema');
        }

        const outPath = path.join(outputDir, 'intelligence.json');
        fs.writeFileSync(outPath, JSON.stringify(contract, null, 2));

        this.logger.info(`Module 8: Contract Builder completed successfully.`);
        return contract;
    }
}

module.exports = ContractBuilder;
