import path from "path"
import {
  emulator,
  init,
  getAccountAddress,
} from "flow-js-testing";
import {
  deployCoreContracts,
  deployFLOATContracts,
  deployExampleNFTContracts,
  deployByName,
  getMistAdmin
} from "./src/common";
import { NFT_getIDs, NFT_mintExampleNFT } from "./src/examplenft";
import { 
  batchDraw,
  claimRaffle,
  createExampleNFTRaffle, 
  deleteRaffle, 
  depositToRaffle, 
  draw, 
  endRaffle, 
  getAllRaffles, 
  getClaimStatus, 
  getRaffle, 
  getRegistrationRecord, 
  getRegistrationRecords, 
  getWinner, 
  mintExampleNFTs, 
  registerRaffle,
  togglePause,
} from "./src/mist";

jest.setTimeout(1000000)

const deployContracts = async () => {
  const deployer = await getMistAdmin()
  await deployCoreContracts(deployer)
  await deployFLOATContracts(deployer)
  await deployExampleNFTContracts(deployer)
  await deployByName(deployer, "Distributors")
  await deployByName(deployer, "EligibilityVerifiers")
  await deployByName(deployer, "Mist")
}

describe("Deployment", () => {
  beforeEach(async () => {
    const basePath = path.resolve(__dirname, "..")
    const port = 8020
    await init(basePath, {port})
    await emulator.start(port)
    return await new Promise(r => setTimeout(r, 2000));
  })

  afterEach(async () => {
    await emulator.stop()
    return await new Promise(r => setTimeout(r, 2000));
  })

  it("Deployment - Should deploy all contracts successfully", async () => {
    const deployer = await getAccountAddress("MistDeployer")
    await deployContracts(deployer)
  })
})

