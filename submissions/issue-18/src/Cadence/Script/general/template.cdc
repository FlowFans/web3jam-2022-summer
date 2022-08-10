// testnet
import NFTStorefront from 0x94b06cfca1d8a476
import NonFungibleToken from 0x631e88ae7f1d7c20
import SoulMadeMain from 0x76b2527585e45db4
import SoulMadeComponent from 0x76b2527585e45db4
import SoulMadePack from 0x76b2527585e45db4
import SoulMade from 0x76b2527585e45db4


// Get SoulMadeMain Ids:

pub fun main(address: Address): [UInt64] {

  if(getAccount(address).getCapability<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).check()){
    return mainNftIds = SoulMade.getMainCollectionIds(address: address)
  } else {
    return []
  }
}

// Get SoulMadeMain Details:

pub fun main(address: Address): [UInt64] {

  if(getAccount(address).getCapability<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).check()){
    return mainNftIds = SoulMade.getMainCollectionIds(address: address)
  } else {
    return []
  }
}





pub struct SoulMadeNftDetail {
    pub let nftId: UInt64
    pub let nftType: String
    pub let mainDetail: SoulMadeMain.MainDetail?
    pub let componentDetail: SoulMadeComponent.ComponentDetail?
    pub let packDetail: SoulMadePack.PackDetail?

    init(nftId: UInt64,
            nftType: String,
            mainDetail: SoulMadeMain.MainDetail?,
            componentDetail: SoulMadeComponent.ComponentDetail?,
            packDetail: SoulMadePack.PackDetail?){
                self.nftId = nftId
                self.nftType = nftType
                self.mainDetail = mainDetail
                self.componentDetail = componentDetail
                self.packDetail = packDetail
    }
}

pub fun main(address: Address): [SoulMadeNftDetail] {

  var res: [SoulMadeNftDetail] = []

  if(getAccount(address).getCapability<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).check()){
    var mainNftIds = SoulMade.getMainCollectionIds(address: address)
    for mainNftId in mainNftIds{
      
      var mainDetail = SoulMade.getMainDetail(address: address, mainNftId: mainNftId)
      var detail = SoulMadeNftDetail(nftId: mainNftId, nftType: "SoulMadeMain", mainDetail: mainDetail, componentDetail: nil , packDetail: nil)
      res.append(detail)
    }

    var componentNftIds = SoulMade.getComponentCollectionIds(address: address)
    for componentNftId in componentNftIds{
      var componentDetail = SoulMade.getComponentDetail(address: address, componentNftId: componentNftId)
      var detail = SoulMadeNftDetail(nftId: componentNftId, nftType: "SoulMadeComponent", mainDetail: nil, componentDetail: componentDetail , packDetail: nil)
      res.append(detail)
    }

    var packNftIds = SoulMade.getPackCollectionIds(address: address)
    for packNftId in packNftIds{
      var packDetail = SoulMade.getPackDetail(address: address, packNftId: packNftId)
      var detail = SoulMadeNftDetail(nftId: packNftId, nftType: "SoulMadePack", mainDetail: nil, componentDetail: nil , packDetail: packDetail)
      res.append(detail)
    }
  }

  return res
}

