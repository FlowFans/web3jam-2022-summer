// import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
// import SoulMade from "../../contracts/SoulMade.cdc"
// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadePack from "../../contracts/SoulMadePack.cdc"
// import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"
// import NFTStorefront from "../../contracts/NFTStorefront.cdc"
// import FungibleToken from 0xee82856bf20e2aa6
// import FlowToken from 0x0ae53cb6e3f42a79

//testnet
import FUSD from 0xe223d8a629e49c68
import FungibleToken from 0x9a0766d93b6608b7
import NonFungibleToken from 0x631e88ae7f1d7c20
import FlowToken from 0x7e60df042a9c0868
import SoulMadeMain from 0x421c19b7dc122357
import SoulMadeComponent from 0x421c19b7dc122357
import SoulMadePack from 0x421c19b7dc122357
import SoulMadeMarketplace from 0x421c19b7dc122357
import NFTStorefront from 0x94b06cfca1d8a476

// mainnet
// import FungibleToken from 0xf233dcee88fe0abe
// import NonFungibleToken from 0x1d7e57aa55817448
// import FlowToken from 0x1654653399040a61
// import SoulMadeMain from 0x9a57dfe5c8ce609c
// import SoulMadeComponent from 0x9a57dfe5c8ce609c
// import SoulMadePack from 0x9a57dfe5c8ce609c
// import SoulMadeMarketplace from 0x9a57dfe5c8ce609c
// import NFTStorefront from 0x4eb8a10cb9f87357

transaction {
  prepare(acct: AuthAccount) {
    
    if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
      acct.save(<- SoulMadeMain.createEmptyCollection(), to: SoulMadeMain.CollectionStoragePath)
      acct.link<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)
      acct.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)

      // todo: is this correct?
      acct.save(<- SoulMadeMain.createEmptyCollection(), to: /storage/SoulMadeMainCollectionFree)
      acct.link<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(/public/SoulMadeMainCollectionFree, target: /storage/SoulMadeMainCollectionFree)
    }

    if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
      acct.save(<- SoulMadeComponent.createEmptyCollection(), to: SoulMadeComponent.CollectionStoragePath)
      acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)
      acct.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)

      acct.save(<- SoulMadeMain.createEmptyCollection(), to: /storage/SoulMadeComponentCollectionFree)
      acct.link<&{NonFungibleToken.Provider}>(/public/SoulMadeComponentCollectionFree, target: /storage/SoulMadeComponentCollectionFree)
    }

    if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) == nil {
      acct.save(<- SoulMadePack.createEmptyCollection(), to: SoulMadePack.CollectionStoragePath)
      acct.link<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)
      acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionPrivatePath, target: SoulMadePack.CollectionStoragePath)

      acct.save(<- SoulMadeMain.createEmptyCollection(), to: /storage/SoulMadePackCollectionFree)
      acct.link<&{NonFungibleToken.Provider}>(/public/SoulMadePackCollectionFree, target: /storage/SoulMadePackCollectionFree)    
    }

    // For Free Claim, to setup admin account
    if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionFreeClaimStoragePath) == nil {
      acct.save(<- SoulMadePack.createEmptyCollection(), to: SoulMadePack.CollectionFreeClaimStoragePath)
      acct.link<&{SoulMadePack.CollectionFreeClaim,SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionFreeClaimPublicPath, target: SoulMadePack.CollectionFreeClaimStoragePath)
      acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionFreeClaimPrivatePath, target: SoulMadePack.CollectionFreeClaimStoragePath)

      acct.save(<- SoulMadeMain.createEmptyCollection(), to: /storage/SoulMadePackFreeClaimCollectionFree)
      acct.link<&{NonFungibleToken.Provider}>(/public/SoulMadePackFreeClaimCollectionFree, target: /storage/SoulMadePackFreeClaimCollectionFree)    
    }


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

  }
}

