import FungibleToken from 0xee82856bf20e2aa6
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import SoulMade from "../../contracts/SoulMade.cdc"
import SoulMadePack from "../../contracts/SoulMadePack.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

//testnet
// import FUSD from 0xe223d8a629e49c68
// import FungibleToken from 0x9a0766d93b6608b7
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import SoulMadePack from 0x76b2527585e45db4
// import NonFungibleToken from 0x631e88ae7f1d7c20

transaction {
  prepare(acct: AuthAccount) {

    if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) == nil {
        let collection <- SoulMadePack.createEmptyCollection()
        acct.save(<-collection, to: SoulMadePack.CollectionStoragePath)
        acct.link<&SoulMadePack.Collection{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)
        acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionPrivatePath, target: SoulMadePack.CollectionStoragePath)
    }

  }
}