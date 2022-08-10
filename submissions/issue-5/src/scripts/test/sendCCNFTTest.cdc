import PunstersNFT from "../../contracts/Punsters.cdc"
import MetadataViews from "../../contracts/MetadataViews.cdc"
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import StarRealm from "../../contracts/StarRealm.cdc"

pub fun main(): UInt64{
    let punster <- PunstersNFT.registerPunster(addr: 0x01, 
                                                description: "Punster test ", 
                                                ipfsURL: "Test IPFS");

    let any <- punster as! @AnyResource;

    let NFTResolver <- any as! @AnyResource{MetadataViews.Resolver};
    let tokenURL: String = (NFTResolver.resolveView(Type<MetadataViews.Display>())! as! MetadataViews.Display).thumbnail.uri();

    let NonToken <- NFTResolver as! @AnyResource{NonFungibleToken.INFT};
    let id: UInt64 = NonToken.id

    let lockerRef <- StarRealm.createStarPort();

    // Deposit the NFT in the locker collection
    let v <- lockerRef.docking(nft: <- NonToken);

    if v != nil {
        panic("NFT docking failed, the `id` exists!")
    } else {
        destroy v;
    }

    // let punster2 <- PunstersNFT.registerPunster(addr: 0x01, 
    //                                             description: "Punster test 2", 
    //                                             ipfsURL: "Test 2 IPFS");

    // let vv <- lockerRef.docking(nft: <- punster2);

    // if vv != nil {
    //     panic("The second time, NFT docking failed, the `id` exists!")
    // } else {
    //     destroy vv;
    // }

    destroy lockerRef;
    return id;
}