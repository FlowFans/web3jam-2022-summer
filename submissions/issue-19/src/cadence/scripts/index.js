import t from '@onflow/types'
import { fclInit, buildAndSendTrx, buildAndExecScript } from '../utils/index.js'
import fcl from '@onflow/fcl'
import { accountAddr, FLOWTokenAddr } from '../config/constants.js'
import { test1Addr, test2Addr, test1Authz, test2Authz } from '../utils/authz.js'

export const mintFlowToken = async (address, amount) => {
  await buildAndSendTrx('mintFlowToken', [fcl.arg(address, t.Address), fcl.arg(amount, t.UFix64)])
}

const testNetAddr = '0x3c09a556ecca42dc'

const main = async () => {
  // fcl init and load config
  fclInit()
  let currentTimestamp = await buildAndExecScript('getTimestamp')
  console.log(currentTimestamp)

  let stats = await buildAndExecScript('getUserStats', [fcl.arg(testNetAddr, t.Address)])

  console.log(stats)

  let totalSupply = await buildAndExecScript('getNFTTotalSupply')

  console.log(totalSupply)

  // let res = await buildAndSendTrx('createStream', [
  //   fcl.arg('flowTokenVault', t.String),
  //   fcl.arg('30.55', t.UFix64),
  //   fcl.arg(true, t.Bool), // revocable
  //   fcl.arg(false, t.Bool), // transferable
  //   fcl.arg((Number(currentTimestamp) + 3200).toFixed(2), t.UFix64), // startTimestamp
  //   fcl.arg((Number(currentTimestamp) + 30000).toFixed(2), t.UFix64), // endTimestamp
  //   fcl.arg(testNetAddr, t.Address),
  // ])
  // console.log(res)
  // console.log(await buildAndExecScript('getVestingCount'), 'stream count')

  // let res = await buildAndExecScript('getVestingCount')
  // console.log(res, 'vesting count')
  // let res = await buildAndSendTrx('createVesting', [
  //   fcl.arg('flowTokenVault', t.String),
  //   fcl.arg(true, t.Bool), // revocable
  //   fcl.arg(false, t.Bool), // transferable
  //   fcl.arg(testNetAddr, t.Address), // receiver
  //   fcl.arg((Number(currentTimestamp) + 86400).toFixed(2), t.UFix64), // start time
  //   fcl.arg('172800.0', t.UFix64), // cliff duration
  //   fcl.arg('100.0', t.UFix64), // cliff amount
  //   fcl.arg(10, t.Int8), // steps
  //   fcl.arg('43200.0', t.UFix64), // step duration
  //   fcl.arg('20.0', t.UFix64), // step amount
  // ])

  // res = await buildAndSendTrx('createSimpleVesting', [
  //   fcl.arg('fusdVault', t.String),
  //   fcl.arg(true, t.Bool), // revocable
  //   fcl.arg(true, t.Bool), // transferable
  //   fcl.arg(test2Addr, t.Address), // receiver
  //   fcl.arg((Number(currentTimestamp) + 1000).toFixed(2), t.UFix64), // start time
  //   fcl.arg(3, t.Int8), // steps
  //   fcl.arg('2.0', t.UFix64), // step duration
  //   fcl.arg('30.0', t.UFix64), // step amount
  // ])

  // console.log(res)

  // console.log(res)

  // res = await buildAndExecScript('getNFTMetadata', [fcl.arg(6, t.UInt64)])
  // console.log(res)
  let payments = await buildAndExecScript('getOutgoingPayment', [fcl.arg('0xb797a88390357df4', t.Address)])

  console.log(payments)

  // res = await buildAndExecScript('getTicketMetadata', [
  //   fcl.arg(test2Addr, t.Address),
  //   fcl.arg(9, t.UInt64),
  // ])

  // console.log(JSON.stringify(res))
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error)
    process.exit(1)
  })
