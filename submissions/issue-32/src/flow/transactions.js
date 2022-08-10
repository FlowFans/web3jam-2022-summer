// 这里是update
import * as fcl from '@onflow/fcl'

import { config } from "@onflow/fcl";

import CHAIN_CONFIG from "./config.json"
if (CHAIN_CONFIG.env == "testnet") {
    config(CHAIN_CONFIG.testnet)
} else {
    config(CHAIN_CONFIG.emulator)
}


// 创建比赛
//return: GameUId
export async function createGame(name, issues, timestamp) {
  const txId = await fcl.mutate({
    cadence: `
        import Racenumber from 0x01
        import FlowToken from 0x01
        import FungibleToken from 0x01
        
        transaction(name:String, issues:UInt64, timestamp: UInt32) {

          prepare(acct: AuthAccount) {
            //创建Games collection
            let hasGames = acct.getCapability<&Racenumber.Games{Racenumber.GamesPublic}>(Racenumber.GamesPublicPath).check()
            if !hasGames {
              let Games <- Racenumber.createEmptyGames()   
              acct.save<@Racenumber.Games>(<- Games, to: Racenumber.GamesStoragePath)
              acct.link<&Racenumber.Games{Racenumber.GamesPublic}>(Racenumber.GamesPublicPath, target:Racenumber.GamesStoragePath)
              log("Games resource created")
            } 
            //创建钱包
            let hasVault = acct.getCapability<&FlowToken.Vault{FungibleToken.Provider, FungibleToken.Receiver, FungibleToken.Balance}>(FlowToken.FlowTokenVaultPublic).check()
            if !hasVault{
              acct.save(<-FlowToken.createEmptyVault(), to: FlowToken.FlowTokenVaultStorage)
              acct.link<&FlowToken.Vault{FungibleToken.Provider, FungibleToken.Receiver, FungibleToken.Balance}>(FlowToken.FlowTokenVaultPublic, target: FlowToken.FlowTokenVaultStorage)
              log("TokenVault created")
            }
            //创建event
            let gamesRef = acct.borrow<&Racenumber.Games>(from: Racenumber.GamesStoragePath)?? panic("Games resource not found")
            let gameId = gamesRef.createGame(name: name, issues: issues, timestamp: timestamp, hostAddr: acct.address)
            log("Game id:")
            log(gameId)
            log(" created")
          }
        }
        `,
    args: (arg, t) => [
      arg(name, t.String),
      arg(parseInt(issues), t.UInt64),
      arg(parseInt(timestamp), t.UInt32)
    ],
    proposer: fcl.authz,
    payer: fcl.authz,
    authorizations: [fcl.authz],
    limit: 999,
  })

  console.log('Here is the transaction: ' + txId)
  fcl.tx(txId).subscribe((res) => {
    console.log(res)
    if (res.status === 0 || res.status === 1) {
      console.log('Pending...');
    } else if (res.status === 2) {
      console.log('Finalized...')
    } else if (res.status === 3) {
      console.log('Executed...');
    } else if (res.status === 4) {
      console.log('Sealed!');
      setTimeout(() => console.log('Run Transaction'), 2000); // We added this line
    }
  })

  const transaction = await fcl.tx(txId).onceSealed()
  console.log("createGame transaction>>>", transaction)
  if(transaction.errorMessage) {
    throw transaction.errorMessage
  } else {
    let gameUId = transaction.events[0].data.uid
    console.log("gameUid:",gameUId)
    return gameUId
  }
}

export async function createGameNFTTemplate(gameUId, imageHash, templateType, gameType, slogan) {
  const txId = await fcl.mutate({
    cadence: `
        import Racenumber from 0x01
        transaction(gameUId:UInt64,imageHash:String,templateType:String, gameType:String,slogan:String) {
          prepare(acct: AuthAccount) {
            let gamesref = acct.borrow<&Racenumber.Games>(from: Racenumber.GamesStoragePath) ?? panic("Games resource not found")
            let gameRef = gamesref.borrowGameRef(GameUId:gameUId)
            gameRef.setImgAndTypes(imageHash:imageHash,templateType:templateType, gameType:gameType,slogan:slogan)
            log("Theme setted!")
          }
        }    
        `,
    args: (arg, t) => [
      arg(parseInt(gameUId), t.UInt64),
      arg(imageHash, t.String),
      arg(templateType, t.String),
      arg(gameType, t.String),
      arg(slogan, t.String),
    ],
    proposer: fcl.authz,
    payer: fcl.authz,
    authorizations: [fcl.authz],
    limit: 999,
  })

  console.log('Here is the transaction: ' + txId)
  fcl.tx(txId).subscribe((res) => {
    console.log(res)
    if (res.status === 0 || res.status === 1) {
      console.log('Pending...');
    } else if (res.status === 2) {
      console.log('Finalized...')
    } else if (res.status === 3) {
      console.log('Executed...');
    } else if (res.status === 4) {
      console.log('Sealed!');
      setTimeout(() => console.log('Run Transaction'), 2000); // We added this line
    }
  })

  await fcl.tx(txId).onceSealed()
}

