import PunstersNFT from "../../contracts/Punsters.cdc"

pub fun main(): [Fix64]{
    let rst: [Fix64] = [];

    var i = 0;
    while i < 200 {
        let adBox = PunstersNFT.AdBox();

        let dv = PunstersNFT.DuanjiView(id: 0, 
                                        owner: 0x01, 
                                        description: "heihei", 
                                        ipfsUrl: "ipfs", 
                                        fidx: 0, 
                                        commends: [],
                                        true);

        if (adBox.publishAD(dv: dv, fi: 10 + UInt32(i * 10))) {
            rst.append(adBox.getRemainingTime())
        }

        i = i + 1;
    }

    return rst;
}