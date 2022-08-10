import NonFungibleToken from 0xNonFungibleToken
// import MetadataViews from 0x631e88ae7f1d7c20
import TicketNFT from 0xTicketNFT

transaction() {
    prepare(account: AuthAccount) {

        if account.borrow<&TicketNFT.Collection>(from: TicketNFT.CollectionStoragePath) == nil {

                    // create a new TopShot Collection
                    let collection <- TicketNFT.createEmptyCollection() as! @TicketNFT.Collection

                    // Put the new Collection in storage
                    account.save(<-collection, to: TicketNFT.CollectionStoragePath)

                    // create a public capability for the collection
                    account.link<&{NonFungibleToken.CollectionPublic, TicketNFT.TicketNFTCollectionPublic}>(TicketNFT.CollectionPublicPath, target: TicketNFT.CollectionStoragePath)
                }
        }
        execute {

        }
}