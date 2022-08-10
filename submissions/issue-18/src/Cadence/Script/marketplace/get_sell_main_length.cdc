// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"

import SoulMadeMarketplace from 0xb4187e54e0ed55a8

pub fun main(address:Address) : Int {
    let marketRef = getAccount(address)
                      .getCapability<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath).borrow() ?? panic("Could not borrow the marketplace reference")
    
    return marketRef.getSoulMadeMainIDs().length
}