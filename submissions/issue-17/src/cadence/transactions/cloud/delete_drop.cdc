import Cloud from "../contracts/Cloud.cdc"
import FungibleToken from "../contracts/core/FungibleToken.cdc"

transaction(
    dropID: UInt64,
    tokenIssuer: Address,
    tokenReceiverPath: String
) {

    let dropCollection: &Cloud.DropCollection
    let receiver: &{FungibleToken.Receiver}

    prepare(acct: AuthAccount) {
        self.dropCollection = acct.borrow<&Cloud.DropCollection>(from: Cloud.DropCollectionStoragePath)
            ?? panic("Could not borrow dropCollection")

        let receiverPath = PublicPath(identifier: tokenReceiverPath)!
        self.receiver = acct.getCapability(receiverPath).borrow<&{FungibleToken.Receiver}>()
            ?? panic("Could not borrow Receiver from signer")
    }

    execute {
       self.dropCollection.deleteDrop(dropID: dropID, receiver: self.receiver) 
    }
}