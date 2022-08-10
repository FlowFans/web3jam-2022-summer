import { 
  getAccountAddress,
  mintFlow,
  sendTransaction,
  executeScript
} from "flow-js-testing"
import { 
  checkFUSDBalance,
  getCloudAdmin,
  mintFUSD,
  setupFUSDVault 
} from "./common"
import { 
  FLOAT_getEventIDs,
  FLOAT_createEvent
} from "./float"

export const createDrop = async (signer, params) => {
  const signers = [signer]
  const txName = "cloud/create_drop"
  return await sendTransaction({ name: txName, signers: signers, args: params})
}

export const createFUSDDrop = async (signer, overrides = {}) => {
  const FUSDInfo = await getFUSDInfo()

  const defaultExclusiveWhitelist = await getDefaultWhitelistWithAmount()
  const defaultWhitelist = await getDefaultWhitelist()

  const {initFlowAmount, initFUSDAmount, 
    image, url, startAt, endAt, 
    withExclusiveWhitelist, exclusiveWhitelist, whitelistTokenAmount,
    withWhitelist, whitelist,
    withIdenticalDistributor, capacity, amountPerEntry,
    withRandomDistributor, totalRandomAmount,
    withFloats, threshold, eventIDs, eventHosts,
    withFloatGroup, floatGroupName, floatGroupHost,
    returnErr} = overrides

  const needFloats = withFloats || withFloatGroup
  const creator = await getAccountAddress("FLOATCreator")
  const defaultEventIDs = needFloats ? await FLOAT_getEventIDs(creator) : []
  const defaultEventHosts = needFloats ? [creator, creator, creator] : []

  const defaultFloatGroupName = "GTEST"
  const defaultFloatGroupHost = creator
  
  const args = {
    name: "TEST", 
    description: "Test DROP", 
    image: image || null, url: url || null,
    startAt: startAt || null, endAt: endAt || null,
    tokenIssuer: FUSDInfo.tokenIssuer, 
    tokenContractName: FUSDInfo.tokenContractName, 
    tokenSymbol: FUSDInfo.tokenSymbol,
    tokenProviderPath: FUSDInfo.tokenProviderPath, 
    tokenBalancePath: FUSDInfo.tokenBalancePath, 
    tokenReceiverPath: FUSDInfo.tokenReceiverPath,

    withExclusiveWhitelist: withExclusiveWhitelist || false,
    exclusiveWhitelist: exclusiveWhitelist || defaultExclusiveWhitelist, 
    whitelistTokenAmount: whitelistTokenAmount || 150.0,
    withWhitelist: withWhitelist || false, whitelist: whitelist || defaultWhitelist,
    withIdenticalDistributor: withIdenticalDistributor || false, capacity: capacity || 2, amountPerEntry: amountPerEntry || 20.0,
    withRandomDistributor: withRandomDistributor || false, totalRandomAmount: totalRandomAmount || 150.0,
    withFloats: withFloats || false, threshold: threshold || 2, eventIDs: eventIDs || defaultEventIDs, eventHosts: eventHosts || defaultEventHosts,
    withFloatGroup: withFloatGroup || false, floatGroupName: floatGroupName || defaultFloatGroupName, floatGroupHost: floatGroupHost || defaultFloatGroupHost
  }

  const flowAmount = initFlowAmount ?? 100.0
  const fusdAmount = initFUSDAmount ?? 1000.0

  await mintFlow(signer, flowAmount)
  await setupFUSDVault(signer)

  await mintFUSD(await getCloudAdmin(), fusdAmount, signer)
  await checkFUSDBalance(signer, fusdAmount)

  const [tx, error] = await createDrop(signer, Object.values(args))
  if (returnErr === true) {
    return error
  }
  expect(error).toBeNull()
}

// ===== UTILS =====

export const getFUSDInfo = async () => {
  return {
    tokenIssuer: await getCloudAdmin(),
    tokenContractName: "FUSD",
    tokenSymbol: "FUSD",
    tokenProviderPath: "fusdVault", tokenBalancePath: "fusdBalance", tokenReceiverPath: "fusdReceiver"
  }
}

export const getDefaultWhitelistWithAmount = async () => {
  const Bob = await getAccountAddress("Bob")
  const Carl = await getAccountAddress("Carl")

  return {
    [Bob]: "100.0",
    [Carl]: "50.0"
  }
}

export const getDefaultWhitelist = async () => {
  const Bob = await getAccountAddress("Bob")
  const Carl = await getAccountAddress("Carl")

  return {
    [Bob]: true,
    [Carl]: true
  }
}

export const createDefaultEvents = async (creator) => {
  await FLOAT_createEvent(creator, {eventName: "EVENT 1"})
  await FLOAT_createEvent(creator, {eventName: "EVENT 2"})
  await FLOAT_createEvent(creator, {eventName: "EVENT 3"})
}

// ===== TRANSACTIONS =====

export const toggleCloudPause = async (signer) => {
  const signers = [signer]
  const name = "cloud/toggle_cloud_pause"
  const args = []
  return await sendTransaction({ name: name, signers: signers, args: args })
}

export const claimDrop = async (dropID, host, claimer) => {
  const signers = [claimer]
  const name = "cloud/claim_drop"
  const args = [dropID, host]
  return await sendTransaction({ name: name, signers: signers, args: args })
}

export const depositToDrop = async (dropID, host, amount) => {
  const signers = [host]
  const name = "cloud/deposit_to_drop"
  const args = [dropID, amount]
  return await sendTransaction({ name: name, signers: signers, args: args})
}

export const endDrop = async (dropID, host, tokenIssuer, tokenReceiverPath) => {
  const signers = [host]
  const name = "cloud/end_drop"
  const args = [dropID, tokenIssuer, tokenReceiverPath]
  return await sendTransaction({ name: name, signers: signers, args: args})
}

export const deleteDrop = async (dropID, signer, tokenIssuer, tokenReceiverPath) => {
  const signers = [signer]
  const name = "cloud/delete_drop"
  const args = [dropID, tokenIssuer, tokenReceiverPath]
  return await sendTransaction({ name: name, signers: signers, args: args})
}

export const togglePause = async (dropID, host) => {
  const signers = [host]
  const name = "cloud/toggle_pause"
  const args = [dropID]
  return await sendTransaction({ name: name, signers: signers, args: args})
}

// ===== SCRIPTS =====

export const getAllDrops = async (account) => {
  const name = "cloud/get_all_drops"
  const args = [account]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const getClaimStatus = async (dropID, host, claimer) => {
  const name = "cloud/get_claim_status"
  const args = [dropID, host, claimer]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const getClaimedRecord = async (dropID, host, claimer) => {
  const name = "cloud/get_claimed_record"
  const args = [dropID, host, claimer]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const getClaimedRecords = async (dropID, host) => {
  const name = "cloud/get_claimed_records"
  const args = [dropID, host]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const getDrop = async (dropID, host, mustResolve = true) => {
  const name = "cloud/get_drop"
  const args = [dropID, host]
  const [result, error] = await executeScript({ name: name, args: args })
  if (mustResolve === true) {
    expect(error).toBeNull()
    return result
  }
  return [result, error]
}

export const getDropBalance = async (dropID, host) => {
  const name = "cloud/get_drop_balance"
  const args = [dropID, host]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

