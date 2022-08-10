import Marketplace  from 0xProfile
pub fun main(account: Address): [UInt64] {


     let collectionRef = getAccount(account).getCapability(/public/TicketNFTSaleCollection)
        .borrow<&{Marketplace.SalePublic}>()
        ?? panic("Could not get public sale reference")
    return collectionRef.getIDs()
}