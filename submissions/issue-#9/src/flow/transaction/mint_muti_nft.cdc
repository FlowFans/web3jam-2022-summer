import PioneerNFT from "../cadence/PioneerNFT.cdc"
import NonFungibleToken from "../cadence/NonFungibleToken.cdc"



transaction(recipient: Address, 
                name: String,
                description: String,
                url: String,
                creator: Address,
                createTime: UFix64,
                externalUrl: String,
                properties: {String:String}?,quantity :UInt64){

    let minter :&PioneerNFTs.NFTMinter
    let minterNFTID: UInt64
    let recipientCollectionRef: &{NonFungibleToken.CollectionPublic}
    prepare(account: AuthAccount){

        self.minterNFTID=PioneerNFT.totalSupply
        self.minter = account.borrow<&PioneerNFTs.NFTMinter>(from: PioneerNFT.MinterStoragePath)
            ?? panic("Account does not store an object at the specified path")

        if account.borrow<&PioneerNFTs.Collection>(from: PioneerNFT.CollectionStoragePath) == nil {

            // create a new TopShot Collection
            let collection <- PioneerNFT.createEmptyCollection() as! @PioneerNFT.Collection

            // Put the new Collection in storage
            account.save(<-collection, to: /storage/PioneerNFTCollection)

            // create a public capability for the collection
            account.link<&{NonFungibleToken.CollectionPublic, PioneerNFT.PioneerNFTCollectionPublic}>(PioneerNFT.CollectionPublicPath, target: PioneerNFT.CollectionStoragePath)
        }

        self.recipientCollectionRef=getAccount(recipient)
            .getCapability(PioneerNFT.CollectionPublicPath)
            .borrow<&{NonFungibleToken.CollectionPublic}>()
            ?? panic("Could not get receiver reference to the NFT Collection")

    }
    execute{


        let mutiNFT <-self.minter.batchMintPioneerNFT(
                    name:name, 
                    description:description,
                    url:url,
                    creator:creator,
                    createTime: createTime,
                    externalUrl: externalUrl,
                    properties: properties,
                    quantity:quantity
        )

          let receiverRef =  getAccount(recipient).getCapability(PioneerNFT.CollectionPublicPath).borrow<&{PioneerNFT.PioneerNFTCollectionPublic}>()
            ?? panic("Cannot borrow a reference to the recipient's moment collection")
           receiverRef.batchDeposit(tokens:<-mutiNFT)

    }

     post {
            self.recipientCollectionRef.getIDs().contains(self.minterNFTID): "The next NFT ID should have been minted and delivered"
            PioneerNFT.totalSupply == self.minterNFTID + quantity: "The total supply should have been increased by quantity"
     }
}