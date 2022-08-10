import Racenumber from 0xf8d6e0586b0a20c7
import FlowToken from 0xf8d6e0586b0a20c7
import FungibleToken from 0xf8d6e0586b0a20c7
import NonFungibleToken from 0xf8d6e0586b0a20c7

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
