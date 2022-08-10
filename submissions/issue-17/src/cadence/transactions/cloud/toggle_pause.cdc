import Cloud from "../contracts/Cloud.cdc"

transaction(dropID: UInt64) {
    let drop: &Cloud.Drop

    prepare(acct: AuthAccount) {
        let dropCollection = acct.borrow<&Cloud.DropCollection>(from: Cloud.DropCollectionStoragePath)
            ?? panic("Could not borrow dropCollection")

        self.drop = dropCollection.borrowDropRef(dropID: dropID)!
    }

    execute {
        self.drop.togglePause()
    }
}