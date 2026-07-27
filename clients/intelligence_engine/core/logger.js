const fs = require('fs');
const path = require('path');

class Logger {
    constructor(auditId) {
        this.auditId = auditId;
        this.logs = [];
    }

    info(message, context = {}) {
        this.log('INFO', message, context);
    }

    warn(message, context = {}) {
        this.log('WARN', message, context);
    }

    error(message, context = {}) {
        this.log('ERROR', message, context);
    }

    log(level, message, context) {
        const entry = {
            timestamp: new Date().toISOString(),
            level,
            message,
            context
        };
        this.logs.push(entry);
        console.log(`[${entry.timestamp}] [${level}] ${message}`, context);
    }

    export(workspacePath) {
        const logPath = path.join(workspacePath, 'extraction.log.json');
        fs.writeFileSync(logPath, JSON.stringify(this.logs, null, 2));
    }
}

module.exports = Logger;
