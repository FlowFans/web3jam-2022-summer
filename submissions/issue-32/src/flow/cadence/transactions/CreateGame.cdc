import Racenumber from 0xf8d6e0586b0a20c7
import FlowToken from 0xf8d6e0586b0a20c7
import FungibleToken from 0xf8d6e0586b0a20c7

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

