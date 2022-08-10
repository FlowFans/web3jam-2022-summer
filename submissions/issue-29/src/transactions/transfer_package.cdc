import OverluPackage from 0xOverluPackage
import NonFungibleToken from 0xNonFungibleToken

transaction(itemId: UInt64, receiver: Address) {
  var senderCollection: &OverluPackage.Collection
  var receiverCollection: &{NonFungibleToken.Receiver}
  prepare(account: AuthAccount) {
    self.senderCollection = account.borrow<&OverluPackage.Collection>(from: OverluPackage.CollectionStoragePath)!
    let receiverCollectionCap = getAccount(receiver).getCapability<&{NonFungibleToken.Receiver}>(OverluPackage.CollectionPublicPath)
    self.receiverCollection = receiverCollectionCap.borrow()?? panic("Canot borrow receiver's collection")
  }
  execute {
    self.receiverCollection.deposit(token: <- self.senderCollection.withdraw(withdrawID: itemId))
  }
}
