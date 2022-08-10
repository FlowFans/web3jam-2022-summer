import { 
  sendTransaction,
  shallPass,
  executeScript
} from "flow-js-testing"

// ===== TRANSACTIONS =====

export const FLOAT_setupAccount = async (signer) => {
  const signers = [signer]
  const txName = "float/setup_account"
  const args = []
  await shallPass(sendTransaction({ name: txName, signers: signers, args: args }))
}

export const FLOAT_createEvent = async (signer, params = {}) => {
  const {eventName, groups} = params
  const signers = [signer]
  const txName = "float/create_event"

  const forHost = signer
  const claimable = true
  const name = eventName || "TEST"
  const description = "TEST"
  const image = ""
  const url = ""
  const transferrable = false
  const timelock = false
  const dateStart = 0.0
  const timePeriod = 0.0
  const secret = false
  const secretPK = ""
  const limited = false
  const capacity = 0
  const initialGroups = groups || []
  const flowTokenPurchase = false
  const flowTokenCost = 0.0
  const minimumBalanceToggle = false
  const minimumBalance = 0.0

  const args = [
    forHost, claimable, name, description, image, url, transferrable,
    timelock, dateStart, timePeriod, secret, secretPK, limited, capacity,
    initialGroups, flowTokenPurchase, flowTokenCost, minimumBalanceToggle, minimumBalance
  ]
  await shallPass(sendTransaction({ name: txName, signers: signers, args: args }))
}

export const FLOAT_createGroup = async (signer, params) => {
  const signers = [signer]
  const txName = "float/create_group"

  const {groupName} = params
  const _groupName = groupName || "TEST GROUP"
  const image = ""
  const description = "TEST GROUP"

  const args = [
    _groupName, image, description
  ]

  await shallPass(sendTransaction({ name: txName, signers: signers, args: args }))
} 

export const FLOAT_createEventsWithGroup = async (signer) => {
  const groupName = "GTEST"
  await FLOAT_createGroup(signer, {groupName: groupName})
  await FLOAT_createEvent(signer, { eventName: "TEST 1", groups: [groupName]})
  await FLOAT_createEvent(signer, { eventName: "TEST 2", groups: [groupName]})
  await FLOAT_createEvent(signer, { eventName: "TEST 3", groups: [groupName]})
}

export const FLOAT_claim = async (signer, eventID, eventHost) => {
  const signers = [signer]
  const txName = "float/claim"

  const args = [
    eventID, eventHost, null 
  ]

  await shallPass(sendTransaction({ name: txName, signers: signers, args: args })) 
}

// ===== SCRIPTS =====

export const FLOAT_getEvent = async (account, eventID) => {
  const name = "float/get_event"
  const args = [account, eventID]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const FLOAT_getEventIDs = async (account) => {
  const name = "float/get_event_ids"
  const args = [account]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const FLOAT_getEventsInGroup = async (account, groupName) => {
  const name = "float/get_events_in_group"
  const args = [account, groupName]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const FLOAT_getFLOATIDs = async (account) => {
  const name = "float/get_float_ids"
  const args = [account]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

