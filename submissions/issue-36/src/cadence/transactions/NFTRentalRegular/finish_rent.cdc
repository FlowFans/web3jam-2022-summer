import NFTRentalRegular from "../../contracts/NFTRentalRegular.cdc"

transaction(tokenId: UInt64, feeAmount: UFix64) {
  let signer: AuthAccount

  prepare(signer: AuthAccount) {
    self.signer = signer
  }

  execute {
    NFTRentalRegular.finishRent(acct: self.signer, tokenId: tokenId)
  }
}