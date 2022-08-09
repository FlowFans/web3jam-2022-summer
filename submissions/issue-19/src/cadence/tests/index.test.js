import { adminTestCases } from './cases/admin.js'
import { streamTestCases } from './cases/stream.js'
import { vestingTestCases } from './cases/vesting'

describe('Cadence test case', () => {
  adminTestCases()
  streamTestCases()
  vestingTestCases()
})
