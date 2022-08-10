import MelodyTicket from 0xMelodyTicket
import NonFungibleToken from 0xNonFungibleToken

transaction(itemId: UInt64, receiver: Address) {
  var senderCollection: &MelodyTicket.Collection
  var receiverCollection: &{NonFungibleToken.CollectionPublic}
  prepare(account: AuthAccount) {
    self.senderCollection = account.borrow<&MelodyTicket.Collection>(from: MelodyTicket.CollectionStoragePath)!
    let receiverCollectionCap = getAccount(receiver).getCapability<&{NonFungibleToken.CollectionPublic}>(MelodyTicket.CollectionPublicPath)
    self.receiverCollection = receiverCollectionCap.borrow()?? panic("Canot borrow receiver's collection")
  }
  execute {
    self.receiverCollection.deposit(token: <- self.senderCollection.withdraw(withdrawID: itemId))
  }
}
