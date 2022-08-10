import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import SoulMade from "../../contracts/SoulMade.cdc"
import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
import SoulMadePack from "../../contracts/SoulMadePack.cdc"
import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"
import NFTStorefront from "../../contracts/NFTStorefront.cdc"
import FungibleToken from 0xee82856bf20e2aa6
import FlowToken from 0x0ae53cb6e3f42a79


//testnet
// import FUSD from 0xe223d8a629e49c68
// import NonFungibleToken from 0x631e88ae7f1d7c20
// import FungibleToken from 0x9a0766d93b6608b7
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import SoulMadePack from 0x76b2527585e45db4


transaction {

  let platformFreeCollectionCap: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>
  let ownNftCollection: &{SoulMadeMain.CollectionPublic}

  prepare(acct: AuthAccount) {
    
    if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
      acct.save(<- SoulMadeMain.createEmptyCollection(), to: SoulMadeMain.CollectionStoragePath)
      acct.link<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)
      acct.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
    }

    if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
      acct.save(<- SoulMadeComponent.createEmptyCollection(), to: SoulMadeComponent.CollectionStoragePath)
      acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)
      acct.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
    }

    if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) == nil {
      acct.save(<- SoulMadePack.createEmptyCollection(), to: SoulMadePack.CollectionStoragePath)
      acct.link<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)
      //todo: here is a private
      acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionPrivatePath, target: SoulMadePack.CollectionStoragePath)
    }

    // todo: these two are actually not needed.
    /*
    if acct.borrow<&SoulMadeMarketplace.SaleCollection>(from: SoulMadeMarketplace.CollectionStoragePath) == nil {
      let wallet =  acct.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)
      acct.save(<- SoulMadeMarketplace.createSaleCollection(ownerVault: wallet), to: SoulMadeMarketplace.CollectionStoragePath)
      acct.link<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath, target: SoulMadeMarketplace.CollectionStoragePath)
    }

    if acct.borrow<&NFTStorefront.Storefront>(from: NFTStorefront.StorefrontStoragePath) == nil {
      let storefront <- NFTStorefront.createStorefront() as! @NFTStorefront.Storefront
      acct.save(<-storefront, to: NFTStorefront.StorefrontStoragePath)
      acct.link<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath, target: NFTStorefront.StorefrontStoragePath)
    }
    */

    //let platformAddress : Address = 0xf8d6e0586b0a20c7 
    let platformAddress : Address = 0x76b2527585e45db4
    self.platformFreeCollectionCap = getAccount(platformAddress).getCapability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(/public/SoulMadeMainCollectionFree)
    self.ownNftCollection = acct.borrow<&{SoulMadeMain.CollectionPublic}>(from: SoulMadeMain.CollectionStoragePath) ?? panic("Cannot borrow NFT collection receiver from account")
  }

  execute{
    var id = self.platformFreeCollectionCap.borrow()!.getIDs()[0]
    self.ownNftCollection.deposit(token: <- self.platformFreeCollectionCap.borrow()!.withdraw(withdrawID: id))
  }
}

