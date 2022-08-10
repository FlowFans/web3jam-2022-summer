import SoulMadeMain from 0x9a57dfe5c8ce609c

transaction(nftIds: [UInt64], to: Address) {
  let soulMadeMainCollection: &SoulMadeMain.Collection
  let mainNftCollection: &{SoulMadeMain.CollectionPublic}

  prepare(account: AuthAccount) {
    self.soulMadeMainCollection = account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
    self.mainNftCollection = getAccount(to).getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Cannot borrow Main NFT collection receiver from account")
  }

  execute {
    for nftId in nftIds {
        let mainNft <- self.soulMadeMainCollection.withdraw(withdrawID: nftId)
        self.mainNftCollection.deposit(token : <- mainNft)
    }
  }
}