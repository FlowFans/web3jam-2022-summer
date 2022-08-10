import PunstersNFT from "../contracts/Punsters.cdc"

pub fun main(addr: Address): [PunstersNFT.DuanjiView] {
    if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
        return punsterRef.getFollowingUpdates();
    }
    
    return [];
}
