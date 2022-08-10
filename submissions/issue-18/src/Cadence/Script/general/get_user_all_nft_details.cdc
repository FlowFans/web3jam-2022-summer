import NonFungibleToken from 0x631e88ae7f1d7c20
import SoulMadeMain from 0xb4187e54e0ed55a8
import SoulMadeComponent from 0xb4187e54e0ed55a8
import SoulMadePack from 0xb4187e54e0ed55a8
import SoulMade from 0xb4187e54e0ed55a8

pub struct SoulMadeNftDetail {
    pub let id: UInt64
    pub let nftType: String
    pub let series: String
    pub let mainDetail: SoulMadeMain.MainDetail?
    pub let componentDetail: SoulMadeComponent.ComponentDetail?

    init(id: UInt64,
            nftType: String,
            series: String,
            mainDetail: SoulMadeMain.MainDetail?,
            componentDetail: SoulMadeComponent.ComponentDetail?){
                self.id = id
                self.nftType = nftType
                self.series = series
                self.mainDetail = mainDetail
                self.componentDetail = componentDetail
    }
}

pub fun main(address: Address): [SoulMadeNftDetail] {

  var res: [SoulMadeNftDetail] = []

  if(getAccount(address).getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).check()){
    var mainNftIds = SoulMade.getMainCollectionIds(address: address)
    for mainNftId in mainNftIds{
      
      var mainDetail = SoulMade.getMainDetail(address: address, mainNftId: mainNftId)!
      var detail = SoulMadeNftDetail(id: mainNftId, nftType: "SoulMadeMain", series: mainDetail.series, mainDetail: mainDetail, componentDetail: nil)
      res.append(detail)
    }

    var componentNftIds = SoulMade.getComponentCollectionIds(address: address)
    for componentNftId in componentNftIds{
      var componentDetail = SoulMade.getComponentDetail(address: address, componentNftId: componentNftId)!
      var detail = SoulMadeNftDetail(id: componentNftId, nftType: "SoulMadeComponent", series: componentDetail.series, mainDetail: nil, componentDetail: componentDetail)
      res.append(detail)
    }

  }

  return res
}