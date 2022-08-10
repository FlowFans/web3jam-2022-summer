import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import SoulMadeMarketplace from 0x76b2527585e45db4


pub fun main(series: String) : {String: SoulMadeMarketplace.SoulMadeSaleData} {
  // let address: Address = 0xf8d6e0586b0a20c7
  let address: Address = 0x76b2527585e45db4
  
  // category: {name: saledata}
  var intermediate: {String : SoulMadeMarketplace.SoulMadeSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeSales(address: address)

  for sale in salesData {
    if sale.nftType == "SoulMadeMain" && sale.mainDetail!.series == series{
      // main must have a name, but this is for drop page
      var category = sale.mainDetail!.componentDetails[0].category
      var name = sale.mainDetail!.name
      var categoryAndName = category.concat(name)

      if intermediate[categoryAndName] == nil {
        intermediate[categoryAndName] = sale
      }
    } 
    // else if sale.nftType == "SoulMadeComponent" && sale.componentDetail!.series == series{
    //   var category = sale.componentDetail!.category
    //   var name = sale.componentDetail!.name
    //   var categoryAndName = category.concat(name)

    //   if intermediate[categoryAndName] == nil {
    //     intermediate[categoryAndName] = sale
    //   }
    // }
  }
  // todo: we should also withdraw information res
  return intermediate
}






























