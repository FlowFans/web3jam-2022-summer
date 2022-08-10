import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import SwapTrader from "../../contracts/SwapTrader.cdc"
import CaaPass from "../../contracts/CaaPass.cdc"

transaction(address: Address, pairID: UInt64, sourceIDs: [UInt64]) {
  let swapTraderList: &{SwapTrader.SwapPairListPublic}
  let sourceProvider: Capability<&{NonFungibleToken.Provider}>
  let targetReceiver: Capability<&{NonFungibleToken.CollectionPublic}>

  prepare(signer: AuthAccount) {
    self.swapTraderList = getAccount(address).getCapability(SwapTrader.SwapPairListPublicPath)
        .borrow<&{SwapTrader.SwapPairListPublic}>()
        ?? panic("Could not borrow swap-pair list public reference")
    
    // private capability
    let CAA_PASS_PRIVATE: PrivatePath = /private/caaPassCollection

    if signer.getCapability(CAA_PASS_PRIVATE).borrow<&{NonFungibleToken.Provider}>() != nil {
      self.sourceProvider = signer.getCapability<&{NonFungibleToken.Provider}>(CAA_PASS_PRIVATE)
    } else {
      self.sourceProvider = signer.link<&{NonFungibleToken.Provider}>(CAA_PASS_PRIVATE, target: CaaPass.CollectionStoragePath)!
    }

    // public capability
    self.targetReceiver = signer.getCapability<&{NonFungibleToken.CollectionPublic}>(CaaPass.CollectionPublicPath)
  }
  execute {
    self.swapTraderList.swapNFT(
      pairID: pairID, 
      sourceIDs: sourceIDs,
      sourceProvider: self.sourceProvider,
      targetReceiver: self.targetReceiver
    )
  }
}
