// import FungibleToken from 0xee82856bf20e2aa6
// import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
// import SoulMade from "../../contracts/SoulMade.cdc"
// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadePack from "../../contracts/SoulMadePack.cdc"

//testnet
import FUSD from 0xe223d8a629e49c68
import FungibleToken from 0x9a0766d93b6608b7
import SoulMadeMain from 0x421c19b7dc122357
import SoulMadeComponent from 0x421c19b7dc122357
import SoulMadePack from 0x421c19b7dc122357
import NonFungibleToken from 0x631e88ae7f1d7c20

transaction {
    prepare(acct: AuthAccount) {

      // Check if a SoulMadeMain collection already exists
      if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
          // Create a new Rewards collection
          let collection <- SoulMadeMain.createEmptyCollection()
          // Put the new collection in storage
          acct.save(<-collection, to: SoulMadeMain.CollectionStoragePath)
          // Create a public Capability for the collection
          acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)

          //acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPrivate}>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
          acct.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
      }

      if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
          // Create a new Rewards collection
          let collection <- SoulMadeComponent.createEmptyCollection()
          // Put the new collection in storage
          acct.save(<-collection, to: SoulMadeComponent.CollectionStoragePath)
          // Create a public Capability for the collection
          acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)

          //acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPrivate}>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
          acct.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
      }

      if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) == nil {
          let collection <- SoulMadePack.createEmptyCollection()
          acct.save(<-collection, to: SoulMadePack.CollectionStoragePath)
          acct.link<&SoulMadePack.Collection{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)

          acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionPrivatePath, target: SoulMadePack.CollectionStoragePath)
      }
    }
}



/*
import ChainmonstersRewards from 0x93615d25d14fa337
import ChainmonstersMarketplace from 0x64f83c60989ce555
import FUSD from 0x3c5959b568896393
import FungibleToken from 0xf233dcee88fe0abe

// This transaction sets up an account to use Chainmonsters Rewards, Marketplace and FUSD

transaction {
    prepare(acct: AuthAccount) {
        // Check if a Rewards collection already exists
        if acct.borrow<&ChainmonstersRewards.Collection>(from: /storage/ChainmonstersRewardCollection) == nil {
            // Create a new Rewards collection
            let collection <- ChainmonstersRewards.createEmptyCollection() as! @ChainmonstersRewards.Collection
            // Put the new collection in storage
            acct.save(<-collection, to: /storage/ChainmonstersRewardCollection)
            // Create a public Capability for the collection
            acct.link<&{ChainmonstersRewards.ChainmonstersRewardCollectionPublic}>(/public/ChainmonstersRewardCollection, target: /storage/ChainmonstersRewardCollection)
        }

        // Check if a Markerplace collection already exists
        if acct.borrow<&ChainmonstersMarketplace.Collection>(from: ChainmonstersMarketplace.CollectionStoragePath) == nil {
            // Create a new Marketplace collection
            let collection <- ChainmonstersMarketplace.createEmptyCollection()
            // Put the new collection in storage
            acct.save(<-collection, to: ChainmonstersMarketplace.CollectionStoragePath)
            // Create a public capability for the collection
            acct.link<&ChainmonstersMarketplace.Collection{ChainmonstersMarketplace.CollectionPublic}>(ChainmonstersMarketplace.CollectionPublicPath, target: ChainmonstersMarketplace.CollectionStoragePath)
        }

        // Check if a FUSD collection already exists
        if acct.borrow<&FUSD.Vault>(from: /storage/fusdVault) == nil {
            // Create a new FUSD Vault and put it in storage
            acct.save(<-FUSD.createEmptyVault(), to: /storage/fusdVault)

            // Create a public capability to the Vault that only exposes
            // the deposit function through the Receiver interface
            acct.link<&FUSD.Vault{FungibleToken.Receiver}>(
                /public/fusdReceiver,
                target: /storage/fusdVault
            )

            // Create a public capability to the Vault that only exposes
            // the balance field through the Balance interface
            acct.link<&FUSD.Vault{FungibleToken.Balance}>(
                /public/fusdBalance,
                target: /storage/fusdVault
            )
        }
    }
}
*/
 