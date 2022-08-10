import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"
import FungibleToken from 0xee82856bf20e2aa6
import FlowToken from 0x0ae53cb6e3f42a79

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import SoulMadeMarketplace from 0x76b2527585e45db4
// import FungibleToken from 0x9a0766d93b6608b7
// import FlowToken from 0x7e60df042a9c0868

transaction(nftIdList: [UInt64], price: UFix64, nftType: String) {
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
    for nftId in nftIdList{
      if nftType == "SoulMadeMain"{
        let mainNft <- self.soulMadeMainCollection.withdraw(withdrawID: nftId) as! @SoulMadeMain.NFT
        self.marketplace.listSoulMadeMainForSale(token: <- mainNft, price: price)
      } else if nftType == "SoulMadeComponent" {
        let componentNft <- self.soulMadeComponentCollection.withdraw(withdrawID: nftId) as! @SoulMadeComponent.NFT
        self.marketplace.listSoulMadeComponentForSale(token: <- componentNft, price: price)
      } else {
        panic("Unknown NFT Type Specified")
      }
    }

  }
}