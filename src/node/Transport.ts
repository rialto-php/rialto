import http = require('http')
import net = require('net')

type TransportHandler = (instruction: string) => (string | Buffer) | Promise<string | Buffer>

/**
 * @internal
 */
export interface Transport {
  /**
   * Starts the server and returns the port number once it's ready.
   */
  serve(handler: TransportHandler): Promise<number>
}

/**
 * @internal
 */
export type HttpTransportOptions = { idleTimeout: number }

/**
 * @internal
 */
export class HttpTransport implements Transport {
  private server: http.Server | undefined
  private idleTimeout: NodeJS.Timeout | undefined

  public constructor(private options: HttpTransportOptions = { idleTimeout: 30 }) {}

  public async serve(handler: TransportHandler): Promise<number> {
    this.server = http.createServer(async (request, response) => {
      const requestData = await this.getDataFromRequest(request)
      const responseData = await handler(requestData)
      this.sendResponseData(response, responseData)
    })

    return this.listenAndGetPort(this.server)
  }

  private async listenAndGetPort(server: http.Server): Promise<number> {
    await new Promise((resolve) => server.listen(resolve))
    this.initIdleTimeout()

    const address = server.address()
    if (!isNetAddressInfo(address)) {
      throw new Error('Server address must be of type net.AddressInfo.')
    }
    return address.port
  }

  private getDataFromRequest(request: http.IncomingMessage): Promise<string> {
    return new Promise((resolve) => {
      const chunks: Buffer[] = []
      request
        .on('data', (chunk: Buffer) => {
          this.delayIdleTimeout()
          chunks.push(chunk)
        })
        .on('end', () => {
          this.delayIdleTimeout()
          resolve(Buffer.concat(chunks).toString())
        })
    })
  }

  private sendResponseData(response: http.ServerResponse, data: string | Buffer): void {
    response.writeHead(200)
    response.end(data)
  }

  private initIdleTimeout() {
    this.idleTimeout = setTimeout(() => {
      throw new Error('The idle timeout has been reached.')
    }, this.options.idleTimeout * 1000)
  }

  private delayIdleTimeout() {
    this.idleTimeout?.refresh()
  }

  public stop(): void {
    this.server?.close()

    if (this.idleTimeout) {
      clearTimeout(this.idleTimeout)
    }
  }
}

function isNetAddressInfo(value: unknown): value is net.AddressInfo {
  const addressInfo = value as net.AddressInfo
  return (
    typeof addressInfo.address === 'string' &&
    typeof addressInfo.family === 'string' &&
    typeof addressInfo.port === 'number'
  )
}
