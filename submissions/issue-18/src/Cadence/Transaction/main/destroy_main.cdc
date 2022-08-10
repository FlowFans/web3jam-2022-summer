import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"

//testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import NonFungibleToken from 0x631e88ae7f1d7c20

transaction(mainId: UInt64) {

  let mainNftProvider: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>

  prepare(acct: AuthAccount) {
    self.mainNftProvider = acct.getCapability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(SoulMadeMain.CollectionPrivatePath)!
    assert(self.mainNftProvider.borrow() != nil, message: "Missing or mis-typed  provider")
  }

  execute {
  let nft <- self.mainNftProvider.borrow()!.withdraw(withdrawID: mainId)
  destroy nft
}


}

//[11, 10, 9, 8, 6, 7]