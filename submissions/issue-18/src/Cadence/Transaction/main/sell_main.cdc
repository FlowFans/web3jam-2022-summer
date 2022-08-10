import SoulMade from 0xf8d6e0586b0a20c7
import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

import FungibleToken from 0xee82856bf20e2aa6
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import FUSD from 0xf8d6e0586b0a20c7
import NFTStorefront from 0xf8d6e0586b0a20c7

// todo: add parameter to specify main or componet
transaction() {

  let fusdReceiver: Capability<&FUSD.Vault{FungibleToken.Receiver}>
  let nftProvider: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>

  prepare(acct: AuthAccount) {

    self.fusdReceiver = acct.getCapability<&FUSD.Vault{FungibleToken.Receiver}>(/public/fusdReceiver)
    assert(self.fusdReceiver.borrow() != nil, message: "Missing or mis-typed FUSD receiver")

    // todo: is this the right way of doing so?
    log("aaa")
    //var nftPrivatePath = nftType == Type<@SoulMadeMain.NFT>() ? SoulMadeMain.CollectionPrivatePath : SoulMadeComponent.CollectionPrivatePath
    var nftPrivatePath = SoulMadeMain.CollectionPrivatePath

    self.nftProvider = acct.getCapability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(nftPrivatePath)!
    assert(self.nftProvider.borrow() != nil, message: "Missing or mis-typed SoulMadeMain.Collection provider")

  }

  execute {
    //SoulMade.sellItem(saleItemID: UInt64(0), saleItemPrice: 10.0, tokenVaultReceiver: self.fusdReceiver, nftProvider: self.nftProvider, tokenVaultType: Type<@FUSD.Vault>(), nftType: nftType)
    SoulMade.sellItem(saleItemID: UInt64(0), saleItemPrice: 10.0, tokenVaultReceiver: self.fusdReceiver, nftProvider: self.nftProvider, tokenVaultType: Type<@FUSD.Vault>(), nftType: Type<@SoulMadeMain.NFT>())
  }
}
