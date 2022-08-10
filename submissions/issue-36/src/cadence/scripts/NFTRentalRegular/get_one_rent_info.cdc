import NFTRentalRegular from "../../contracts/NFTRentalRegular.cdc"

pub fun main(tokenId: UInt64): NFTRentalRegular.Promise? {
  return NFTRentalRegular.getOneRentInfo(tokenId: tokenId)
}