import * as fcl from '@onflow/fcl'

import axios from 'axios'
import { ec as EC } from 'elliptic'
import { SHA3 } from 'sha3'
import { publicKey, privateKey } from '../constants.js'
const ec = new EC('p256')

import { encodeKey, ECDSA_P256, SHA3_256 } from '@onflow/util-encode-key'

export const FLOW_ENCODED_SERVICE_KEY = encodeKey(publicKey, ECDSA_P256, SHA3_256, 1000)

export const sign = (msg: string) => {
  const key = ec.keyFromPrivate(Buffer.from(privateKey!, 'hex'))
  const sig = key.sign(hashMsg(msg))
  const n = 32
  const r = sig.r.toArrayLike(Buffer, 'be', n)
  const s = sig.s.toArrayLike(Buffer, 'be', n)
  return Buffer.concat([r, s]).toString('hex')
}

export const hashMsg = (msg: string) => {
  const sha = new SHA3(256)
  sha.update(Buffer.from(msg, 'hex'))
  return sha.digest()
}

const ADDRESS = process.env.ACCOUNT_ADDRESS

export const authorizationFunctionProposer = async (account: any) => {
  let keyId = 0
  // authorization function need to return an account
  return {
    ...account, // bunch of defaults in here, we want to overload some of them though
    tempId: `${ADDRESS}-${keyId}`, // tempIds are more of an advanced topic, for 99% of the times where you know the address and keyId you will want it to be a unique string per that address and keyId
    addr: fcl.sansPrefix(ADDRESS), // the address of the signatory, currently it needs to be without a prefix right now
    keyId: Number(keyId), // this is the keyId for the accounts registered key that will be used to sign, make extra sure this is a number and not a string
    signingFunction: async (signable: any) => {
      // Singing functions are passed a signable and need to return a composite signature
      // signable.message is a hex string of what needs to be signed.
      return {
        addr: fcl.withPrefix(ADDRESS), // needs to be the same as the account.addr but this time with a prefix, eventually they will both be with a prefix
        keyId: Number(keyId), // needs to be the same as account.keyId, once again make sure its a number and not a string
        signature: sign(signable.message), // this needs to be a hex string of the signature, where signable.message is the hex value that needs to be signed
      }
    },
  }
}

export const getScript = async (name: string, type = 'transcation') => {
  try {
    const response = await fetch(
      `/api/scripts?name=${name}&type=${type}`,
      // `http://localhost:5000/api/getScript/${scriptName}`,
      {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
      },
    )
    const res = response.json()
    return res
  } catch (error) {
    return ''
  }
}

// sign transaction with verify the cadence code
const signWithVerify = async (args = {}) => {
  const response = await axios.post(
    `/api/auth/serverAuth`,
    // `http://localhost:5000/api/signWithVerify`,
    {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(args),
    },
  )

  //TODO: add necessary corrections
  const signed = response.data

  console.log({ signed })

  return signed
}

export const authorizationFunction = async (account: any) => {
  // authorization function need to return an account
  return {
    ...account, // bunch of defaults in here, we want to overload some of them though
    tempId: `${ADDRESS}-0`, // tempIds are more of an advanced topic, for 99% of the times where you know the address and keyId you will want it to be a unique string per that address and keyId
    addr: fcl.sansPrefix(ADDRESS), // the address of the signatory, currently it needs to be without a prefix right now
    keyId: Number(0), // this is the keyId for the accounts registered key that will be used to sign, make extra sure this is a number and not a string
    signingFunction: async (signable: any) => {
      // Singing functions are passed a signable and need to return a composite signature
      // signable.message is a hex string of what needs to be signed.
      return {
        addr: fcl.withPrefix(ADDRESS), // needs to be the same as the account.addr but this time with a prefix, eventually they will both be with a prefix
        keyId: Number(0), // needs to be the same as account.keyId, once again make sure its a number and not a string
        signature: sign(signable.message), // this needs to be a hex string of the signature, where signable.message is the hex value that needs to be signed
      }
    },
  }
}

export const serverAuthorization = (scriptName: string, user: any) => {
  return async (account: any) => {
    // this gets the address and keyIndex that the server will use when signing the message
    // const serviceAccountSigningKey = await getAccountSigningKey()
    // console.log(serviceAccountSigningKey)

    return {
      ...account,
      tempId: `${fcl.sansPrefix()}-0`,
      addr: fcl.sansPrefix(ADDRESS),
      keyId: 0,
      signingFunction: async (signable: any) => {
        // this signs the message server-side and returns the signature
        const signature = await signWithVerify({
          scriptName,
          signable,
          user,
        })

        return {
          addr: fcl.withPrefix(ADDRESS),
          keyId: 0,
          signature: signature.signature,
        }
      },
    }
  }
}

export const signUtil = {
  sign,
  authorizationFunction,
  authorizationFunctionProposer,
  serverAuthorization,
}
