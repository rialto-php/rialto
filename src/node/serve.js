'use strict'

const ConsoleInterceptor = require('./NodeInterceptors/ConsoleInterceptor'),
  Logger = require('./Logger'),
  { HttpTransport } = require('./Transport'),
  ResourceRepository = require('./Data/ResourceRepository'),
  Instruction = require('./Instruction'),
  DataSerializer = require('./Data/Serializer'),
  DataUnserializer = require('./Data/Unserializer')

// Throw unhandled rejections
process.on('unhandledRejection', (error) => {
  throw error
})

// Output the exceptions in JSON format
process.on('uncaughtException', panic)
function panic(error) {
  process.stderr.write(JSON.stringify(DataSerializer.serializeError(error)))
  process.exit(1) // eslint-disable-line
}

// Retrieve the options
let options = process.argv.slice(2)[1]
options = options !== undefined ? JSON.parse(options) : {}

// Intercept Node logs
if (options.log_node_console === true) {
  ConsoleInterceptor.startInterceptingLogs((type, originalMessage) => {
    const level = ConsoleInterceptor.getLevelFromType(type)
    const message = ConsoleInterceptor.formatMessage(originalMessage)

    Logger.log('Node', level, message)
  })
}

// Instanciate the custom connection delegate
const connectionDelegate = new (require(process.argv.slice(2)[0]))(options)

// Instanciate the mandatory services
const resources = new ResourceRepository()
const dataSerializer = new DataSerializer(resources)
const dataUnserializer = new DataUnserializer(resources)

// Start the server with the custom connection delegate
new HttpTransport({ idleTimeout: options.idle_timeout })
  .serve(async (instruction) => {
    const parsedInstruction = new Instruction(JSON.parse(instruction), resources, dataUnserializer)

    const serializeValue = (value) =>
      JSON.stringify({
        logs: Logger.logs(),
        value: dataSerializer.serialize(value),
      })

    const serializeError = (error) =>
      JSON.stringify({
        logs: Logger.logs(),
        value: DataSerializer.serializeError(error),
      })

    return new Promise((resolve) => {
      connectionDelegate
        .handleInstruction(
          parsedInstruction,
          (value) => resolve(serializeValue(value)),
          (error) => resolve(serializeError(error)),
        )
        .catch(panic)
    })
  })
  .then((port) => {
    // Write the server port to the process output
    process.stdout.write(`${port}\n`)
  })
