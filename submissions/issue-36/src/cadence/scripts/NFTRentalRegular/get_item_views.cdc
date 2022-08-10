import NFTRentalRegular from "../../contracts/NFTRentalRegular.cdc"

pub fun main(tokenId: UInt64): {Type: AnyStruct}? {
  return NFTRentalRegular.getViews(tokenId: tokenId)
}