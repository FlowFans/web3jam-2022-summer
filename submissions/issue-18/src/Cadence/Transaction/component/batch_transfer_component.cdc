
import SoulMadeComponent from 0x9a57dfe5c8ce609c

transaction(nftIds: [UInt64], to: Address) {
  let soulMadeComponentCollection: &SoulMadeComponent.Collection
  let componentNftCollection: &{SoulMadeComponent.CollectionPublic}

  prepare(account: AuthAccount) {
    self.soulMadeComponentCollection = account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
    self.componentNftCollection = getAccount(to).getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Cannot borrow Main NFT collection receiver from account")
  }

  execute {
    for nftid in nftIds {
        let nft <- self.soulMadeComponentCollection.withdraw(withdrawID: nftid)
        self.componentNftCollection.deposit(token : <- nft)
    }
  }
}