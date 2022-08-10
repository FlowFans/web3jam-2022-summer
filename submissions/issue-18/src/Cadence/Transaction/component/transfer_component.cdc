
import SoulMadeComponent from 0x421c19b7dc122357

transaction(nftId: UInt64, to: Address) {
  let soulMadeComponentCollection: &SoulMadeComponent.Collection
  let componentNftCollection: &{SoulMadeComponent.CollectionPublic}

  prepare(account: AuthAccount) {
    self.soulMadeComponentCollection = account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
    self.componentNftCollection = getAccount(to).getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Cannot borrow Main NFT collection receiver from account")
  }

  execute {
    let nft <- self.soulMadeComponentCollection.withdraw(withdrawID: nftId)
    self.componentNftCollection.deposit(token : <- nft)
  }
}