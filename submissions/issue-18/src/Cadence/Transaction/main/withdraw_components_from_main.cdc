import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4

transaction(mainId: UInt64, categoryList: [String]) {
    let mainCollectionRef: &SoulMadeMain.Collection
    let componentCollectionRef: &SoulMadeComponent.Collection

    prepare(acct: AuthAccount) {
        // todo: actually, do we have to always borrow storage?
        self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
        self.componentCollectionRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
    }

    execute {
      for category in categoryList{
        var component <- self.mainCollectionRef.borrowMainPrivate(id: mainId).withdrawComponent(category: category)
        // todo: check if this is component okay?
        self.componentCollectionRef.deposit(token: <- component!)
      }
      
    }
}