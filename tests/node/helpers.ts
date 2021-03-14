interface JestMockedProcess extends NodeJS.Process {
  _original(): NodeJS.Process
}

export function isJestMockedProcess(process: NodeJS.Process): process is JestMockedProcess {
  const mockedProcess = process as JestMockedProcess
  return typeof mockedProcess._original === 'function'
}

// See: https://johann.pardanaud.com/blog/how-to-assert-unhandled-rejection-and-uncaught-exception-with-jest/
export function getOriginalProcess(): NodeJS.Process {
  const mockedProcess = process
  if (!isJestMockedProcess(mockedProcess)) {
    throw new Error('Original process is not available.')
  }

  return mockedProcess._original()
}

const originalJestProcessListeners: {
  uncaughtException: NodeJS.UncaughtExceptionListener[]
  unhandledRejection: NodeJS.UnhandledRejectionListener[]
} = {
  uncaughtException: [],
  unhandledRejection: [],
}

export function beforeTestAssertingProcessEvents(): void {
  getOriginalProcess()
    .listeners('uncaughtException')
    .forEach((listener) => {
      originalJestProcessListeners['uncaughtException'].push(listener)
      getOriginalProcess().off('uncaughtException', listener)
    })

  getOriginalProcess()
    .listeners('unhandledRejection')
    .forEach((listener) => {
      originalJestProcessListeners['unhandledRejection'].push(listener)
      getOriginalProcess().off('unhandledRejection', listener)
    })
}

export function afterTestAssertingProcessEvents(): void {
  // eslint-disable-next-line
  while (true) {
    const exceptionListener = originalJestProcessListeners['uncaughtException'].pop()
    if (exceptionListener === undefined) {
      break
    }
    getOriginalProcess().on('uncaughtException', exceptionListener)
  }

  // eslint-disable-next-line
  while (true) {
    const rejectionListener = originalJestProcessListeners['unhandledRejection'].pop()
    if (rejectionListener === undefined) {
      break
    }
    getOriginalProcess().on('unhandledRejection', rejectionListener)
  }
}
