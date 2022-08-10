import StarRealm from "../../contracts/StarRealm.cdc"
import PunstersNFT from "../../contracts/Punsters.cdc"
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"

pub resource aaa: NonFungibleToken.INFT {
    pub let id: UInt64

    init() {
        self.id = 100;
    }

    pub fun getID(): UInt64 {
        return self.id;
    }
}

pub fun main(): {String: AnyStruct} {

    // let aaaRes <- create aaa();
    // let aaaINFT <- aaaRes as! @AnyResource{NonFungibleToken.INFT};
    // let aaaBack <- aaaINFT as! @aaa;
    // let r: [UInt64] = [];
    // r.append(aaaBack.getID());
    // destroy aaaBack;
    // return r;

    let punster <- PunstersNFT.registerPunster(addr: 0x01, description: "test punster", ipfsURL: "test url");

    punster.publishDuanji(description: "Hello", ipfsURL: "Nika");

    let duanji <- punster.withdraw(withdrawID: 1);
    let duanjiNFT <- duanji as! @AnyResource{NonFungibleToken.INFT};
    punster.docking(nft: <- duanjiNFT);
    let ids = punster.getIDs();

    let starportOne <- StarRealm.createStarPort();

    starportOne.docking(nft: <- punster);

    let starportTwo <- StarRealm.createStarPort();

    // Attention: `sailing` returns an option!!!
    let sailingPunster <- starportOne.sailing();

    var psView: {String: AnyStruct} = {}
    // Attention: `sailingPunster` is an option!!!
    if let backPunster <- sailingPunster as! @PunstersNFT.Collection? {
        psView = backPunster.metadata;
        starportTwo.docking(nft: <- backPunster);
    } else {
        destroy sailingPunster;
    }
    
    // `as`: the result type is just the type of the right one
    // `as?`: the result type is the type of right one + ?, which is used for left type is different from the right one
    // `as!`: the result type is the forced casting of `as?`, which is also used for left type is different from the right one 
    
    // let psRef: &PunstersNFT.Collection = (&sailingPunster as auth &AnyResource{NonFungibleToken.INFT}?)! as! &PunstersNFT.Collection;
    // let psView = psRef.metadata;
    // starportTwo.docking(nft: <- sailingPunster!);

    // destroy punster;
    // destroy sailingPunster;
    destroy starportOne;
    destroy starportTwo;

    return psView;
    // return ids;
}
