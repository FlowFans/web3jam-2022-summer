import Locker from 0x1a478a7149935b63;

transaction(id: UInt64, 
            answer: String
){
    let signer: AuthAccount;

    prepare(signer: AuthAccount){
        self.signer = signer;
    }

    execute {
        Locker.claimNFT(id: id, answer: answer);
    }
}