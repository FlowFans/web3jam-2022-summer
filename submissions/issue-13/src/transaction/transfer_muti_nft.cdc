import NonFungibleToken from 0xNonFungibleToken
import TicketNFT from 0xProfile

// This transaction transfers a number of moments to a recipient

// Parameters
//
// recipientAddress: the Flow address who will receive the NFTs
// momentIDs: an array of moment IDs of NFTs that recipient will receive

transaction(recipientAddress: Address, ticketNFTIDs: [UInt64]) {

    let transferTokens: @NonFungibleToken.Collection

    prepare(acct: AuthAccount) {

        self.transferTokens <- acct.borrow<&TicketNFT.Collection>(from: /storage/TicketNFTCollection)!.batchWithdraw(ids: ticketNFTIDs)
    }

    execute {

        // get the recipient's public account object
        let recipient = getAccount(recipientAddress)

        // get the Collection reference for the receiver
        let receiverRef = recipient.getCapability(/public/TicketNFTCollection).borrow<&{TicketNFT.TicketNFTCollectionPublic}>()
            ?? panic("Could not borrow a reference to the recipients moment receiver")

        // deposit the NFT in the receivers collection
        receiverRef.batchDeposit(tokens: <-self.transferTokens)
    }
}