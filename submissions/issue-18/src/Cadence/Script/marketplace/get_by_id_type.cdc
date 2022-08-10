// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"

//testnet
import SoulMadeMain from 0xb4187e54e0ed55a8
import SoulMadeComponent from 0xb4187e54e0ed55a8
import SoulMadeMarketplace from 0xb4187e54e0ed55a8

pub fun main(id: UInt64, nftType: String) : SoulMadeMarketplace.SoulMadeSaleData? {
  // platform address
  let address: Address = 0xb4187e54e0ed55a8
  // let address : Address = 0x76b2527585e45db4

  if nftType == "SoulMadeMain"{
    let mainSale = SoulMadeMarketplace.getSoulMadeMainSale(address: address, id: id)
    return SoulMadeMarketplace.SoulMadeSaleData(id: mainSale.id, price: mainSale.price, nftType: "SoulMadeMain", mainDetail: mainSale.mainDetail, componentDetail: nil)
  } else if nftType == "SoulMadeComponent" {
    let componentSale = SoulMadeMarketplace.getSoulMadeComponentSale(address: address, id: id)
    return SoulMadeMarketplace.SoulMadeSaleData( id: componentSale.id, price: componentSale.price, nftType: "SoulMadeComponent", mainDetail: nil, componentDetail: componentSale.componentDetail )
  }

  return nil

}