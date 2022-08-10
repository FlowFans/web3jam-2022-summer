import Cloud from "../contracts/Cloud.cdc"
import FungibleToken from "../contracts/core/FungibleToken.cdc"

transaction(
    dropID: UInt64,
    tokenIssuer: Address,
    tokenReceiverPath: String
) {

    let drop: &Cloud.Drop
    let receiver: &{FungibleToken.Receiver}

    prepare(acct: AuthAccount) {
        let dropCollection = acct.borrow<&Cloud.DropCollection>(from: Cloud.DropCollectionStoragePath)
            ?? panic("Could not borrow dropCollection")
        self.drop = dropCollection.borrowDropRef(dropID: dropID)!

        let receiverPath = PublicPath(identifier: tokenReceiverPath)!
        self.receiver = acct.getCapability(receiverPath).borrow<&{FungibleToken.Receiver}>()
            ?? panic("Could not borrow Receiver from signer")
    }

    execute {
        self.drop.end(receiver: self.receiver)
    }
}