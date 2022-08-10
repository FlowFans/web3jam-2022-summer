import SoulMadeMain from 0xb4187e54e0ed55a8
import SoulMadeComponent from 0xb4187e54e0ed55a8
import SoulMadeMarketplace from 0xb4187e54e0ed55a8

pub fun main(series: String) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // let address: Address = 0xf8d6e0586b0a20c7
  let address: Address = 0xb4187e54e0ed55a8
  
  // category: {name: saledata}
  var intermediate: {String : SoulMadeMarketplace.SoulMadeMainSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeMainSales(address: address)
  var res: [SoulMadeMarketplace.SoulMadeSaleData] = []
  for sale in salesData {
    var se = sale.mainDetail!.componentDetails[0].series
    if series == se {
      res.append(SoulMadeMarketplace.SoulMadeSaleData(
                  id: sale.id,
                  price: sale.price,
                  nftType: "SoulMadeMain",
                  mainDetail: sale.mainDetail,
                  componentDetail: nil
                  ))
    }
  }
  return res
}
 