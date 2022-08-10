// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadeMarketplace from "../../contracts/SoulMadeComponent.cdc"
// import FungibleToken from 0xee82856bf20e2aa6
// import FlowToken from 0x0ae53cb6e3f42a79


import SoulMadeMain from 0x421c19b7dc122357

transaction(nftId: UInt64, to: Address) {
  let soulMadeMainCollection: &SoulMadeMain.Collection
  let mainNftCollection: &{SoulMadeMain.CollectionPublic}

  prepare(account: AuthAccount) {
    self.soulMadeMainCollection = account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
    self.mainNftCollection = getAccount(to).getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Cannot borrow Main NFT collection receiver from account")
  }

  execute {
    let mainNft <- self.soulMadeMainCollection.withdraw(withdrawID: nftId)
    self.mainNftCollection.deposit(token : <- mainNft)
  }
}