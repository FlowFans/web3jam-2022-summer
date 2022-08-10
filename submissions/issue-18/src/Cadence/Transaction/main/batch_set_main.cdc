import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"

//testnet
// import SoulMadeMain from 0x76b2527585e45db4

transaction(startmainId: UInt64, endmainId: UInt64, newName: String, newIpfs: String, newDescription:String) {
    let mainCollectionRef: &SoulMadeMain.Collection

    prepare(acct: AuthAccount) {
        self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
    }

    execute {
      var id = startmainId
      while id <= endmainId {
        self.mainCollectionRef.borrowMainPrivate(id: id).setName(newName)
        self.mainCollectionRef.borrowMainPrivate(id: id).setIpfsHash(newIpfs)
        self.mainCollectionRef.borrowMainPrivate(id: id).setDescription(newDescription)
        id = id + 1
      }
    }
}