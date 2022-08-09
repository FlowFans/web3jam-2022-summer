import t from '@onflow/types'
import { fclInit, buildAndExecScript, buildAndSendTrx } from '../utils/index.js'
import fcl from '@onflow/fcl'
import { accountAddr, FLOWTokenAddr } from '../config/constants.js'
import { test1Addr, test2Addr, test1Authz, test2Authz } from '../utils/authz.js'
import { getBal, mintToken, transferToken } from '../scripts/helper.js'

const main = async () => {
  // fcl init and load config
  fclInit()

  console.log(await getBal('FLOW', accountAddr))

  await buildAndSendTrx('initTokens', [], test1Authz())
  await buildAndSendTrx('initTokens', [], test2Authz())
  await buildAndSendTrx('initTokens', [])

  // mint FUSD
  // await mintToken('FUSD', test1Addr, 5000)
  await mintToken('FUSD', test2Addr, 200)
  await mintToken('FUSD', accountAddr, 10000)

  console.log('FUSD', await getBal('FUSD', test1Addr))
  console.log('FUSD', await getBal('FUSD', test2Addr))
  console.log('FUSD', await getBal('FUSD', accountAddr))

  // mint Kibble
  // await mintToken('KIBBLE', test1Addr, 1000)
  // await mintToken('KIBBLE', test2Addr, 1000)
  await mintToken('KIBBLE', accountAddr, 10000)

  console.log('KIBBLE', await getBal('KIBBLE', test1Addr))
  console.log('KIBBLE', await getBal('KIBBLE', test2Addr))
  console.log('KIBBLE', await getBal('KIBBLE', accountAddr))

  // transfer FLOW
  await transferToken('FLOW', test1Addr, 999.999)
  await transferToken('FLOW', test2Addr, 999.999)

  console.log('FLOW', await getBal('FLOW', test1Addr))
  console.log('FLOW', await getBal('FLOW', test2Addr))
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error)
    process.exit(1)
  })
