import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"

//testnet
// import SoulMadeMain from 0x76b2527585e45db4

transaction {
  prepare(acct: AuthAccount) {

    // Check if a SoulMadeMain collection already exists
    if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
        // Create a new Rewards collection
        let collection <- SoulMadeMain.createEmptyCollection()
        // Put the new collection in storage
        acct.save(<- collection, to: SoulMadeMain.CollectionStoragePath)
        // Create a public Capability for the collection
        acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)

        //acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPrivate}>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
        acct.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
    }
  }
}

 