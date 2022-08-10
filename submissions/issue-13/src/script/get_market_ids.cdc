

 import Marketplace from 0xMarketplace

 import TicketNFT from 0xTicketNFT


// import TicketNFT from 0xd1183642a19fd336
// import Marketplace from 0x1dd1316850f649ea


pub fun main():[UInt64]{
    let keys=TicketNFT.middleOwner.keys
    var result :[UInt64]=[]
    var i=0
    while i<keys.length{
        if TicketNFT.middleOwner[keys[i]]==true{
            result.appendAll(getMarketplaceIds(account:keys[i]))
        }
        i=i+1
    }
    return result
}
pub fun getMarketplaceIds(account: Address): [UInt64] {

     let collectionRef = getAccount(account).getCapability(Marketplace.MarketplacePublicPath)
        .borrow<&{Marketplace.SalePublic}>()
        ?? panic("Could not get public sale reference")
    return collectionRef.getIDs()
}