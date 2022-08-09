import MelodyTicket from 0xMelodyTicket
import NonFungibleToken from 0xNonFungibleToken

transaction(itemId: UInt64) {
  var senderCollection: &MelodyTicket.Collection
  prepare(account: AuthAccount) {
    self.senderCollection = account.borrow<&MelodyTicket.Collection>(from: MelodyTicket.CollectionStoragePath)!
  }
  execute {
     let ticket <- self.senderCollection.withdraw(withdrawID: itemId)
     destroy ticket
  }
}
