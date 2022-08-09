import t from '@onflow/types'
import fcl from '@onflow/fcl'
import dotenv from 'dotenv'
import moment from 'moment'
import { accountAddr } from '../../config/constants.js'
import { test1Authz, test2Authz, test1Addr, test2Addr } from '../../utils/authz'
import { buildAndExecScript, fclInit, buildAndSendTrx, sleep } from '../../utils/index'
import { getBal, transferToken } from '../../scripts/helper.js'
let accountBal = 0
export const adminTestCases = () =>
  describe('Admin test cases', () => {
    beforeAll(() => {
      dotenv.config()
      return fclInit()
    })

    test('Check user init state', async () => {
      let res = await buildAndExecScript('checkInit', [fcl.arg(accountAddr, t.Address)])
      expect(res).toBe(true)

      res = await buildAndExecScript('checkInit', [fcl.arg(test1Addr, t.Address)])
      expect(res).toBe(false)

      res = await buildAndExecScript('checkInit', [fcl.arg(test2Addr, t.Address)])
      expect(res).toBe(false)

      // res = await buildAndSendTrx('setupAccount', [], test1Authz())
      // expect(res).not.toBeNull()

      // res = await buildAndExecScript('checkInit', [fcl.arg(test1Addr, t.Address)])
      // expect(res).toBe(true)

      res = await buildAndSendTrx('setupAccount', [], test2Authz())
      expect(res).not.toBeNull()

      res = await buildAndExecScript('checkInit', [fcl.arg(test2Addr, t.Address)])
      expect(res).toBe(true)

      res = await buildAndExecScript('checkInit', [fcl.arg(test1Addr, t.Address)])
      expect(res).toBe(false)
    })

    test('Test admin state', async () => {
      let res = await buildAndExecScript('getPause')
      expect(res).toBe(false)

      res = await buildAndSendTrx('setPause', [fcl.arg(true, t.Bool)])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getPause')
      expect(res).toBe(true)

      // wil failed
      res = await buildAndSendTrx('createStream', [
        fcl.arg('fusdVault', t.String),
        fcl.arg('100.0', t.UFix64),
        fcl.arg(true, t.Bool),
        fcl.arg(false, t.Bool),
        fcl.arg((moment().unix() + 1000).toFixed(2), t.UFix64),
        fcl.arg((moment().unix() + 5000).toFixed(2), t.UFix64),
        fcl.arg(accountAddr, t.Address),
      ])
      expect(res).toBe(null)

      res = await buildAndSendTrx('setPause', [fcl.arg(false, t.Bool)])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndSendTrx('createStream', [
        fcl.arg('fusdVault', t.String),
        fcl.arg('100.0', t.UFix64),
        fcl.arg(true, t.Bool),
        fcl.arg(false, t.Bool),
        fcl.arg((moment().unix() + 1000).toFixed(2), t.UFix64),
        fcl.arg((moment().unix() + 5000).toFixed(2), t.UFix64),
        fcl.arg(accountAddr, t.Address),
      ])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getNFTTotalSupply')
      expect(Number(res)).toBe(1)

      res = await buildAndExecScript('getNFTMetadata', [fcl.arg(1, t.UInt64)])
      expect(res).not.toBeNull()
      console.log(res)
    })

    test('test grace duration', async () => {
      let res = await buildAndExecScript('getGraceDuration')
      expect(Number(res)).toBe(300)

      res = await buildAndSendTrx('createStream', [
        fcl.arg('fusdVault', t.String),
        fcl.arg('100.0', t.UFix64),
        fcl.arg(true, t.Bool),
        fcl.arg(false, t.Bool),
        fcl.arg((moment().unix() + 200).toFixed(2), t.UFix64),
        fcl.arg((moment().unix() + 5000).toFixed(2), t.UFix64),
        fcl.arg(accountAddr, t.Address),
      ])
      expect(res).toBe(null)

      res = await buildAndSendTrx('setGraceDuration', [fcl.arg('200.0', t.UFix64)])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getGraceDuration')
      expect(Number(res)).toBe(200)

      accountBal = await getBal('FUSD', accountAddr)
      let currentTimestamp = await buildAndExecScript('getTimestamp')
      res = await buildAndSendTrx('createStream', [
        fcl.arg('fusdVault', t.String),
        fcl.arg('100.0', t.UFix64),
        fcl.arg(true, t.Bool),
        fcl.arg(false, t.Bool),
        fcl.arg((Number(currentTimestamp) + 250).toFixed(2), t.UFix64),
        fcl.arg((Number(currentTimestamp) + 550).toFixed(2), t.UFix64),
        fcl.arg(test1Addr, t.Address),
      ])

      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await getBal('FUSD', accountAddr)
      expect(Number(res)).toBe(accountBal - 100)

      res = await buildAndExecScript('getUserUnclaimTickets', [fcl.arg(test2Addr, t.Address)])
      expect(res.length).toBe(0)

      res = await buildAndExecScript('getUserIncomePayment', [fcl.arg(test1Addr, t.Address)])
      expect(res.length).toBe(0)
      res = await buildAndExecScript('getUserUnclaimTickets', [fcl.arg(test1Addr, t.Address)])
      console.log(res)
      expect(res.length).toBe(1)

      res = await buildAndSendTrx('createStream', [
        fcl.arg('fusdVault', t.String),
        fcl.arg('1.0', t.UFix64),
        fcl.arg(true, t.Bool),
        fcl.arg(false, t.Bool),
        fcl.arg((Number(currentTimestamp) + 250).toFixed(2), t.UFix64),
        fcl.arg((Number(currentTimestamp) + 550).toFixed(2), t.UFix64),
        fcl.arg(test1Addr, t.Address),
      ])

      expect(res).not.toBeNull()
      expect(res.status).toBe(4)
    })

    test('User claim ticket', async () => {
      // let res = await buildAndSendTrx('claimAllTicket', [], test1Authz())
      let res = await buildAndSendTrx('claimTicket', [fcl.arg(2, t.UInt64)], test1Authz())
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getUserUnclaimTickets', [fcl.arg(test1Addr, t.Address)])
      expect(res.length).toBe(1)

      res = await buildAndExecScript('getUserIncomePayment', [fcl.arg(test1Addr, t.Address)])
      expect(res.length).toBe(1)
      console.log(res)
    })

    test('test minimum amount', async () => {
      let res = await buildAndExecScript('getMinimumPaymentAmount')
      expect(Number(res)).toBe(0.1)

      res = await buildAndSendTrx('createStream', [
        fcl.arg('fusdVault', t.String),
        fcl.arg('0.09', t.UFix64),
        fcl.arg(true, t.Bool),
        fcl.arg(false, t.Bool),
        fcl.arg((moment().unix() + 1000).toFixed(2), t.UFix64),
        fcl.arg((moment().unix() + 5000).toFixed(2), t.UFix64),
        fcl.arg(accountAddr, t.Address),
      ])
      expect(res).toBe(null)

      res = await buildAndSendTrx('setMinimumPayment', [fcl.arg('0.01', t.UFix64)])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getMinimumPaymentAmount')
      expect(Number(res)).toBe(0.01)

      res = await buildAndSendTrx('createStream', [
        fcl.arg('fusdVault', t.String),
        fcl.arg('0.09', t.UFix64),
        fcl.arg(true, t.Bool),
        fcl.arg(false, t.Bool),
        fcl.arg((moment().unix() + 1000).toFixed(2), t.UFix64),
        fcl.arg((moment().unix() + 5000).toFixed(2), t.UFix64),
        fcl.arg(accountAddr, t.Address),
      ])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)
    })

    test('test melody commision ratio set', async () => {
      let res = await buildAndExecScript('getCommision')
      expect(Number(res)).toBe(0.01)

      res = await buildAndSendTrx('setCommision', [fcl.arg('0.0', t.UFix64)])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getCommision')
      expect(Number(res)).toBe(0)
    })

    // ===

    test('template', async () => {
      let res = await buildAndExecScript('checkInit', [fcl.arg(accountAddr, t.Address)])
      expect(res).toBe(true)

      res = await buildAndSendTrx('setupAccount', [], test2Authz())
      expect(res).not.toBeNull()
    })
  })
