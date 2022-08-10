
import TicketNFT from 0xTicketNFT

// import TicketNFT from 0xd1183642a19fd336

pub fun main(account: Address): [UInt64] {

    let account = getAccount(account)

    let collectionRef = account.getCapability(TicketNFT.CollectionPublicPath)
                            .borrow<&{TicketNFT.TicketNFTCollectionPublic}>()!
    return collectionRef.getIDs()
}