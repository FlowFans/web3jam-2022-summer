import Univoice from 0x231727804b3acfe3
import NonFungibleToken from 0x631e88ae7f1d7c20

transaction(recipient: Address, id: UInt64) {
    let collectionRef: &Univoice.Collection
    prepare(signer: AuthAccount) {
        self.collectionRef = signer.borrow<&Univoice.Collection>(from: Univoice.CollectionStoragePath) ?? panic("no resource")
    }

    execute {
        let nft <- self.collectionRef.withdraw(withdrawID: id)
        let acct = getAccount(recipient)
        let receiverReference = acct.getCapability<&{Univoice.UnivoiceCollectionPublic}>(Univoice.CollectionPublicPath)
                                .borrow() ?? panic("no resource")
        receiverReference.deposit(token:<-nft)
    }
}