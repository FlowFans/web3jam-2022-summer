import OverluModel from 0xOverluModel
import OverluDNA from 0xOverluDNA
import NonFungibleToken from 0xNonFungibleToken

transaction(modelId: UInt64, dnaId: UInt64) {
  var modelCollection: &OverluModel.Collection
  var dnaNFT: @OverluDNA.NFT
  prepare(account: AuthAccount) {
    self.modelCollection = account.borrow<&OverluModel.Collection>(from: OverluModel.CollectionStoragePath)!

    let dnaCollection = account.borrow<&OverluDNA.Collection>(from: OverluDNA.CollectionStoragePath)!
    self.dnaNFT <- dnaCollection.withdraw(withdrawID: dnaId) as! @OverluDNA.NFT
  }
  execute {
    let modelRef = self.modelCollection.borrowOverluModel(id:modelId)!
    modelRef.expandModel(dna: <- self.dnaNFT)
  }
}
