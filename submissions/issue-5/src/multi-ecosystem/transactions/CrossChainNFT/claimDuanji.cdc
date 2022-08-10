import PunstersNFT from 0x1a478a7149935b63
import Locker from 0x1a478a7149935b63

transaction(id: UInt64, answer: String) {

    prepare(acct: AuthAccount) {
        Locker.claimNFT(id: id, answer: answer);
    }

    execute {
        
    }
}