const path = require('path');
const Pipeline = require('./core/pipeline');
const events = require('./core/events');

// CLI arguments
const args = process.argv.slice(2);
let workspaceRoot = null;
let plugin = 'google_ads';
let skipExtraction = false;

args.forEach(arg => {
    if (arg.startsWith('--workspace=')) {
        workspaceRoot = arg.split('=')[1];
    }
    if (arg.startsWith('--plugin=')) {
        plugin = arg.split('=')[1];
    }
    if (arg === '--skip-extraction') {
        skipExtraction = true;
    }
});

if (!workspaceRoot) {
    console.error('Usage: node index.js --workspace=/path/to/workspace [--plugin=google_ads]');
    process.exit(1);
}

// Bind events for terminal output
events.on('Extraction Started', () => console.log('>>> EVENT: Extraction Started'));
events.on('Extraction Completed', () => console.log('>>> EVENT: Extraction Completed'));
events.on('Knowledge Generation Started', () => console.log('>>> EVENT: Knowledge Generation Started'));
events.on('Knowledge Generation Completed', () => console.log('>>> EVENT: Knowledge Generation Completed'));
events.on('Rule Evaluation Started', () => console.log('>>> EVENT: Rule Evaluation Started'));
events.on('Rule Evaluation Completed', () => console.log('>>> EVENT: Rule Evaluation Completed'));
events.on('Scoring Started', () => console.log('>>> EVENT: Scoring Started'));
events.on('Scoring Completed', () => console.log('>>> EVENT: Scoring Completed'));
events.on('Opportunities Started', () => console.log('>>> EVENT: Opportunities Started'));
events.on('Opportunities Completed', () => console.log('>>> EVENT: Opportunities Completed'));
events.on('Recommendations Started', () => console.log('>>> EVENT: Recommendations Started'));
events.on('Recommendations Completed', () => console.log('>>> EVENT: Recommendations Completed'));
events.on('Executive Summary Started', () => console.log('>>> EVENT: Executive Summary Started'));
events.on('Executive Summary Completed', () => console.log('>>> EVENT: Executive Summary Completed'));
events.on('Contract Builder Started', () => console.log('>>> EVENT: Contract Builder Started'));
events.on('Contract Builder Completed', () => console.log('>>> EVENT: Contract Builder Completed'));

async function main() {
    const pipeline = new Pipeline(path.resolve(workspaceRoot), plugin, skipExtraction);
    try {
        await pipeline.runExtraction();
        console.log('Pipeline finished successfully.');
    } catch (err) {
        console.error('Pipeline crashed:', err.message);
        process.exit(1);
    }
}

main();
