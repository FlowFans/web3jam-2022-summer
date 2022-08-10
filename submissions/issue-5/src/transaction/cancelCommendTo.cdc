import PunstersNFT from "../contracts/Punsters.cdc"
import NonFungibleToken from "../contracts/NonFungibleToken.cdc"

transaction (ownerAddr: Address, duanjiID: UInt64) {
    prepare (acct: AuthAccount) {
        if let punsterRef = acct.borrow<&PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath) {
            punsterRef.cancelCommendToDuanji(addr: ownerAddr, duanjiID: duanjiID);
        }
    }

    execute {

    }
}