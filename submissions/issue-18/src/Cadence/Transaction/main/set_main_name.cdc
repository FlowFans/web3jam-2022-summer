import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"

//testnet
// import SoulMadeMain from 0x76b2527585e45db4

transaction(mainId: UInt64, newName: String) {
    let mainCollectionRef: &SoulMadeMain.Collection

    prepare(acct: AuthAccount) {
        self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
    }

    execute {
      self.mainCollectionRef.borrowMainPrivate(id: mainId).setName(newName)
    }
}