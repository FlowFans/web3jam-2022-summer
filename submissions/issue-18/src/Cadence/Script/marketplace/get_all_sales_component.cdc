import SoulMadeMain from 0xb4187e54e0ed55a8
import SoulMadeComponent from 0xb4187e54e0ed55a8
import SoulMadeMarketplace from 0xb4187e54e0ed55a8

pub fun main(series: String) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // let address: Address = 0xf8d6e0586b0a20c7
  let address: Address = 0xb4187e54e0ed55a8
  
  // category: {name: saledata}
  var intermediate: {String : SoulMadeMarketplace.SoulMadeComponentSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeComponentSales(address: address)


  var res: [SoulMadeMarketplace.SoulMadeSaleData] = []
  for sale in salesData {
    res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: sale.id,
                price: sale.price,
                nftType: "SoulMadeComponent",
                mainDetail: nil,
                componentDetail: sale.componentDetail
                ))
  }
  return res
}