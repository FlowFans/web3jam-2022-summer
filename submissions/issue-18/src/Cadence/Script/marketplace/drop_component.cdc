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
  var intermediate: {String : SoulMadeMarketplace.SoulMadeComponentSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeComponentSales(address: address)

  for sale in salesData {
    var category = sale.componentDetail.category
    var name = sale.componentDetail.name
    var categoryAndName = category.concat(name)

    if intermediate[categoryAndName] == nil {
      intermediate[categoryAndName] = sale
    }
  }

  var res: [SoulMadeMarketplace.SoulMadeSaleData] = []
  for componentSale in intermediate.values{
    res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: componentSale.id,
                price: componentSale.price,
                nftType: "SoulMadeComponent",
                mainDetail: nil,
                componentDetail: componentSale.componentDetail
                ))
  }
  return res
}



