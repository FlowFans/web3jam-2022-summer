import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4

transaction(componentIdList: [UInt64], mainName: String) {
    let mainCollectionRef: &SoulMadeMain.Collection
    let componentCollectionRef: &SoulMadeComponent.Collection

    prepare(acct: AuthAccount) {
        self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow Main reference")
        self.componentCollectionRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
            ?? panic("Could not borrow Component reference")
    }

    execute {
      var newMain <- SoulMadeMain.mintMain(series: "SoulySushi")
      newMain.setName(mainName)

      for componentId in componentIdList{
        var component <- self.componentCollectionRef.withdraw(withdrawID: componentId) as! @SoulMadeComponent.NFT
        var empty <- newMain.depositComponent(componentNft: <- component)

        if empty != nil{
          panic("Empty not nil")
        }
        destroy empty
      }

      self.mainCollectionRef.deposit(token: <- newMain)
      
    }
}