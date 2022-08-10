// import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"

// testnet
import SoulMadeMarketplace from 0xb4187e54e0ed55a8

pub fun main(address: Address) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // let address: Address = 0xf8d6e0586b0a20c7
  // let address: Address = 0x76b2527585e45db4

  let salesData = SoulMadeMarketplace.getSoulMadeSales(address: address)
  return salesData
}
