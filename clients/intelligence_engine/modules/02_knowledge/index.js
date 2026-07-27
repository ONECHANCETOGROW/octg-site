const fs = require('fs');
const path = require('path');
const Ajv = require('ajv');
const addFormats = require('ajv-formats');

class KnowledgeBuilder {
    constructor(logger, pluginManifest) {
        this.logger = logger;
        this.pluginManifest = pluginManifest;
        this.ajv = new Ajv({ allErrors: true });
        addFormats(this.ajv);
        
        // Load Schemas
        const schemaPath = path.join(__dirname, '..', '..', 'schemas');
        this.extractedSchema = JSON.parse(fs.readFileSync(path.join(schemaPath, 'extracted.schema.json'), 'utf8'));
        this.knowledgeSchema = JSON.parse(fs.readFileSync(path.join(schemaPath, 'knowledge.schema.json'), 'utf8'));
        
        this.validateExtracted = this.ajv.compile(this.extractedSchema);
        this.validateKnowledge = this.ajv.compile(this.knowledgeSchema);
    }

    identifyEntity(row) {
        const rowKeys = Object.keys(row);
        const entities = this.pluginManifest.entities || {};

        for (const [entityName, config] of Object.entries(entities)) {
            const reqCols = config.required_columns || [];
            if (reqCols.length > 0 && reqCols.every(col => rowKeys.includes(col))) {
                return entityName;
            }
        }
        return 'unknown';
    }

    cleanNumeric(val) {
        if (typeof val === 'number') return val;
        if (typeof val === 'string') {
            const stripped = val.replace(/[^0-9.-]+/g, '');
            const parsed = parseFloat(stripped);
            return isNaN(parsed) ? 0 : parsed;
        }
        return 0;
    }

    build(extractedDir, outputDir) {
        this.logger.info('Module 2: Knowledge Builder started.');
        
        if (!fs.existsSync(extractedDir)) {
            throw new Error(`Directory not found: ${extractedDir}`);
        }
        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
        }

        const files = fs.readdirSync(extractedDir).filter(f => f.endsWith('.json'));
        
        const knowledge = {
            metadata: {
                contract_version: "1.0",
                engine_version: "1.0",
                plugin: this.pluginManifest.plugin_name,
                plugin_version: this.pluginManifest.version,
                generated_at: new Date().toISOString(),
                client_id: null,
                audit_id: null,
                source_files: files
            },
            statistics: {
                total_spend: 0,
                total_conversions: 0,
                total_clicks: 0
            },
            entities: {
                campaigns: [],
                searchTerms: [],
                keywords: [],
                devices: [],
                locations: [],
                conversions: [],
                landingPages: [],
                tracking: [],
                budget: [],
                audiences: [],
                extensions: []
            }
        };

        for (const file of files) {
            const filePath = path.join(extractedDir, file);
            const rawData = JSON.parse(fs.readFileSync(filePath, 'utf8'));

            // Validate against Extracted Schema
            if (!this.validateExtracted(rawData)) {
                this.logger.error(`File ${file} failed extracted schema validation`, { errors: this.validateExtracted.errors });
                continue;
            }

            // Identify and group
            for (let i = 0; i < rawData.length; i++) {
                const row = rawData[i];
                // Skip entirely empty rows
                if (Object.keys(row).length === 0 || Object.values(row).every(v => v === null || v === '')) {
                    continue;
                }

                const entityType = this.identifyEntity(row);

                if (entityType !== 'unknown') {
                    // Clean numeric fields on the row itself (used for
                    // this entity's own table display), but do NOT sum
                    // them into knowledge.statistics here -- see the
                    // single-source resolution after this loop for why.
                    if (row.spend !== undefined) {
                        row.spend = this.cleanNumeric(row.spend);
                    }
                    if (row.conversions !== undefined) {
                        row.conversions = this.cleanNumeric(row.conversions);
                    }
                    if (row.clicks !== undefined) {
                        row.clicks = this.cleanNumeric(row.clicks);
                    }

                    if (knowledge.entities[entityType]) {
                        knowledge.entities[entityType].push(row);
                    } else {
                        knowledge.entities[entityType] = [row]; // Fallback
                    }
                } else {
                    this.logger.warn(`Could not classify row in ${file}`, { row });
                }
            }
        }

        // Resolve the top-line totals from exactly ONE entity type, not
        // by summing every classified row across every uploaded file.
        // campaigns.csv, keywords.csv, and search_terms.csv (all
        // supported per plugin.json's supported_reports) describe the
        // SAME underlying account spend at different granularities, not
        // separate spend to add together -- summing all of them (as this
        // used to do, incrementing total_spend/conversions/clicks inside
        // the row loop above for every classified row regardless of type)
        // silently multiplied a real account's totals by however many
        // report files happened to be uploaded together for one audit.
        // Prefer the most account-level breakdown available.
        const statsSourceKey = ['campaigns', 'keywords', 'searchTerms'].find(
            key => Array.isArray(knowledge.entities[key]) && knowledge.entities[key].length > 0
        );
        if (statsSourceKey) {
            for (const row of knowledge.entities[statsSourceKey]) {
                if (typeof row.spend === 'number') knowledge.statistics.total_spend += row.spend;
                if (typeof row.conversions === 'number') knowledge.statistics.total_conversions += row.conversions;
                if (typeof row.clicks === 'number') knowledge.statistics.total_clicks += row.clicks;
            }
        }

        // Calculate specific stats
        for (const [key, arr] of Object.entries(knowledge.entities)) {
            knowledge.statistics[`${key}_count`] = arr.length;
        }

        // Validate final knowledge payload
        if (!this.validateKnowledge(knowledge)) {
            this.logger.error('Final knowledge payload failed schema validation', { errors: this.validateKnowledge.errors });
            throw new Error('Knowledge Builder produced invalid schema');
        }

        const outPath = path.join(outputDir, 'knowledge.json');
        fs.writeFileSync(outPath, JSON.stringify(knowledge, null, 2));
        
        this.logger.info('Module 2: Knowledge Builder completed successfully.');
        return knowledge;
    }
}

module.exports = KnowledgeBuilder;
