import NonFungibleToken from 0xNonFungibleToken
import TicketNFT from 0xProfile
import MetadataViews from 0xNonFungibleToken

// This transaction sets up an account to use Top Shot
// by storing an empty moment collection and creating
// a public capability for it

transaction {

    prepare(acct: AuthAccount) {

        // First, check to see if a moment collection already exists
        if acct.borrow<&TicketNFT.Collection>(from: /storage/TicketNFTCollection) == nil {

            // create a new TopShot Collection
            let collection <- TicketNFT.createEmptyCollection() as! @TicketNFT.Collection

            // Put the new Collection in storage
            acct.save(<-collection, to: /storage/TicketNFTCollection)

            // create a public capability for the collection
            acct.link<&{NonFungibleToken.CollectionPublic, TicketNFT.TicketNFTCollectionPublic, MetadataViews.ResolverCollection}>(/public/TicketNFTCollection, target: /storage/TicketNFTCollection)
        }
    }
}