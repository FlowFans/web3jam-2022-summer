import PunstersNFT from "../contracts/Punsters.cdc"

pub fun main(addr: Address): [Address]? {
    if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
        return punsterRef.getFollowings();
    }
    
    return nil;
}