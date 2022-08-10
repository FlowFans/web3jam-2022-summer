import NFTRentalRegular from "../../contracts/NFTRentalRegular.cdc"

pub fun main(tokenId: UInt64): [Type]? {
  return NFTRentalRegular.getViewTypes(tokenId: tokenId)
}