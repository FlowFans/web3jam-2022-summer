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
// import FungibleToken from 0x9a0766d93b6608b7
// import NonFungibleToken from 0x631e88ae7f1d7c20
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import SoulMadePack from 0x76b2527585e45db4
// import SoulMadeMarketplace from 0x76b2527585e45db4
// import NFTStorefront from 0x94b06cfca1d8a476
// import FlowToken from 0x7e60df042a9c0868


transaction {
  prepare(acct: AuthAccount) {
    
    if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) != nil {
      destroy acct.load<@SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
    }

    if acct.borrow<&SoulMadeMain.Collection>(from: /storage/SoulMadeMainCollectionFree) != nil {
      destroy acct.load<@SoulMadeMain.Collection>(from: /storage/SoulMadeMainCollectionFree)
    }

    if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) != nil {
      destroy acct.load<@SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
    }

    if acct.borrow<&SoulMadeComponent.Collection>(from: /storage/SoulMadeComponentCollectionFree) != nil {
      destroy acct.load<@SoulMadeComponent.Collection>(from: /storage/SoulMadeComponentCollectionFree)
    }

  }
}

