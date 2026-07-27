const EventEmitter = require('events');

class PipelineEvents extends EventEmitter {}

const events = new PipelineEvents();

module.exports = events;
