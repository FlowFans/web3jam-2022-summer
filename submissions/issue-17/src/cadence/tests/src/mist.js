import { builtInMethods, executeScript, getAccountAddress, mintFlow, sendTransaction, shallPass, shallResolve } from "flow-js-testing"
import { getMistAdmin } from "./common"
import { NFT_mintExampleNFT, NFT_setupExampleNFTCollection } from "./examplenft"

export const mintExampleNFTs = async (recipient) => {
  await NFT_setupExampleNFTCollection(recipient)

  const admin = await getMistAdmin()
  await NFT_mintExampleNFT(admin, recipient)
  await NFT_mintExampleNFT(admin, recipient)
  await NFT_mintExampleNFT(admin, recipient)
}

export const createRaffle = async (signer, params) => {
  const signers = [signer]
  const txName = "mist/create_raffle"
  return await sendTransaction({ name: txName, signers: signers, args: params})
}

export const createExampleNFTRaffle = async (signer, overrides = {}) => {
  const nftInfo = await getExampleNFTInfo()

  const defaultWhitelist = await getDefaultWhitelist()

  const {initFlowAmount, 
    image, url, startAt, endAt,
    registrationEndAt, numberOfWinners,
    rewardTokenIDs,
    withWhitelist, whitelist,
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
    registrationEndAt: registrationEndAt || (new Date()).getTime() / 1000 + 2, 
    numberOfWinners: numberOfWinners || 2,
    nftName: nftInfo.nftName,
    nftTypeIdentifer: nftInfo.nftTypeIdentifer,
    nftContractName: nftInfo.nftContractName,
    nftContractAddress: nftInfo.nftContractAddress, 
    nftCollectionTypeIdentifier: nftInfo.nftCollectionTypeIdentifier, 
    nftCollectionTypeRestrictions: nftInfo.nftCollectionTypeRestrictions, 
    nftCollectionLogoURL: nftInfo.nftCollectionLogoURL, 
    nftCollectionPublicPath: nftInfo.nftCollectionPublicPath,
    nftCollectionStoragePath: nftInfo.nftCollectionStoragePath,
    rewardTokenIDs: rewardTokenIDs || [],
    withWhitelist: withWhitelist || false, whitelist: whitelist || defaultWhitelist,
    withFloats: withFloats || false, threshold: threshold || 2, eventIDs: eventIDs || defaultEventIDs, eventHosts: eventHosts || defaultEventHosts,
    withFloatGroup: withFloatGroup || false, floatGroupName: floatGroupName || defaultFloatGroupName, floatGroupHost: floatGroupHost || defaultFloatGroupHost
  }

  const flowAmount = initFlowAmount ?? 100.0

  await mintFlow(signer, flowAmount)

  const [tx, error] = await createRaffle(signer, Object.values(args))
  if (returnErr === true) {
    return error
  }
  expect(error).toBeNull()
  return tx
}

// ===== UTILS =====

export const getExampleNFTInfo = async () => {
  const mistAdmin = await getMistAdmin()
  const trimmedMistAdmin = mistAdmin.replace("0x", "")
  return {
    nftName: "Example",
    nftTypeIdentifer: `A.${trimmedMistAdmin}.ExampleNFT.NFT`,
    nftContractName: "ExampleNFT",
    nftContractAddress: await getMistAdmin(),
    nftCollectionTypeIdentifier: `A.${trimmedMistAdmin}.ExampleNFT.Collection`,
    nftCollectionTypeRestrictions: [
      `A.${trimmedMistAdmin}.ExampleNFT.ExampleNFTCollectionPublic`,
      `A.${trimmedMistAdmin}.NonFungibleToken.CollectionPublic`,
      `A.${trimmedMistAdmin}.MetadataViews.ResolverCollection`
    ],
    nftCollectionLogoURL: "",
    nftCollectionStoragePath: "exampleNFTCollection", 
    nftCollectionPublicPath: "exampleNFTCollection"
  }
}

export const getDefaultWhitelist = async () => {
  const Bob = await getAccountAddress("Bob")
  const Carl = await getAccountAddress("Carl")
  const Dave = await getAccountAddress("Dave")
  const Emily = await getAccountAddress("Emily")

  return {
    [Bob]: true,
    [Carl]: true,
    [Dave]: true,
    [Emily]: true
  }
}

// ===== TRANSACTIONS =====

export const toggleRafflePause = async (signer) => {
  const signers = [signer]
  const name = "mist/toggle_mist_pause"
  const args = []
  return await sendTransaction({ name: name, signers: signers, args: args })
}

export const registerRaffle = async (raffleID, host, registrator) => {
  const signers = [registrator]
  const name = "mist/register_raffle"
  const args = [raffleID, host]
  return await sendTransaction({ name: name, signers: signers, args: args, transformers: [builtInMethods] })
}

export const claimRaffle = async (raffleID, host, claimer) => {
  const signers = [claimer]
  const name = "mist/claim_raffle"
  const args = [raffleID, host]
  return await sendTransaction({ name: name, signers: signers, args: args, transformers: [builtInMethods] })
}

export const draw = async (raffleID, host) => {
  const signers = [host]
  const name = "mist/draw"
  const args = [raffleID]
  return await sendTransaction({ name: name, signers: signers, args: args, transformers: [builtInMethods] })
}

export const batchDraw = async (raffleID, host) => {
  const signers = [host]
  const name = "mist/batch_draw"
  const args = [raffleID]
  return await sendTransaction({ name: name, signers: signers, args: args, transformers: [builtInMethods] })
}

export const depositToRaffle = async (raffleID, host, tokenIDs) => {
  const signers = [host]
  const name = "mist/deposit_to_raffle"
  const args = [raffleID, tokenIDs]
  return await sendTransaction({ name: name, signers: signers, args: args})
}

export const endRaffle = async (raffleID, host) => {
  const signers = [host]
  const name = "mist/end_raffle"
  const args = [raffleID]
  return await sendTransaction({ name: name, signers: signers, args: args})
}

export const deleteRaffle = async (raffleID, signer) => {
  const signers = [signer]
  const name = "mist/delete_raffle"
  const args = [raffleID]
  return await sendTransaction({ name: name, signers: signers, args: args})
}

export const togglePause = async (raffleID, host) => {
  const signers = [host]
  const name = "mist/toggle_pause"
  const args = [raffleID]
  return await sendTransaction({ name: name, signers: signers, args: args})
}

// ===== SCRIPTS =====

export const getAllRaffles = async (account) => {
  const name = "mist/get_all_raffles"
  const args = [account]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const getClaimStatus = async (raffleID, host, claimer) => {
  const name = "mist/get_claim_status"
  const args = [raffleID, host, claimer]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const getRegistrationRecord = async (raffleID, host, claimer) => {
  const name = "mist/get_registration_record"
  const args = [raffleID, host, claimer]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const getRegistrationRecords = async (raffleID, host) => {
  const name = "mist/get_registration_records"
  const args = [raffleID, host]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const getWinner = async (raffleID, host, winner) => {
  const name = "mist/get_winner"
  const args = [raffleID, host, winner]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}

export const getRaffle = async (raffleID, host, mustResolve = true) => {
  const name = "mist/get_raffle"
  const args = [raffleID, host]
  const [result, error] = await executeScript({ name: name, args: args })
  if (mustResolve === true) {
    expect(error).toBeNull()
    return result
  }
  return [result, error]
}

// export const getRaffleBalance = async (raffleID, host) => {
//   const name = "mist/get_raffle_balance"
//   const args = [raffleID, host]
//   const [result, error] = await executeScript({ name: name, args: args })
//   expect(error).toBeNull()
//   return result
// }

