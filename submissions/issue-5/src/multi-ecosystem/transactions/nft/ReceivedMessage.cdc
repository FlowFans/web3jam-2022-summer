import MessageProtocol from 0x1a478a7149935b63
import ReceivedMessageContract from 0x1a478a7149935b63;
import Locker from 0x1a478a7149935b63;

transaction(id: UInt128, 
            fromChain: String, 
            toChain: String,
            sqosString: String, 
            nftID: UInt64,
            receiver: String,
            publicPath: String,
            hashValue: String,
            sessionId: UInt128,
            sessionType: UInt8,
            sessionCallback: String,
            sessionCommitment: String,
            sessionAnswer: String,
            signature: String
){
    let signer: AuthAccount;

    prepare(signer: AuthAccount){
        self.signer = signer
    }

    execute {
        Locker.receivedCrossChainNFT(
            signer: self.signer.address, 
            id: id, 
            fromChain: fromChain, 
            toChain: toChain,
            sqosString: sqosString, 
            nftID: nftID,
            receiver: receiver,
            publicPath: publicPath,
            hashValue: hashValue,
            sessionId: sessionId,
            sessionType: sessionType,
            sessionCallback: sessionCallback,
            sessionCommitment: sessionCommitment,
            sessionAnswer: sessionAnswer,
            signature: signature
        );
    }
}