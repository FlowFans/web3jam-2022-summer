// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"

import SoulMadeMain from 0xa25fe4df1a3d7b77
import SoulMadeComponent from 0xa25fe4df1a3d7b77
import SoulMadeMarketplace from 0xa25fe4df1a3d7b77

pub fun main(address:Address) :  Int {
    let marketRef = getAccount(address)
                      .getCapability<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath).borrow() ?? panic("Could not borrow the marketplace reference")
    
    return marketRef.getSoulMadeComponentIDs().length

}