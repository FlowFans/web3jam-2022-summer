// 这里都是get方法
import * as fcl from '@onflow/fcl'
import { GameDetailClass, ThemeMetaClass } from './types'
import { config } from "@onflow/fcl";
import CHAIN_CONFIG from "./config.json"
if (CHAIN_CONFIG.env== "testnet") {
    config(CHAIN_CONFIG.testnet)
} else {
    config(CHAIN_CONFIG.emulator)
}


export async function getAllGames() {
    const resp = await fcl.query({
        cadence: `
        import Racenumber from 0x01
        pub fun main():{UInt64:Racenumber.GameDetail} {
            return Racenumber.getAllGames()
        }
        `,
        args: (arg, t) => []
    })

    let games = Object.entries(resp).map(d => {
        let [k, v] = d
        return new GameDetailClass(v.gameName, v.timestamp, v.issues, v.mintedNum, v.uid, v.gameId, v.hostAddr, v.price, v.imageHash, v.templateType, v.gameType, v.slogan)
    })
    console.log("getAllGames", games)
    return games
}

export async function getGameByOwnerAddr(addr) {
    const resp = await fcl.query({
        cadence: `
        import Racenumber from 0x01
        pub fun main(addr:Address):{UInt64:Racenumber.GameDetail} {
            let acct = getAccount(addr)
            let eventsRef = acct.getCapability<&Racenumber.Games{Racenumber.GamesPublic}>(Racenumber.GamesPublicPath).borrow() ?? panic("Events resource not found")
            let allEvents = eventsRef.getAllGames()
            log(allEvents.length)
            return allEvents
          }          
        `,
        args: (arg, t) => [arg(addr, t.Address)]
    })

    let games = Object.entries(resp).map(d => {
        let [k, v] = d
        return new GameDetailClass(v.gameName, v.timestamp, v.issues, v.mintedNum, v.uid, v.gameId, v.hostAddr, v.price, v.imageHash, v.templateType, v.gameType, v.slogan)
    })
    console.log("getGameByOwnerAddr>>>", games)
    return games
}

export async function getGameByGameId(uid) {
    const v = await fcl.query({
        cadence: `
        import Racenumber from 0x01
        pub fun main(uid:UInt64):Racenumber.GameDetail {
            return Racenumber.getGameById(id:uid)
        }       
        `,
        args: (arg, t) => [arg(parseInt(uid), t.UInt64)]
    })
    let game = null
    if (v) {
        game = new GameDetailClass(v.gameName, v.timestamp, v.issues, v.mintedNum, v.uid, v.gameId, v.hostAddr, v.price, v.imageHash, v.templateType, v.gameType, v.slogan)
    }
    console.log("getGameByGameId>>>", game)
    return game
}

export async function getMintedNFTList(hostAddr, gameUId) {
    let resp = await fcl.query({
        cadence: `
        import Racenumber from 0x01
        pub fun main(hostAddr:Address,gameUId:UInt64):[UInt64]{
            let hostAcct = getAccount(hostAddr)
            let gamesRef = hostAcct.getCapability<&Racenumber.Games{Racenumber.GamesPublic}>(Racenumber.GamesPublicPath).borrow() ?? panic("Games resource not found")
            let gameRef = gamesRef.borrowPublicGameRef(GameUId: gameUId) as &Racenumber.Game{Racenumber.GamePublic}
            return gameRef.getMintedNftList()
        }    
        `,
        args: (arg, t) => [arg(hostAddr, t.Address), arg(parseInt(gameUId), t.UInt64)]
    })
    resp = resp.map(d => parseInt(d))
    console.log("getMintedNFTList>>>", resp)
    return resp
}

export async function getUserNFTs(userAddr) {
    let resp = await fcl.query({
        cadence: `
        import Racenumber from 0x01
        pub fun main(addr:Address):[Racenumber.ThemeMeta]{
            let acct = getAccount(addr)
            let nfts:[Racenumber.ThemeMeta] = []
            if !acct.getCapability<&Racenumber.ThemeCollection{Racenumber.ThemeCollectionPublic}>(Racenumber.ThemeNFTCollectionPublicPath).check(){
                return nfts
            }
            let themeCollectionRef = acct.getCapability<&Racenumber.ThemeCollection{Racenumber.ThemeCollectionPublic}>(Racenumber.ThemeNFTCollectionPublicPath).borrow()!
            let ids = themeCollectionRef.getIDs()
            for id in ids{
                let nft = themeCollectionRef.borrowNFT(id: id)
                let gameDetail = Racenumber.getAllGames()[nft.gameUId]!
                let metadata = Racenumber.ThemeMeta(gameDetail:gameDetail,num:nft.num,background:nft.background)
                nfts.append(metadata)
            }
            log(nfts.length)
            return nfts
        }
        `,
        args: (arg, t) => [arg(userAddr, t.Address)]
    })
    console.log(resp)

    let nfts = resp.map(d => {
        let v = d.gameDetail
        let gameDetail = new GameDetailClass(v.gameName, v.timestamp, v.issues, v.mintedNum, v.uid, v.gameId, v.hostAddr, v.price, v.imageHash, v.templateType, v.gameType, v.slogan)
        let nft = new ThemeMetaClass(gameDetail, d.num, d.background)
        return nft
    })
    console.log("getUserNFTs>>>", nfts)
    return nfts
}

export async function getBalance(addr) {
    const resp = await fcl.query({
        cadence: `
            import FlowToken from 0x01
            import FungibleToken from 0x01
            pub fun main(addr:Address): UFix64{
                let acct = getAccount(addr).getCapability<&FlowToken.Vault{FungibleToken.Balance}>(FlowToken.FlowTokenVaultPublic).borrow() ?? panic("VAult not found")
                return acct.balance
            }
        `,
        args: (arg, t) => [arg(addr, t.Address)]
    })

    console.log("getBalance", resp)
    return resp
}