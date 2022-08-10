import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4

transaction(mainId: UInt64, componentIdList: [UInt64]) {
    let mainCollectionRef: &SoulMadeMain.Collection
    let componentCollectionRef: &SoulMadeComponent.Collection

    prepare(acct: AuthAccount) {
        self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
        self.componentCollectionRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
    }

    execute {
      for componentId in componentIdList{
        var component <- self.componentCollectionRef.withdraw(withdrawID: componentId) as! @SoulMadeComponent.NFT
        var old <- self.mainCollectionRef.borrowMainPrivate(id: mainId).depositComponent(componentNft: <- component)
        if old != nil {
          self.componentCollectionRef.deposit(token: <- old!)
        } else {
          destroy old
        }
      }
      
    }
}