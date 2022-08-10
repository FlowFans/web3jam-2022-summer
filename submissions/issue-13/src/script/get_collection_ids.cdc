import TicketNFT  from 0xProfile
pub fun main(account: Address): [UInt64] {

    let account = getAccount(account)

    let collectionRef = account.getCapability(/public/TicketNFTCollection)
                            .borrow<&{TicketNFT.TicketNFTCollectionPublic}>() ?? panic("message")

    return collectionRef.getIDs()
}