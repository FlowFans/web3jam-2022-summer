// "../../contracts/NonFungibleToken.cdc"
import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import NonFungibleToken from 0x631e88ae7f1d7c20


// todo: this does not work on testnet
pub fun main(address: Address) : [{UInt64: {String: SoulMadeComponent.ComponentDetail}}] {
    // todo: use parameters
    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")

    //log(receiverRef.getIDs())
    //log(receiverRef.borrowMain(id: 0)!.getAllComponents())
    //log(receiverRef.borrowMain(id: 0).getAllComponents())
    
    //var result: [{UInt64: {String: SoulMadeComponent.ComponentInfo}}] = []
    var res : [{UInt64: {String: SoulMadeComponent.ComponentDetail}}] = []
    for mainId in receiverRef.getIDs(){
        res.append({mainId : receiverRef.borrowMain(id: mainId).getAllComponentDetail()})
    }
    
    return res
}


/*

pub struct MainComponent {
  pub let mainNftId: UInt64
  pub let category: String
  pub let componentInfo: SoulMadeComponent.ComponentInfo

  init(mainNftId: UInt64, category: String, componentInfo: SoulMadeComponent.ComponentInfo) {
    self.mainNftId = mainNftId
    self.category = category
    self.componentInfo = componentInfo
  }
}



pub fun main() : [MainComponent] {
    // todo: use parameters
    let receiverRef = getAccount(0xf8d6e0586b0a20c7)
                      .getCapability<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    
    //var result: [{UInt64: {String: SoulMadeComponent.ComponentInfo}}] = []
    var res : [MainComponent] = []
    for mainId in receiverRef.getIDs(){
        let allComponentDict = receiverRef.borrowMain(id: mainId).getAllComponents()
        for category in allComponentDict.keys{
            var mainComponent = MainComponent(mainNftId: mainId, category: category, componentInfo: allComponentDict[category]!)
            res.append(mainComponent)
        }
    }
    
    return res
}
 */






