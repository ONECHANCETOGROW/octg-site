const fs = require('fs');
const csv = require('csv-parser');
const xlsx = require('xlsx');

class Extractor {
    constructor(logger) {
        this.logger = logger;
    }

    normalizeHeader(header, pluginManifest) {
        const lowerHeader = header.trim().toLowerCase();
        const map = pluginManifest.header_normalization || {};
        return map[lowerHeader] || lowerHeader;
    }

    async parseCSV(filePath, pluginManifest) {
        return new Promise((resolve, reject) => {
            const results = [];
            fs.createReadStream(filePath)
                .pipe(csv({
                    mapHeaders: ({ header }) => this.normalizeHeader(header, pluginManifest)
                }))
                .on('data', (data) => results.push(data))
                .on('end', () => resolve(results))
                .on('error', (err) => reject(err));
        });
    }

    parseXLSX(filePath, pluginManifest) {
        const workbook = xlsx.readFile(filePath);
        const sheetName = workbook.SheetNames[0];
        const sheet = workbook.Sheets[sheetName];
        
        // Convert to JSON
        const rawData = xlsx.utils.sheet_to_json(sheet, { defval: null });
        
        // Normalize headers
        const normalizedData = rawData.map(row => {
            const newRow = {};
            for (let key in row) {
                const newKey = this.normalizeHeader(key, pluginManifest);
                newRow[newKey] = row[key];
            }
            return newRow;
        });

        return normalizedData;
    }

    async extractFile(filePath, pluginManifest) {
        const ext = filePath.toLowerCase().split('.').pop();
        
        this.logger.info(`Extracting file: ${filePath}`, { ext });
        
        try {
            let data;
            if (ext === 'csv') {
                data = await this.parseCSV(filePath, pluginManifest);
            } else if (ext === 'xlsx') {
                data = this.parseXLSX(filePath, pluginManifest);
            } else {
                throw new Error(`Unsupported file type: ${ext}`);
            }
            
            this.logger.info(`Successfully extracted ${data.length} rows.`);
            return data;
        } catch (err) {
            this.logger.error(`Failed to extract file`, { error: err.message });
            throw err;
        }
    }
}

module.exports = Extractor;