export async function mintGameNFT(hostAddr, gameUId, num) {
  const txId = await fcl.mutate({
    cadence: `
        import Racenumber from 0x01
        import FlowToken from 0x01
        import FungibleToken from 0x01
        import NonFungibleToken from 0x01
        
        transaction(hostAddr:Address,gameUId:UInt64,num:UInt64) {
        
          prepare(acct: AuthAccount) {
            let hostAcct = getAccount(hostAddr)
            let gamesRef = hostAcct.getCapability<&Racenumber.Games{Racenumber.GamesPublic}>(Racenumber.GamesPublicPath).borrow() ?? panic("Games resource not found")
            let eventRef = gamesRef.borrowPublicGameRef(GameUId: gameUId)
            if !acct.getCapability<&Racenumber.Collection{Racenumber.CollectionPublic}>(Racenumber.NumberNFTCollectionPublicPath).check(){
                let collection <- Racenumber.createEmptyCollection()   
                acct.save<@Racenumber.Collection>(<- collection, to: Racenumber.NumberNFTCollectionStoragePath)
                acct.link<&Racenumber.Collection{Racenumber.CollectionPublic,NonFungibleToken.CollectionPublic}>(Racenumber.NumberNFTCollectionPublicPath, target:Racenumber.NumberNFTCollectionStoragePath)
                log("Number Collection created!")
            }
            let numberCollectionRef = acct.getCapability<&Racenumber.Collection{NonFungibleToken.CollectionPublic}>(Racenumber.NumberNFTCollectionPublicPath).borrow() ?? panic("Number Collection not found")
            
            let hasVault = acct.getCapability<&FlowToken.Vault{FungibleToken.Provider, FungibleToken.Receiver, FungibleToken.Balance}>(FlowToken.FlowTokenVaultPublic).check()
            if !hasVault{
              acct.save(<-FlowToken.createEmptyVault(), to: FlowToken.FlowTokenVaultStorage)
              acct.link<&FlowToken.Vault{FungibleToken.Provider, FungibleToken.Receiver, FungibleToken.Balance}>(FlowToken.FlowTokenVaultPublic, target: FlowToken.FlowTokenVaultStorage)
              log("TokenVault created")
            }
            let vault = acct.borrow<&FlowToken.Vault>(from: FlowToken.FlowTokenVaultStorage) ?? panic("Flowtoken Vault not found!")
            let flowToken <- vault.withdraw(amount: eventRef.price) as! @FlowToken.Vault
            eventRef.mintNumber(num:num,recipient:numberCollectionRef,flowVault:<-flowToken)
            log("Expected Number NFT minted!")
          }
        
          execute {
          
          }
        }
      
        `,
    args: (arg, t) => [
      arg(hostAddr, t.Address),
      arg(parseInt(gameUId), t.UInt64),
      arg(parseInt(num), t.UInt64),
    ],
    proposer: fcl.authz,
    payer: fcl.authz,
    authorizations: [fcl.authz],
    limit: 999,
  })

  console.log('Here is the transaction: ' + txId)
  fcl.tx(txId).subscribe((res) => {
    console.log(res)
    if (res.status === 0 || res.status === 1) {
      console.log('Pending...');
    } else if (res.status === 2) {
      console.log('Finalized...')
    } else if (res.status === 3) {
      console.log('Executed...');
    } else if (res.status === 4) {
      console.log('Sealed!');
      setTimeout(() => console.log('Run Transaction'), 2000); // We added this line
    }
  })

  await fcl.tx(txId).onceSealed()
}

