import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import SwapTrader from "../../contracts/SwapTrader.cdc"
import CaaPass from "../../contracts/CaaPass.cdc"

transaction(pairID: UInt64, inputs: [[UInt64; 3]], outputs: [[UInt64; 3]]) {
  let swapTraderList: &SwapTrader.SwapPairList
  let sourceReceiver: Capability<&{NonFungibleToken.CollectionPublic}>
  let targetCollection: Capability<&{NonFungibleToken.CollectionPublic}>
  let targetProvider: Capability<&{NonFungibleToken.Provider}>

  prepare(signer: AuthAccount) {
    self.swapTraderList = signer.borrow<&SwapTrader.SwapPairList>(from: SwapTrader.SwapPairListStoragePath)
      ?? panic("Could not borrow swap-pair list resource reference")

    // avoid missing collection
    if signer.borrow<&CaaPass.Collection>(from: CaaPass.CollectionStoragePath) == nil {
      let collection <- CaaPass.createEmptyCollection() as! @CaaPass.Collection

      signer.save(<-collection, to: CaaPass.CollectionStoragePath)

      signer.link<&{NonFungibleToken.CollectionPublic, CaaPass.CollectionPublic}>(
          CaaPass.CollectionPublicPath,
          target: CaaPass.CollectionStoragePath)
    }

    // collection capablitity
    self.targetCollection = signer.getCapability<&{NonFungibleToken.CollectionPublic}>(CaaPass.CollectionPublicPath)
    self.sourceReceiver = self.targetCollection

    // private capability
    let CAA_PASS_PRIVATE: PrivatePath = /private/caaPassCollection

    if signer.getCapability(CAA_PASS_PRIVATE).borrow<&{NonFungibleToken.Provider}>() != nil {
      self.targetProvider = signer.getCapability<&{NonFungibleToken.Provider}>(CAA_PASS_PRIVATE)
    } else {
      self.targetProvider = signer.link<&{NonFungibleToken.Provider}>(CAA_PASS_PRIVATE, target: CaaPass.CollectionStoragePath)!
    }
  }

  execute { 
    // should be ((UInt64,UInt64):A.f8xxxxx7.CaaPass.NFT)
    let casPassIdentifier = CaaPass.NFT.getType().identifier
    // change to A.f8xxxxx7.CaaPass.NFT
    let resourceIdentifier = casPassIdentifier.slice(from: 17, upTo: casPassIdentifier.length - 1)

    let sourceAttrs: [SwapTrader.SwapAttribute] = []
    // setup inputs
    for one in inputs {
      sourceAttrs.append(SwapTrader.SwapAttribute(
        resourceIdentifier: resourceIdentifier,
        minId: one[0],
        maxId: one[1],
        amount: one[2]
      ))
    }

    let targetAttrs: [SwapTrader.SwapAttribute] = []
    // setup outputs
    for one in outputs {
      targetAttrs.append(SwapTrader.SwapAttribute(
        resourceIdentifier: resourceIdentifier,
        minId: one[0],
        maxId: one[1],
        amount: one[2]
      ))
    }

    // register
    self.swapTraderList.registerSwapPair(index: pairID, pair: SwapTrader.SwapPair(
      sourceReceiver: self.sourceReceiver,
      targetCollection: self.targetCollection,
      targetProvider: self.targetProvider,
      sourceAttrs: sourceAttrs,
      targetAttrs: targetAttrs,
      paused: false
    ))
  }
}
 