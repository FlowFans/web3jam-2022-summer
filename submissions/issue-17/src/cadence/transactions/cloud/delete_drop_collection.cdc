import Cloud from "../contracts/Cloud.cdc"
import FungibleToken from "../contracts/core/FungibleToken.cdc"

transaction() {

    let collection: @Cloud.DropCollection
    prepare(acct: AuthAccount) {
        acct.unlink(Cloud.DropCollectionPublicPath)
        self.collection <- acct.load<@Cloud.DropCollection>(from: Cloud.DropCollectionStoragePath)
            ?? panic("Cloud not load dropCollection")

        let dropRefs = self.collection.getAllDrops()
        for dropID in dropRefs.keys {
            let dropRef = dropRefs[dropID]!

            let receiver = acct.getCapability(dropRef.tokenInfo.receiverPath).borrow<&{FungibleToken.Receiver}>()
                ?? panic("Could not borrow Receiver from signer")
            self.collection.deleteDrop(dropID: dropID, receiver: receiver)
        }
    }

    execute {
        destroy self.collection
    }
}