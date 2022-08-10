import PunstersNFT from 0x1a478a7149935b63
import Locker from 0x1a478a7149935b63

transaction(hashValue: String, owner: String, duanjiID: UInt64) {

    prepare(acct: AuthAccount) {
        let punsterRef = acct.borrow<&PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath)!;

        let duanji <- punsterRef.withdraw(withdrawID: duanjiID);

        Locker.sendCrossChainNFT(transferToken: <- duanji, 
                                signerAddress: acct.address, 
                                id: duanjiID, 
                                owner: owner, 
                                hashValue: hashValue);
    }

    execute {
        
    }
}
