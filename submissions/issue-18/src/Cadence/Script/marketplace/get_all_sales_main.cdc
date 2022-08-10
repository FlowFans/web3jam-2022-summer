import SoulMadeMain from 0xb4187e54e0ed55a8
import SoulMadeComponent from 0xb4187e54e0ed55a8
import SoulMadeMarketplace from 0xb4187e54e0ed55a8

pub fun main(series: String) : [SoulMadeMarketplace.SoulMadeMainSaleData] {
  // let address: Address = 0xf8d6e0586b0a20c7
  let address: Address = 0xb4187e54e0ed55a8
  
  // category: {name: saledata}
  var intermediate: {String : SoulMadeMarketplace.SoulMadeMainSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeMainSales(address: address)

  return salesData
}
 