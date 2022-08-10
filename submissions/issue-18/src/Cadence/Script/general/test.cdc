// testnet
import NFTStorefront from 0x94b06cfca1d8a476
import NonFungibleToken from 0x631e88ae7f1d7c20
import SoulMadeMain from 0x76b2527585e45db4
import SoulMadeComponent from 0x76b2527585e45db4
import SoulMadePack from 0x76b2527585e45db4
import SoulMade from 0x76b2527585e45db4


pub fun main(address: Address): [UInt64] {


  // var packNftIds = SoulMade.getPackCollectionIds(address: address)
  // for packNftId in packNftIds{
  //   var packDetail = SoulMade.getPackDetail(address: address, packNftId: packNftId)
  //   var detail = SoulMadeNftDetail(nftId: packNftId, nftType: "SoulMadePack", mainDetail: nil, componentDetail: nil , packDetail: packDetail)
  //   res.append(detail)
  // }

  return SoulMade.getPackCollectionIds(address: address)
}

