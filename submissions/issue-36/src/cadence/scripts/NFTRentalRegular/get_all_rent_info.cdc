import NFTRentalRegular from "../../contracts/NFTRentalRegular.cdc"

pub fun main(): [NFTRentalRegular.Promise?] {
  return NFTRentalRegular.getAllRentInfo()
}