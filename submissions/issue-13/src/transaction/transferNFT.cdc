import NonFungibleToken from 0xNonFungibleToken
import TicketNFT from 0xProfile


transaction(recipient: Address, withdrawID: UInt64) {

    let transferToken: @NonFungibleToken.NFT

    let collectionRef: &TicketNFT.Collection

    prepare(acct: AuthAccount) {
        self.collectionRef=acct.borrow<&TicketNFT.Collection>(from: /storage/TicketNFTCollection)??panic("Cannot borrow a reference to the recipient's TicketNFT collection")
        self.transferToken <-self.collectionRef.withdraw(withdrawID: withdrawID)
    }

    execute {


        let recipient = getAccount(recipient)

        let receiverRef = recipient.getCapability(/public/TicketNFTCollection).borrow<&{TicketNFT.TicketNFTCollectionPublic}>() ??panic("Cannot borrow a reference to the recipient's TicketNFTCollection ")
        //let receiverRef = recipient.getCapability(/public/TicketNFTCollection).borrow<&{TicketNFT.TicketNFTCollectionPublic}>()!

        receiverRef.deposit(token: <-self.transferToken)
    }
}