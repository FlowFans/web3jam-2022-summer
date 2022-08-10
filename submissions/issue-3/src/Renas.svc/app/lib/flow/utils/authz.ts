import { sign } from './crypto.js'
import fcl from '@onflow/fcl'

// alias Hex = String
// type signable = { message: Hex, voucher: voucher }
// type compositeSignature = { addr: String, keyId: Number, signature: Hex }
// signingFunction :: signable -> compositeSignature
// type account = { tempId: String, addr: String, keyId: Number, signingFunction: signingFunction }
// authz :: account -> account

// local
export const test1Addr = '0x01cf0e2f2f715450'
export const test2Addr = '0x179b6b1cb6755e31'

// testnet
// export const test1Addr = '0xd50084a1a43b1507'
// export const test2Addr = '0x31673d7cb5c115d8'

export async function authz(account: any) {
  return {
    // there is stuff in the account that is passed in
    // you need to make sure its part of what is returned
    ...account,
    // the tempId here is a very special and specific case.
    // what you are usually looking for in a tempId value is a unique string for the address and keyId as a pair
    // if you have no idea what this is doing, or what it does, or are getting an error in your own
    // implementation of an authorization function it is recommended that you use a string with the address and keyId in it.
    // something like... tempId: `${address}-${keyId}`
    tempId: 'SERVICE_ACCOUNT',
    addr: fcl.sansPrefix(process.env.ACCOUNT_ADDRESS), // eventually it wont matter if this address has a prefix or not, sadly :'( currently it does matter.
    keyId: Number(process.env.ACCOUNT_KEY_ID), // must be a number
    signingFunction: (signable: any) => ({
      addr: fcl.withPrefix(process.env.ACCOUNT_ADDRESS), // must match the address that requested the signature, but with a prefix
      keyId: Number(process.env.ACCOUNT_KEY_ID || 0), // must match the keyId in the account that requested the signature
      signature: sign(process.env.PRIVATE_KEY!, signable.message), // signable.message |> hexToBinArray |> hash |> sign |> binArrayToHex
      // if you arent in control of the transaction that is being signed we recommend constructing the
      // message from signable.voucher using the @onflow/encode module
    }),
  }
}

export function authFunc(opt: any = {}) {
  const { addr, keyId = 0, tempId = 'SERVICE_ACCOUNT', key } = opt

  return (account: any) => {
    return {
      ...account,
      tempId,
      addr: fcl.sansPrefix(addr),
      keyId: Number(keyId),
      signingFunction: (signable: any) => ({
        addr: fcl.withPrefix(addr), // must match the address that requested the signature, but with a prefix
        keyId: Number(keyId), // must match the keyId in the account that requested the signature
        signature: sign(key, signable.message), // signable.message |> hexToBinArray |> hash |> sign |> binArrayToHex
        // if you arent in control of the transaction that is being signed we recommend constructing the
        // message from signable.voucher using the @onflow/encode module
      }),
    }
  }
}
