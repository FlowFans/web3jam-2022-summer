import { json } from '@remix-run/node' // or "@remix-run/cloudflare"
import type { ActionFunction } from '@remix-run/node' // or "@remix-run/cloudflare"
import * as fcl from '@onflow/fcl'
import * as t from '@onflow/types'
import { fclinit } from '~/lib/flow/utils'
import { serverSend } from '~/lib/flow/transactions'
import crypto from 'crypto'

export const action: ActionFunction = async ({ request }) => {
  fclinit()
  // const formData = await request.formData()
  const body = await request.json()
  const { message = '', data = '' } = body

  if (message === '') {
    return json({ error: 'invalid params' }, 500)
  }

  // let jsonData = new Buffer(data, 'base64')
  let jsonData =
    '{"timestamp":1659150145,"identifier":"renasTestTokenVault","recieverPath":"renasTestTokenReceiver","amount":0.001,"reciever":"0x337bf11954a327d1"}'
  console.log(jsonData.toString())
  let verifier = crypto.createVerify('RSA-SHA256')
  verifier.update(jsonData)

  let pubKey = process.env.RSA_PUBLIC_KEY || ''
  let result = verifier.verify(pubKey, message, 'hex')

  // todo
  let trxData = JSON.parse(jsonData)
  const { amount, recieverPath, identifier } = trxData
  let trxId = await serverSend(
    'transfer_ft_with_path',
    [fcl.arg('0xbef84882fdd4e5fa', t.Address), fcl.arg(amount.toFixed(8), t.UFix64)],
    { vaultId: identifier, receiverId: recieverPath },
    true,
  )

  console.log(trxId)
  // console.log(result, '====')
  return json({ trxId }, 200)
}
