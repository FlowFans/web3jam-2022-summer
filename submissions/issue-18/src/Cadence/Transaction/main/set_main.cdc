// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"

//testnet
import SoulMadeMain from 0x421c19b7dc122357

transaction(mainId: UInt64, newDescription: String, newIPFS: String, newName: String) {
    let mainCollectionRef: &SoulMadeMain.Collection

    prepare(acct: AuthAccount) {
        self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
    }

    execute {
      self.mainCollectionRef.borrowMainPrivate(id: mainId).setDescription(newDescription)
      self.mainCollectionRef.borrowMainPrivate(id: mainId).setName(newName)
      self.mainCollectionRef.borrowMainPrivate(id: mainId).setIpfsHash(newIPFS)
    }
}