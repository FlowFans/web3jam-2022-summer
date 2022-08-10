import component from "../../contracts/component.cdc"
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"

//testnet
// import SoulMadeComponent from 0x76b2527585e45db4
// import NonFungibleToken from 0x631e88ae7f1d7c20

transaction(componentId: UInt64) {

  let componentNftProvider: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>

  prepare(acct: AuthAccount) {
    self.componentNftProvider = acct.getCapability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(SoulMadeComponent.CollectionPrivatePath)!
    assert(self.componentNftProvider.borrow() != nil, message: "Missing or mis-typed  provider")
  }

  execute {
  let nft <- self.componentNftProvider.borrow()!.withdraw(withdrawID: componentId)
  destroy nft
  }
}