describe("Mist", () => {
  beforeEach(async () => {
    const basePath = path.resolve(__dirname, "..")
    const port = 8020
    await init(basePath, {port})
    await emulator.start(port)
    await new Promise(r => setTimeout(r, 2000));
    return await deployContracts()
  })

  afterEach(async () => {
    await emulator.stop();
    return await new Promise(r => setTimeout(r, 2000));
  })

  // If the size of whitelist is huge(exceed 30000), it is recommanded to use MerkleAirDrop rather than AirDrop
  // [Error: transaction byte size (3991439) exceeds the maximum byte size allowed for a transaction (3000000)]
  it("Mist - Should be ok if we create raffle with valid params", async () => {
    const Alice = await getAccountAddress("Alice")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs})
  })

  it("Mist - Should not be ok if we create raffle with too less nft", async () => {
    const Alice = await getAccountAddress("Alice")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    const err = await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, numberOfWinners: 4, returnErr: true})
    expect(err).not.toBeNull()
  })

  it("Mist - Should not be ok for registrator to claim reward when registering", async () => {
    const Alice = await getAccountAddress("Alice")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, registrationEndAt:  (new Date()).getTime() / 1000 + 100})

    const Bob = await getAccountAddress("Bob")

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, error] = await registerRaffle(raffleID, Alice, Bob)
    expect(error).toBeNull()

    const [, error2] = await claimRaffle(raffleID, Alice, Bob)
    expect(error2.includes("registering")).toBeTruthy()
  }) 

  it("Mist - Should not be ok for uneligible users to register", async () => {
    const Alice = await getAccountAddress("Alice")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, registrationEndAt:  (new Date()).getTime() / 1000 + 100})

    const Frank = await getAccountAddress("Frank")

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, error] = await registerRaffle(raffleID, Alice, Frank)
    expect(error.includes("not eligible")).toBeTruthy()
  }) 

  it("Mist - Should not be ok for users to register if regestry has ended", async () => {
    const Alice = await getAccountAddress("Alice")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    const registrationEndAt = new Date().getTime() / 1000 + 1
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, registrationEndAt: registrationEndAt})

    const Bob = await getAccountAddress("Bob")

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    await new Promise(r => setTimeout(r, 2000))
    const [, error] = await registerRaffle(raffleID, Alice, Bob)
    // no registrant, so the raffle is drawn
    expect(error.includes("drawn")).toBeTruthy()
  })

  it("Mist - Should not be ok for host to draw reward before registery ended", async () => {
    const Alice = await getAccountAddress("Alice")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    const registrationEndAt = new Date().getTime() / 1000 + 100
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, registrationEndAt: registrationEndAt})

    const Bob = await getAccountAddress("Bob")

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, error] = await registerRaffle(raffleID, Alice, Bob)
    expect(error).toBeNull()

    const [, errorDraw] = await draw(raffleID, Alice)
    expect(errorDraw.includes("registering")).toBeTruthy()
  })

  it("Mist - Should be ok for host to draw reward after registery ended", async () => {
    const Alice = await getAccountAddress("Alice")
    const Bob = await getAccountAddress("Bob")
    const Carl = await getAccountAddress("Carl")

    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    const registrationEndAt = new Date().getTime() / 1000 + 2 
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, registrationEndAt: registrationEndAt})

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, errorBob] = await registerRaffle(raffleID, Alice, Bob)
    expect(errorBob).toBeNull()
    const [, errorCarl] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl).toBeNull()

    // setTimeOut can't change block timestamp
    // setTimestampOffset is not working for contracts
    // so we add this trick to make the block time pass
    const [, errorCarl2] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl2).not.toBeNull()
    const [, errorCarl3] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl3).not.toBeNull()
    const [, errorCarl4] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl4).not.toBeNull()
    const [, errorCarl5] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl5).not.toBeNull()

    const preRaffle = await getRaffle(raffleID, Alice) 
    expect(Object.keys(preRaffle.winners).length).toBe(0)
    expect(preRaffle.nftToBeDrawn.length).toBe(3)
    expect(Object.keys(preRaffle.rewardDisplays).length).toBe(3)

    const recordBob = await getRegistrationRecord(raffleID, Alice, Bob)
    expect(recordBob.address).toBe(Bob)

    const recordCarl = await getRegistrationRecord(raffleID, Alice, Carl)
    expect(recordCarl.address).toBe(Carl)

    const [, errorDraw] = await draw(raffleID, Alice)
    expect(errorDraw).toBeNull()
    const raffle1 = await getRaffle(raffleID, Alice) 
    expect(Object.keys(raffle1.winners).length).toBe(1)
    expect(raffle1.nftToBeDrawn.length).toBe(2)

    const [, errorDraw2] = await draw(raffleID, Alice)
    expect(errorDraw2).toBeNull()
    const raffle2 = await getRaffle(raffleID, Alice) 
    expect(Object.keys(raffle2.winners).length).toBe(2)
    expect(raffle2.nftToBeDrawn.length).toBe(1)
    expect(Object.keys(raffle2.rewardDisplays).length).toBe(3)

    const [, errorDraw3] = await draw(raffleID, Alice)
    expect(errorDraw3.includes("drawn")).toBeTruthy()
  })

  it("Mist - Should not be ok for host to draw reward if drawn", async () => {
    const Alice = await getAccountAddress("Alice")
    const Bob = await getAccountAddress("Bob")
    const Carl = await getAccountAddress("Carl")

    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    const registrationEndAt = new Date().getTime() / 1000 + 2 
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, registrationEndAt: registrationEndAt, numberOfWinners: 1})

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, errorBob] = await registerRaffle(raffleID, Alice, Bob)
    expect(errorBob).toBeNull()
    const [, errorCarl] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl).toBeNull()

    // setTimeOut can't change block timestamp
    // setTimestampOffset is not working for contracts
    // so we add this trick to make the block time pass
    const [, errorCarl2] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl2).not.toBeNull()
    const [, errorCarl3] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl3).not.toBeNull()
    const [, errorCarl4] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl4).not.toBeNull()
    const [, errorCarl5] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl5).not.toBeNull()

    const [, errorDraw] = await draw(raffleID, Alice)
    expect(errorDraw).toBeNull()
    const raffle1 = await getRaffle(raffleID, Alice) 
    expect(Object.keys(raffle1.winners).length).toBe(1)

    const [, errorDraw2] = await draw(raffleID, Alice)
    expect(errorDraw2.includes("drawn")).toBeTruthy()
  })

  it("Mist - Should be ok for host to draw reward in batch after registery ended", async () => {
    const Alice = await getAccountAddress("Alice")
    const Bob = await getAccountAddress("Bob")
    const Carl = await getAccountAddress("Carl")
    const Dave = await getAccountAddress("Dave")

    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    const registrationEndAt = new Date().getTime() / 1000 + 2 
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, registrationEndAt: registrationEndAt, numberOfWinners: 3 })

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, errorBob] = await registerRaffle(raffleID, Alice, Bob)
    expect(errorBob).toBeNull()
    const [, errorCarl] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl).toBeNull()
    const [, errorDave] = await registerRaffle(raffleID, Alice, Dave)
    expect(errorDave).toBeNull()

    // setTimeOut can't change block timestamp
    // setTimestampOffset is not working for contracts
    // so we add this trick to make the block time pass
    const [, errorCarl2] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl2).not.toBeNull()
    const [, errorCarl3] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl3).not.toBeNull()
    const [, errorCarl4] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl4).not.toBeNull()
    const [, errorCarl5] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl5).not.toBeNull()

    const [, errorDraw] = await draw(raffleID, Alice)
    expect(errorDraw).toBeNull()
    const raffle1 = await getRaffle(raffleID, Alice) 
    expect(Object.keys(raffle1.winners).length).toBe(1)

    const [, errorBatchDraw] = await batchDraw(raffleID, Alice)
    expect(errorBatchDraw).toBeNull()
    const raffle2 = await getRaffle(raffleID, Alice) 
    expect(Object.keys(raffle2.winners).length).toBe(3)
  })

  it("Mist - Only winner can claim the reward", async () => {
    const Alice = await getAccountAddress("Alice")
    const Bob = await getAccountAddress("Bob")
    const Carl = await getAccountAddress("Carl")

    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    const registrationEndAt = new Date().getTime() / 1000 + 2 
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, registrationEndAt: registrationEndAt, numberOfWinners: 1 })

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, errorBob] = await registerRaffle(raffleID, Alice, Bob)
    expect(errorBob).toBeNull()
    const [, errorCarl] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl).toBeNull()

    // setTimeOut can't change block timestamp
    // setTimestampOffset is not working for contracts
    // so we add this trick to make the block time pass
    const [, errorCarl2] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl2).not.toBeNull()
    const [, errorCarl3] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl3).not.toBeNull()
    const [, errorCarl4] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl4).not.toBeNull()
    const [, errorCarl5] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl5).not.toBeNull()

    const [, errorDraw] = await batchDraw(raffleID, Alice)
    expect(errorDraw).toBeNull()
    const raffle1 = await getRaffle(raffleID, Alice) 
    expect(Object.keys(raffle1.winners).length).toBe(1)

    const winner = Object.keys(raffle1.winners)[0]
    const loser = winner == Bob ? Carl : Bob

    // unregistered account
    const [, errorAliceClaim] = await claimRaffle(raffleID, Alice, Alice)
    expect(errorAliceClaim.includes("not eligible")).toBeTruthy()

    // loser 
    const [, errorLoserClaim] = await claimRaffle(raffleID, Alice, loser)
    expect(errorLoserClaim.includes("not eligible")).toBeTruthy()

    const [, errorWinner] = await claimRaffle(raffleID, Alice, winner)
    expect(errorWinner).toBeNull()

    const winnerRecord = await getWinner(raffleID, Alice, winner)
    expect(winnerRecord.isClaimed).toBeTruthy()

    const winnerTokenIDs = (await NFT_getIDs(winner)).map((id) => parseInt(id)).sort()
    expect(winnerTokenIDs).toEqual(winnerRecord.rewardTokenIDs)
  })

  it("Mist - Should be ok for winner to claim reward when the raffle is drawing", async () => {
    const Alice = await getAccountAddress("Alice")
    const Bob = await getAccountAddress("Bob")
    const Carl = await getAccountAddress("Carl")
    const Dave = await getAccountAddress("Dave")

    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    const registrationEndAt = new Date().getTime() / 1000 + 2 
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, registrationEndAt: registrationEndAt, numberOfWinners: 3 })

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, errorBob] = await registerRaffle(raffleID, Alice, Bob)
    expect(errorBob).toBeNull()
    const [, errorCarl] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl).toBeNull()
    const [, errorDave] = await registerRaffle(raffleID, Alice, Dave)
    expect(errorDave).toBeNull()

    // setTimeOut can't change block timestamp
    // setTimestampOffset is not working for contracts
    // so we add this trick to make the block time pass
    const [, errorCarl2] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl2).not.toBeNull()
    const [, errorCarl3] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl3).not.toBeNull()
    const [, errorCarl4] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl4).not.toBeNull()
    const [, errorCarl5] = await registerRaffle(raffleID, Alice, Carl)
    expect(errorCarl5).not.toBeNull()

    const [, errorDraw] = await draw(raffleID, Alice)
    expect(errorDraw).toBeNull()
    const raffle1 = await getRaffle(raffleID, Alice) 
    expect(Object.keys(raffle1.winners).length).toBe(1)
    const winner = Object.keys(raffle1.winners)[0]

    const [, errorWinner] = await claimRaffle(raffleID, Alice, winner)
    expect(errorWinner).toBeNull()

    const winnerRecord = await getWinner(raffleID, Alice, winner)
    expect(winnerRecord.isClaimed).toBeTruthy() 
  })

  it("Mist - Should be ok for host to deposit more NFTs", async () => {
    const Alice = await getAccountAddress("Alice")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs }) 

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const raffle1 = await getRaffle(raffleID, Alice) 
    expect(raffle1.nftToBeDrawn.length).toBe(3)
    expect(Object.keys(raffle1.rewardDisplays).length).toBe(3) 

    const admin = await getMistAdmin()
    await NFT_mintExampleNFT(admin, Alice)
    await NFT_mintExampleNFT(admin, Alice)
    const newTokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()

    const [, error] = await depositToRaffle(raffleID, Alice, newTokenIDs)
    expect(error).toBeNull()
    const raffle2 = await getRaffle(raffleID, Alice) 
    expect(raffle2.nftToBeDrawn.length).toBe(5)
    expect(Object.keys(raffle2.rewardDisplays).length).toBe(5)

    tokenIDs.push(...newTokenIDs)
    expect(raffle2.nftToBeDrawn.sort()).toEqual(tokenIDs.sort())
    expect(Object.keys(raffle2.rewardDisplays).map((id) => parseInt(id)).sort()).toEqual(tokenIDs.sort())
  })

  it("Mist - Should be ok for host to pause raffle", async () => {
    const Alice = await getAccountAddress("Alice")
    const Bob = await getAccountAddress("Bob")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs }) 

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, error] = await togglePause(raffleID, Alice)
    expect(error).toBeNull()

    const raffle = await getRaffle(raffleID, Alice) 
    expect(raffle.isPaused).toBeTruthy()

    const [, errorBob] = await registerRaffle(raffleID, Alice, Bob)
    expect(errorBob.includes("paused")).toBeTruthy()
  })

  it("Mist - Should be ok for host to end a raffle", async () => {
    const Alice = await getAccountAddress("Alice")
    const Bob = await getAccountAddress("Bob")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs }) 

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, error] = await endRaffle(raffleID, Alice)
    expect(error).toBeNull()

    const raffle = await getRaffle(raffleID, Alice) 
    expect(raffle.isEnded).toBeTruthy()

    const [, errorBob] = await registerRaffle(raffleID, Alice, Bob)
    expect(errorBob.includes("ended")).toBeTruthy()

    const newTokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    expect(newTokenIDs).toEqual(tokenIDs)
  })

  it("Mist - Should be ok for host to delete a raffle", async () => {
    const Alice = await getAccountAddress("Alice")
    const Bob = await getAccountAddress("Bob")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs }) 

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const [, error] = await deleteRaffle(raffleID, Alice)
    expect(error).toBeNull()

    const [, error2]= await getRaffle(raffleID, Alice, false) 
    expect(error2.message.includes("Could not borrow")).toBeTruthy()

    const newTokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    expect(newTokenIDs).toEqual(tokenIDs)
  }) 

  it("Mist - Should be ok for eligible users to register", async () => {
    const Alice = await getAccountAddress("Alice")
    await mintExampleNFTs(Alice)
    const tokenIDs = (await NFT_getIDs(Alice)).map((id) => parseInt(id)).sort()
    await createExampleNFTRaffle(Alice, { withWhitelist: true, rewardTokenIDs: tokenIDs, registrationEndAt:  (new Date()).getTime() / 1000 + 100})

    const Bob = await getAccountAddress("Bob")

    const raffles = await getAllRaffles(Alice)
    const raffleID = parseInt(Object.keys(raffles)[0])

    const raffle = await getRaffle(raffleID, Alice)
    expect(Object.keys(raffle.rewardDisplays).map((id) => parseInt(id)).sort()).toEqual(tokenIDs)
    expect(raffle.nftToBeDrawn.sort()).toEqual(tokenIDs)

    const preClaimed = await getClaimStatus(raffleID, Alice, Bob)
    expect(preClaimed.availability.status.rawValue).toBe(2)
    expect(preClaimed.eligibilityForRegistration.status.rawValue).toBe(0)
    expect(preClaimed.eligibilityForClaim.status.rawValue).toBe(3)
    const eligibleNFTs = preClaimed.eligibilityForRegistration.eligibleNFTs
    expect(eligibleNFTs.length).toBe(0)

    const [, error] = await registerRaffle(raffleID, Alice, Bob)
    expect(error).toBeNull()

    const [, error2] = await registerRaffle(raffleID, Alice, Bob)
    expect(error2.includes("has registered")).toBeTruthy()

    const postClaimed = await getClaimStatus(raffleID, Alice, Bob)
    expect(postClaimed.availability.status.rawValue).toBe(2)
    expect(postClaimed.eligibilityForRegistration.status.rawValue).toBe(4)
    expect(preClaimed.eligibilityForClaim.status.rawValue).toBe(3)

    const record = await getRegistrationRecord(raffleID, Alice, Bob)
    expect(record.address).toBe(Bob)

    const records = await getRegistrationRecords(raffleID, Alice)
    expect(Object.keys(records).length).toBe(1)
  })
})
