import t from '@onflow/types'
import { buildAndExecScript, buildAndSendTrx } from '../utils/index.js'
import fcl from '@onflow/fcl'

export const mintToken = async (token, address, amount) => {
  await buildAndSendTrx(`mint${token}`, [
    fcl.arg(address, t.Address),
    fcl.arg(amount.toFixed(2), t.UFix64),
  ])
}

export const transferToken = async (token, address, amount) => {
  await buildAndSendTrx(`transfer${token}`, [
    fcl.arg(address, t.Address),
    fcl.arg(amount.toFixed(2), t.UFix64),
  ])
}

export const getBal = async (token, address) => {
  const bal = await buildAndExecScript(`get${token}`, [fcl.arg(address, t.Address)])
  return bal
}
