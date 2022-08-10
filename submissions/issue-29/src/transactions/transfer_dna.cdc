import OverluDNA from 0xOverluDNA
import NonFungibleToken from 0xNonFungibleToken

transaction(itemId: UInt64, receiver: Address) {
  var senderCollection: &OverluDNA.Collection
  var receiverCollection: &{NonFungibleToken.Receiver}
  prepare(account: AuthAccount) {
    self.senderCollection = account.borrow<&OverluDNA.Collection>(from: OverluDNA.CollectionStoragePath)!
    let receiverCollectionCap = getAccount(receiver).getCapability<&{NonFungibleToken.Receiver}>(OverluDNA.CollectionPublicPath)
    self.receiverCollection = receiverCollectionCap.borrow()?? panic("Canot borrow receiver's collection")
  }
  execute {
    self.receiverCollection.deposit(token: <- self.senderCollection.withdraw(withdrawID: itemId))
  }
}
