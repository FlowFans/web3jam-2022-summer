import PunstersNFT from "../../contracts/Punsters.cdc"
import StarRealm from "../../contracts/StarRealm.cdc"

transaction (to: Address, id: UInt64) {
    prepare (acct: AuthAccount) {
        if let punsterRef = acct.borrow<&PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath) {
            let duanjiRes <- punsterRef.withdraw(withdrawID: id);

            if let toRef = PunstersNFT.getIPunsterFromAddress(addr: to) {
                toRef.deposit(token: <- duanjiRes);
            } else {
                panic("`to` address does not exist!");
            }
        }
    }

    execute {

    }
}