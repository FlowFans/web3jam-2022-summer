import PunstersNFT from "../contracts/Punsters.cdc"

pub fun main(addr: Address): [PunstersNFT.DuanjiView]? {
    
    var duanjiView: [PunstersNFT.DuanjiView] = []

    if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
        duanjiView.concat(punsterRef.getAllDuanjiView());
        duanjiView = duanjiView.concat(punsterRef.getAllDuanjiView());
        return duanjiView;
    }

    // let pubAcct = getAccount(addr);
    // let oIPunster = pubAcct.getCapability<&{PunstersNFT.IPunsterPublic}>(PunstersNFT.IPunsterPublicPath);
    // duanjiView.concat(oIPunster.borrow()!.getAllDuanjiView());

    return nil;
}