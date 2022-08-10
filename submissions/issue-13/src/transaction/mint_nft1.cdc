import TicketNFT from 0xProfile
import NonFungibleToken from 0xNonFungibleToken
import MetadataViews from 0xNonFungibleToken
import FungibleToken from 0xFungibleToken


transaction(
    recipient: Address,
    name: String,
    description: String,
    thumbnail: String,
    creator:Address,
    externalUrl:String,
    //properties: {String:String},
    //cuts: [UFix64],
    //royaltyDescriptions: [String],
    //royaltyBeneficiaries: [Address]
) {

    /// local variable for storing the minter reference
    let minter: &TicketNFT.NFTMinter

    /// Reference to the receiver's collection
    let recipientCollectionRef: &{NonFungibleToken.CollectionPublic}

    /// Previous NFT ID before the transaction executes
    let mintingIDBefore: UInt64

    prepare(signer: AuthAccount) {
        self.mintingIDBefore = TicketNFT.totalSupply

        // borrow a reference to the NFTMinter resource in storage
        self.minter = signer.borrow<&TicketNFT.NFTMinter>(from: TicketNFT.MinterStoragePath)
            ?? panic("Account does not store an object at the specified path")

        // Borrow the recipient's public NFT collection reference
        self.recipientCollectionRef = getAccount(recipient)
            .getCapability(TicketNFT.CollectionPublicPath)
            .borrow<&{NonFungibleToken.CollectionPublic}>()
            ?? panic("Could not get receiver reference to the NFT Collection")
    }

    // pre {
    //     cuts.length == royaltyDescriptions.length && cuts.length == royaltyBeneficiaries.length: "Array length should be equal for royalty related details"
    // }

    execute {
        var count = 0
        var cuts=[0.1]
        var royaltyDescriptions=["ssss"]
        var royaltyBeneficiaries=[0xf8d6e0586b0a20c7]
        var royalties: [MetadataViews.Royalty] = []
        var properties ={"":""}
        while royaltyBeneficiaries.length > count {
            let beneficiary = royaltyBeneficiaries[count]
            let beneficiaryCapability = getAccount(Address(beneficiary))
            .getCapability<&{FungibleToken.Receiver}>(MetadataViews.getRoyaltyReceiverPublicPath())

            // Make sure the royalty capability is valid before minting the NFT
            if !beneficiaryCapability.check() { panic("Beneficiary capability is not valid!") }

            royalties.append(
                MetadataViews.Royalty(
                    receiver: (beneficiaryCapability),
                    cut: cuts[count],
                    description: royaltyDescriptions[count]
                )
            )
            count = count + 1
        }
        let ss=TicketNFT.Ruple(name:"",key:"")



      let TicketNFT:@TicketNFT.NFT<-self.minter.mintNFT(
                        name: name,
                        description: description,
                        url:thumbnail,
                        royalties: royalties,
                        creator:creator,
                        externalUrl: externalUrl,
                        collection:ss,
                        properties:properties,
        )

        //recipientCollectionRef.deposit(token: <-TicketNFT)
        let receiverRef =  getAccount(recipient).getCapability(/public/TicketNFTCollection).borrow<&{TicketNFT.TicketNFTCollectionPublic}>()
            ?? panic("Cannot borrow a reference to the recipient's moment collection")
           receiverRef.deposit(token: <-TicketNFT)
    }

    post {
        self.recipientCollectionRef.getIDs().contains(self.mintingIDBefore): "The next NFT ID should have been minted and delivered"
        TicketNFT.totalSupply == self.mintingIDBefore + 1: "The total supply should have been increased by 1"
    }
}