export async function mintThemeNFT(hostAddr, gameUId, background) {
  const txId = await fcl.mutate({
    cadence: `
        import Racenumber from 0x01
        transaction(hostAddr:Address, gameUId:UInt64,background:String) {

          prepare(acct: AuthAccount) {
            if !acct.getCapability<&Racenumber.ThemeCollection{Racenumber.ThemeCollectionPublic}>(Racenumber.ThemeNFTCollectionPublicPath).check(){
                let collection <- Racenumber.createEmptyThemeCollection()   
                acct.save<@Racenumber.ThemeCollection>(<- collection, to: Racenumber.ThemeNFTCollectionStoragePath)
                acct.link<&Racenumber.ThemeCollection{Racenumber.ThemeCollectionPublic}>(Racenumber.ThemeNFTCollectionPublicPath, target:Racenumber.ThemeNFTCollectionStoragePath)
                log("Theme Collection created!")
            }
            let hostAcct = getAccount(hostAddr)
            let gamesRef = acct.getCapability<&Racenumber.Games{Racenumber.GamesPublic}>(Racenumber.GamesPublicPath).borrow() ?? panic("Events resource not found")
            let gameRef = gamesRef.borrowPublicGameRef(GameUId: gameUId)
            let collectionCap = acct.getCapability<&Racenumber.Collection{Racenumber.CollectionPublic}>(Racenumber.NumberNFTCollectionPublicPath)
            let collectionRef = collectionCap.borrow() ?? panic("Your address hasn't initialized game collection!")
            let numberNFT = collectionRef.borrowNumberNFT(id:gameUId) 
            let themeCollectionRef = acct.getCapability<&Racenumber.ThemeCollection{Racenumber.ThemeCollectionPublic}>(Racenumber.ThemeNFTCollectionPublicPath).borrow() ?? panic("Theme Collection not found")
            gameRef.mintTheme(collectionCap:collectionCap,gameRef:gameRef,num:numberNFT.num,background:background, recipient: themeCollectionRef)
            log("Them NFT minted!")
          }
        
          execute {
            
          }
        }
        `,
    args: (arg, t) => [
      arg(hostAddr, t.Address),
      arg(parseInt(gameUId), t.UInt64),
      arg(background, t.String),
    ],
    proposer: fcl.authz,
    payer: fcl.authz,
    authorizations: [fcl.authz],
    limit: 999,
  })

  console.log('Here is the transaction: ' + txId)
  fcl.tx(txId).subscribe((res) => {
    console.log(res)
    if (res.status === 0 || res.status === 1) {
      console.log('Pending...');
    } else if (res.status === 2) {
      console.log('Finalized...')
    } else if (res.status === 3) {
      console.log('Executed...');
    } else if (res.status === 4) {
      console.log('Sealed!');
      setTimeout(() => console.log('Run Transaction'), 2000); // We added this line
    }
  })

  await fcl.tx(txId).onceSealed()
}

//默认创建余额10000.0
export async function createFlowtokenVault() {
  const txId = await fcl.mutate({
    cadence: `
        import FlowToken from 0x01
        import FungibleToken from 0x01
        transaction() {
          prepare(acct: AuthAccount) {
            acct.save(<-FlowToken.createEmptyVault(), to: FlowToken.FlowTokenVaultStorage)
            acct.link<&FlowToken.Vault{FungibleToken.Provider, FungibleToken.Receiver, FungibleToken.Balance}>(FlowToken.FlowTokenVaultPublic, target: FlowToken.FlowTokenVaultStorage)
          }
        }
        `,
    args: (arg, t) =>[] ,
    proposer: fcl.authz,
    payer: fcl.authz,
    authorizations: [fcl.authz],
    limit: 999,
  })

  console.log('Here is the transaction: ' + txId)
  fcl.tx(txId).subscribe((res) => {
    console.log(res)
    if (res.status === 0 || res.status === 1) {
      console.log('Pending...');
    } else if (res.status === 2) {
      console.log('Finalized...')
    } else if (res.status === 3) {
      console.log('Executed...');
    } else if (res.status === 4) {
      console.log('Sealed!');
      setTimeout(() => console.log('Run Transaction'), 2000); // We added this line
    }
  })

  await fcl.tx(txId).onceSealed()
}

