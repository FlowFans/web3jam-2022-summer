import t from '@onflow/types'
import fcl from '@onflow/fcl'
import dotenv from 'dotenv'
import moment from 'moment'
import { accountAddr } from '../../config/constants.js'
import { test1Authz, test2Authz, test1Addr, test2Addr } from '../../utils/authz'
import { buildAndExecScript, fclInit, buildAndSendTrx, sleep } from '../../utils/index'
import { getBal, transferToken } from '../../scripts/helper.js'
let paymentId = 0
export const vestingTestCases = () =>
  describe('Vesting test cases', () => {
    beforeAll(() => {
      dotenv.config()
      return fclInit()
    })

    test('Revocable vesting test', async () => {
      let currentTimestamp = await buildAndExecScript('getTimestamp')
      let res = await buildAndExecScript('getVestingCount')
      expect(Number(res)).toBe(0)

      res = await buildAndSendTrx('createVesting', [
        fcl.arg('fusdVault', t.String),
        fcl.arg(true, t.Bool), // revocable
        fcl.arg(false, t.Bool), // transferable
        fcl.arg(test2Addr, t.Address), // receiver
        fcl.arg((Number(currentTimestamp) + 5).toFixed(2), t.UFix64), // start time
        fcl.arg('2.0', t.UFix64), // cliff duration
        fcl.arg('2.0', t.UFix64), // cliff amount
        fcl.arg(3, t.Int8), // steps
        fcl.arg('4.0', t.UFix64), // step duration
        fcl.arg('20.0', t.UFix64), // step amount
      ])

      expect(res).not.toBeNull()
      expect(res.status).toBe(4)
      res = await buildAndExecScript('getVestingCount')
      expect(Number(res)).toBe(1)

      paymentId = 7

      // failed
      // res = await buildAndSendTrx('withdraw', [fcl.arg(paymentId, t.UInt64)], test2Authz())
      // expect(res).toBeNull()

      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      console.log(res, 'paymentInfo')

      await sleep(8000)

      // failed
      res = await buildAndSendTrx('withdraw', [fcl.arg(paymentId, t.UInt64)], test1Authz())
      expect(res).toBeNull()

      res = await buildAndExecScript('getOutgoingPayment', [fcl.arg(accountAddr, t.Address)])
      expect(res).not.toBeNull()
      expect(res.length).toBe(7)
    })

    test('Test transfeable change', async () => {
      let res = await buildAndExecScript('getUserIncomePayment', [fcl.arg(test2Addr, t.Address)])
      expect(res).not.toBeNull()
      expect(res.length).toBe(1)

      res = await buildAndSendTrx(
        'transferTicket',
        [fcl.arg(paymentId, t.UInt64), fcl.arg(test1Addr, t.Address)],
        test2Authz(),
      )
      expect(res).toBeNull()

      res = await buildAndSendTrx('changeTransferable', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      console.log(res, 'paymentInfo')

      res = await buildAndSendTrx(
        'transferTicket',
        [fcl.arg(paymentId, t.UInt64), fcl.arg(test1Addr, t.Address)],
        test2Authz(),
      )
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndSendTrx('changeTransferable', [fcl.arg(paymentId, t.UInt64)])
      expect(res).toBeNull()
    })

    test('Test reovcable payment', async () => {
      let test1Bal = await getBal('FUSD', test1Addr)
      // let test2Bal = await getBal('FUSD', test1Addr)
      let accBal = await getBal('FUSD', accountAddr)

      let res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()

      res = await buildAndSendTrx('withdraw', [fcl.arg(paymentId, t.UInt64)], test1Authz())
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      console.log(res, 'paymentInfo')
      let claimable = Number(res.claimable)

      let commision = await buildAndExecScript('getCommision', [])
      res = await getBal('FUSD', test1Addr)
      // expect(Number(res)).toBe(Number((Number(test1Bal) + claimable * (1 - Number(commision))).toPrecision(4)))
      expect(Number(res)).toBe(Number(test1Bal) + claimable * (1 - Number(commision)))
      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      const { withdrawn, balance, amount } = res
      expect(Number(withdrawn)).toBe(claimable)
      expect(Number(amount)).toBe(Number(withdrawn) + Number(balance))

      // revoke

      res = await buildAndSendTrx('revokePayment', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await getBal('FUSD', accountAddr)
      expect(Number(accBal)).toBe(Number(res) - Number(balance))

      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      console.log(res, 'paymentInfo')
    })

    test('test vesting change revocable', async () => {
      // failed
      let res = await buildAndSendTrx('changeRevocable', [fcl.arg(paymentId, t.UInt64)])
      expect(res).toBeNull()

      let currentTimestamp = await buildAndExecScript('getTimestamp')

      res = await buildAndSendTrx('createVesting', [
        fcl.arg('fusdVault', t.String),
        fcl.arg(true, t.Bool), // revocable
        fcl.arg(true, t.Bool), // transferable
        fcl.arg(test2Addr, t.Address), // receiver
        fcl.arg((Number(currentTimestamp) + 2).toFixed(2), t.UFix64), // start time
        fcl.arg('2.0', t.UFix64), // cliff duration
        fcl.arg('2.0', t.UFix64), // cliff amount
        fcl.arg(3, t.Int8), // steps
        fcl.arg('1.0', t.UFix64), // step duration
        fcl.arg('30.0', t.UFix64), // step amount
      ])

      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      paymentId = 8

      await sleep(5000)

      res = await buildAndSendTrx('changeRevocable', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      expect(res.type).toBe('2')

      res = await buildAndSendTrx('withdraw', [fcl.arg(paymentId, t.UInt64)], test2Authz())
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      await sleep(2500)

      res = await buildAndSendTrx(
        'transferTicket',
        [fcl.arg(paymentId, t.UInt64), fcl.arg(test1Addr, t.Address)],
        test2Authz(),
      )
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      expect(res.ticketInfo.owner).toBe(test1Addr)
      console.log(res, 'paymentInfo')

      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      expect(res.ticketInfo.owner).toBe(test1Addr)
      console.log(res, 'paymentInfo')

      res = await buildAndSendTrx('withdraw', [fcl.arg(paymentId, t.UInt64)], test1Authz())
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndSendTrx('destoryTicket', [fcl.arg(paymentId, t.UInt64)], test1Authz())
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

    })

    test('template', async () => {
      let currentTimestamp = await buildAndExecScript('getTimestamp')
      let res = await buildAndSendTrx('createSimpleVesting', [
        fcl.arg('fusdVault', t.String),
        fcl.arg(true, t.Bool), // revocable
        fcl.arg(true, t.Bool), // transferable
        fcl.arg(test1Addr, t.Address), // receiver
        fcl.arg((Number(currentTimestamp) + 5).toFixed(2), t.UFix64), // start time
        fcl.arg(5, t.Int8), // steps
        fcl.arg('3.0', t.UFix64), // step duration
        fcl.arg('20.0', t.UFix64), // step amount
      ])
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      paymentId = 9

      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      expect(res.ticketInfo.owner).toBe(test1Addr)

      await sleep(6500)
      res = await buildAndSendTrx(
        'transferTicket',
        [fcl.arg(paymentId, t.UInt64), fcl.arg(test2Addr, t.Address)],
        test1Authz(),
      )
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)

      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      expect(res.ticketInfo.owner).toBe(test2Addr)
      console.log(res, 'paymentInfo')

      res = await buildAndSendTrx('withdraw', [fcl.arg(paymentId, t.UInt64)], test2Authz())
      expect(res).not.toBeNull()
      expect(res.status).toBe(4)


      res = await buildAndExecScript('getPaymentInfo', [fcl.arg(paymentId, t.UInt64)])
      expect(res).not.toBeNull()
      expect(res.ticketInfo.owner).toBe(test2Addr)
      console.log(res, 'paymentInfo')

      
    })
  })
