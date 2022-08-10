import Cloud from "../contracts/Cloud.cdc"

pub struct DropStats {
    pub let dropBalance: UFix64
    pub let claimed: {Address: Cloud.ClaimRecord}
    pub let tokenSymbol: String

    init(
        dropBalance: UFix64, 
        claimed: {Address: Cloud.ClaimRecord},
        tokenSymbol: String
    ) {
        self.dropBalance = dropBalance
        self.claimed = claimed
        self.tokenSymbol = tokenSymbol
    }
}

pub fun main(dropID: UInt64, host: Address): DropStats? {
    let dropCollection =
        getAccount(host)
        .getCapability(Cloud.DropCollectionPublicPath)
        .borrow<&Cloud.DropCollection{Cloud.IDropCollectionPublic}>()
        ?? panic("Could not borrow IDropCollectionPublic from address")

    let drop = dropCollection.borrowPublicDropRef(dropID: dropID)
        ?? panic("Could not borrow drop")

    let claimedRecords = drop.getClaimedRecords()
    let dropBalance = drop.getDropBalance()
    let tokenSymbol = drop.tokenInfo.symbol
    
    return DropStats(dropBalance: dropBalance, claimed: claimedRecords, tokenSymbol: tokenSymbol)
}