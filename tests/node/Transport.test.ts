/* eslint-disable jest/expect-expect */
import http = require('http')
import { HttpTransport } from '../../src/node/Transport'
import { beforeTestAssertingProcessEvents, afterTestAssertingProcessEvents, getOriginalProcess } from './helpers'
import { mock } from 'jest-mock-extended'

jest.mock('http')

/**
 * Returns a mocked request which immediately returns data when binding `data` and `end` events.
 */
function createRequest() {
  const request = mock<http.IncomingMessage>()
  request.on.mockImplementation((event, listener) => {
    if (event === 'data') {
      listener(Buffer.from('Request data', 'utf-8'))
    } else if (event === 'end') {
      listener()
    }
    return request
  })
  return request
}

/**
 * Returns a mocked response.
 */
function createResponse() {
  return mock<http.ServerResponse>()
}

/**
 * A HTTP server mock implementation with a method to trigger listeners with custom request/response.
 */
const httpServer = (function () {
  const listeners: http.RequestListener[] = []
  const mockImplementation = (listener: http.RequestListener) => {
    listeners.push(listener)
    return {
      listen: (callback: () => void) => callback(),
      address: () => ({ address: '', family: '', port: 1234 }),
      close: () => null,
    }
  }

  return {
    mockImplementation,
    triggerListeners(request: http.IncomingMessage, response: http.ServerResponse) {
      listeners.forEach((listener) => listener(request, response))
    },
  }
})()

beforeAll(() => {
  ;(http.createServer as jest.Mock).mockImplementation(httpServer.mockImplementation)
})

test('Method createServer resolves with the HTTP port number', async () => {
  const transport = new HttpTransport()
  await expect(transport.serve(() => '')).resolves.toBe(1234)
  transport.stop()
})

test('Option idleTimeout is applied', async () => {
  beforeTestAssertingProcessEvents()

  const transport = new HttpTransport({ idleTimeout: 0.001 })
  transport.serve(() => '')

  const uncaughtErrorPromise = new Promise((resolve) => getOriginalProcess().on('uncaughtException', resolve))
  await expect(uncaughtErrorPromise).resolves.toBeInstanceOf(Error)

  transport.stop()
  afterTestAssertingProcessEvents()
})

test('Idle timeout is delayed on each request', async () => {
  const transport = new HttpTransport({ idleTimeout: 0.01 })
  await transport.serve(() => '')

  const interval = setInterval(() => httpServer.triggerListeners(createRequest(), createResponse()), 5)
  await new Promise<void>((resolve) => setTimeout(resolve, 30))

  transport.stop()
  clearInterval(interval)
})

test('Request data is passed to the server handler', async () => {
  const transport = new HttpTransport()

  const handlerPromise = new Promise((resolve) => {
    transport.serve((data) => {
      resolve(data)
      return ''
    })
  })

  httpServer.triggerListeners(createRequest(), createResponse())
  await expect(handlerPromise).resolves.toBe('Request data')

  transport.stop()
})

test('Response contains data returned by the handler', async () => {
  const transport = new HttpTransport()
  await transport.serve(() => 'Response data')

  const response = createResponse()
  httpServer.triggerListeners(createRequest(), response)

  await new Promise<void>(setImmediate)
  transport.stop()

  expect(response.end).toHaveBeenCalledWith('Response data')
})
