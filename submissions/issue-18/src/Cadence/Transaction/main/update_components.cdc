import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4

/*
Data Structure:
[{String: Integer}]
Example:
[{"Body": null}, {"Head": 2}] (edited) 
*/

// todo: maybe we can restrict the update can only work if the main and component are within the same series. right now, it is only being restricted from front-end
transaction(mainNftId: UInt64, changes: [{String: UInt64?}]) {

    let mainCollectionRef: &SoulMadeMain.Collection
    let componentCollectionRef: &SoulMadeComponent.Collection

    prepare(acct: AuthAccount) {
      self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
          ?? panic("Could not borrow main reference")
      
      self.componentCollectionRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
          ?? panic("Could not borrow component reference")
    }

    execute {
      let mainNftRef = self.mainCollectionRef.borrowMainPrivate(id: mainNftId)

      for individualChange in changes{

        for category in individualChange.keys {
          // todo: why there is a force operator? Is it because it's a dictionary?
          var componentNftId: UInt64? = individualChange[category]!
          
          if(componentNftId != nil){
            // todo: maybe we can refactor this? self.componentCollectionRef appears in many places. 
            var componentNft <- self.componentCollectionRef.withdraw(withdrawID: componentNftId!) as! @SoulMadeComponent.NFT

            var old <- mainNftRef.depositComponent(componentNft: <- componentNft!)

            if old != nil {
              // todo: double check this, does this work? Old should be SoulMadeComponent.NFT type, not NonfungibleToken.NFT
              self.componentCollectionRef.deposit(token: <- old!)
            } else {
              destroy old
            }

          } else {
            // todo: why this returns optional?
            var component <- mainNftRef.withdrawComponent(category: category)
            // todo: check if this is component okay?
            // todo: why there has to be a "force operator"?
            self.componentCollectionRef.deposit(token: <- component!)
          }

        }
      }

    }

}

