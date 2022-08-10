export const MINT_NFT = `
import PioneerNFTs from 0xPioneerNFT
import NonFungibleToken from 0xNonFungibleToken


transaction(recipient: Address, 
    name: String,
    description: String,
    url: String,
    creator: Address,
    createTime: UFix64,
    externalUrl: String,
    properties: {String:String}?){

let minter :&PioneerNFTs.NFTMinter
let minterNFTID: UInt64
let recipientCollectionRef: &{NonFungibleToken.CollectionPublic}
prepare(account: AuthAccount){

self.minterNFTID=PioneerNFTs.totalSupply

if account.borrow<&PioneerNFTs.NFTMinter>(from: PioneerNFTs.MinterStoragePath)==nil{
let collection <- PioneerNFTs.createEmptyNFTMinter() as! @PioneerNFTs.NFTMinter
 // Put the new Collection in storage
account.save(<-collection, to: PioneerNFTs.MinterStoragePath)

}
self.minter = account.borrow<&PioneerNFTs.NFTMinter>(from: PioneerNFTs.MinterStoragePath)
?? panic("Account does not store an object at the specified path")

if account.borrow<&PioneerNFTs.Collection>(from: PioneerNFTs.CollectionStoragePath) == nil {

// create a new TopShot Collection
let collection <- PioneerNFTs.createEmptyCollection() as! @PioneerNFTs.Collection

// Put the new Collection in storage
account.save(<-collection, to: /storage/PioneerNFTsCollection)

// create a public capability for the collection
account.link<&{NonFungibleToken.CollectionPublic, PioneerNFTs.PioneerNFTCollectionPublic}>(PioneerNFTs.CollectionPublicPath, target: PioneerNFTs.CollectionStoragePath)
}

self.recipientCollectionRef=getAccount(recipient)
.getCapability(PioneerNFTs.CollectionPublicPath)
.borrow<&{NonFungibleToken.CollectionPublic}>()
?? panic("Could not get receiver reference to the NFT Collection")

}
execute{
let NFT <-self.minter.mintNFT(
        name:name, 
        description:description,
        url:url,
        creator:creator,
        createTime: createTime,
        externalUrl: externalUrl,
        properties: properties
)

let receiverRef =  getAccount(recipient).getCapability(PioneerNFTs.CollectionPublicPath).borrow<&{PioneerNFTs.PioneerNFTCollectionPublic}>()
?? panic("Cannot borrow a reference to the recipient's moment collection")
receiverRef.deposit(token: <-NFT)

}

post {
// self.recipientCollectionRef.getIDs().contains(self.minterNFTID): "The next NFT ID should have been minted and delivered"
PioneerNFTs.totalSupply == self.minterNFTID + 1: "The total supply should have been increased by 1"
}
}
`