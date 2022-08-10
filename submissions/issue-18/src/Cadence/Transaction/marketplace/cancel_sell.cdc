// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"
// import FungibleToken from 0xee82856bf20e2aa6
// import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
// import FlowToken from 0x0ae53cb6e3f42a79


import SoulMadeMain from 0xb4187e54e0ed55a8
import SoulMadeComponent from 0xb4187e54e0ed55a8
import SoulMadeMarketplace from 0xb4187e54e0ed55a8
import FungibleToken from 0x9a0766d93b6608b7
import FlowToken from 0x7e60df042a9c0868

transaction(nftId: UInt64, nftType: String) {
  let soulMadeMainCollection: &SoulMadeMain.Collection
  let soulMadeComponentCollection: &SoulMadeComponent.Collection
  let marketplace: &SoulMadeMarketplace.SaleCollection

  prepare(account: AuthAccount) {
    let marketplaceCap = account.getCapability<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath)
    // if sale collection is not created yet we make it.
    if !marketplaceCap.check() {
          let wallet =  account.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)
          let sale <- SoulMadeMarketplace.createSaleCollection(ownerVault: wallet)

        // store an empty NFT Collection in account storage
        account.save<@SoulMadeMarketplace.SaleCollection>(<- sale, to:SoulMadeMarketplace.CollectionStoragePath)
        // publish a capability to the Collection in storage
        account.link<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath, target: SoulMadeMarketplace.CollectionStoragePath)
    }

    // todo: check the force operator
    self.marketplace = account.borrow<&SoulMadeMarketplace.SaleCollection>(from: SoulMadeMarketplace.CollectionStoragePath)!
    self.soulMadeMainCollection = account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
    self.soulMadeComponentCollection = account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
  }

  execute {
    if nftType == "SoulMadeMain"{
      let mainNft <- self.marketplace.withdrawSoulMadeMain(tokenId: nftId)
      self.soulMadeMainCollection.deposit(token: <- mainNft)
    } else if nftType == "SoulMadeComponent" {
      let componentNft <- self.marketplace.withdrawSoulMadeComponent(tokenId: nftId)
      self.soulMadeComponentCollection.deposit(token: <- componentNft)
    } else {
      panic("Unknown NFT Type Specified")
    }
  }
}