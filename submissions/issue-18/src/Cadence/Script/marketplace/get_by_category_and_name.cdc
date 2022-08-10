import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"

//testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import SoulMadeMarketplace from 0x76b2527585e45db4


pub fun main(series: String, nftType: String, category: String, nftName: String) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // platform address
  let address : Address = 0x76b2527585e45db4

  var res: [SoulMadeMarketplace.SoulMadeSaleData] = [] 

  if nftType == "SoulMadeMain"{
    let salesData = SoulMadeMarketplace.getSoulMadeMainSales(address: address)
    for mainSale in salesData {
      var saleCategory = mainSale.mainDetail!.componentDetails[0].category
      var saleName = mainSale.mainDetail!.name

      if saleCategory == category &&  saleName == nftName{
        res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: mainSale.id,
                price: mainSale.price,
                nftType: "SoulMadeMain",
                mainDetail: mainSale.mainDetail,
                componentDetail: nil
                )
              )
      }
    }
  } else if nftType == "SoulMadeComponent" {
    let salesData = SoulMadeMarketplace.getSoulMadeComponentSales(address: address)
    for componentSale in salesData {
      var saleCategory = componentSale.componentDetail.category
      var saleName = componentSale.componentDetail.name

      if saleCategory == category &&  saleName == nftName{
        res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: componentSale.id,
                price: componentSale.price,
                nftType: "SoulMadeComponent",
                mainDetail: nil,
                componentDetail: componentSale.componentDetail
                )
              )
      }
    }

  }
  return res
}