import Main from "../../contracts/Main.cdc"

//testnet
// import Main from 0x76b2527585e45db4

transaction(mainIdList: [UInt64]) {
    let mainCollectionRef: &Main.Collection

    prepare(acct: AuthAccount) {
        // todo: should this be &Main.Collection? Or it can be any Type as this is from StoragePath, not Public or Private path
        self.mainCollectionRef = acct.borrow<&Main.Collection>(from: Main.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
    }

    execute {
      for mainId in mainIdList{
        var newName = self.mainCollectionRef.borrowMain(id: mainId).getAllComponentDetail()["Body"]!.name
        self.mainCollectionRef.borrowMainPrivate(id: mainId).setName(newName)
      }
    }
}