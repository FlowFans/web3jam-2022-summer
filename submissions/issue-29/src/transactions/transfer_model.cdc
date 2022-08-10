import OverluModel from 0xOverluModel
import NonFungibleToken from 0xNonFungibleToken

transaction(itemId: UInt64, receiver: Address) {
  var senderCollection: &OverluModel.Collection
  var receiverCollection: &{NonFungibleToken.Receiver}
  prepare(account: AuthAccount) {
    self.senderCollection = account.borrow<&OverluModel.Collection>(from: OverluModel.CollectionStoragePath)!
    let receiverCollectionCap = getAccount(receiver).getCapability<&{NonFungibleToken.Receiver}>(OverluModel.CollectionPublicPath)
    self.receiverCollection = receiverCollectionCap.borrow()?? panic("Canot borrow receiver's collection")
  }
  execute {
    self.receiverCollection.deposit(token: <- self.senderCollection.withdraw(withdrawID: itemId))
  }
}
