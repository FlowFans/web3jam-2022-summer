import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import SoulMadeMarketplace from 0x76b2527585e45db4


pub fun main(series: String) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // let address: Address = 0xf8d6e0586b0a20c7
  let address: Address = 0x76b2527585e45db4
  
  // category: {name: saledata}
  var intermediate: {String : SoulMadeMarketplace.SoulMadeMainSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeMainSales(address: address)

  for sale in salesData {
    var category = sale.mainDetail!.componentDetails[0].category
    var name = sale.mainDetail!.name
    var categoryAndName = category.concat(name)

    if intermediate[categoryAndName] == nil {
      intermediate[categoryAndName] = sale
    }
  }
  var res: [SoulMadeMarketplace.SoulMadeSaleData] = []
  for mainSale in intermediate.values{
    res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: mainSale.id,
                price: mainSale.price,
                nftType: "SoulMadeMain",
                mainDetail: mainSale.mainDetail,
                componentDetail: nil
                ))
  }
  return res
}
































