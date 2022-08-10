import PunstersNFT from "../contracts/Punsters.cdc"
import NonFungibleToken from "../contracts/NonFungibleToken.cdc"

transaction (followingAddr: Address) {
    prepare (acct: AuthAccount) {
        if let punsterRef = acct.borrow<&PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath) {
            punsterRef.cancelFollow(addr: followingAddr);
        }
    }

    execute {

    }
}
