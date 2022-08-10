import NFTRentalRegular from "../../contracts/NFTRentalRegular.cdc"

pub fun main(user: Address): [NFTRentalRegular.Promise?] {
  return NFTRentalRegular.getUserRented(user: user)
}