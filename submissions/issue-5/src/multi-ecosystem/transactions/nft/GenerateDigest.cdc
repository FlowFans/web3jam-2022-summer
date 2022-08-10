import ReceivedMessageContract from 0x1a478a7149935b63
import MessageProtocol from 0x1a478a7149935b63
import IdentityVerification from 0x1a478a7149935b63

pub struct createdData {
    pub let rawData: String;
    pub let toBeSign: String;

    init(rawData: String, toBeSign: String) {
        self.rawData = rawData;
        self.toBeSign = toBeSign;
    }
}

pub fun main(
    sender: Address,
    locker: Address,
    id: UInt128, 
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
    sessionAnswer: String
): createdData {
    // prepare received message
    let sqos = MessageProtocol.SQoS()
    let sqosItem = MessageProtocol.SQoSItem(type: MessageProtocol.SQoSType.Reveal, value: sqosString)
    sqos.addItem(item: sqosItem)

    let resourceAccount = locker
    let link = publicPath
    let data = MessageProtocol.MessagePayload()

    let idItem = MessageProtocol.createMessageItem(name: "id", type: MessageProtocol.MsgType.cdcU64, value: nftID as UInt64)
    data.addItem(item: idItem!)
    let ownerItem = MessageProtocol.createMessageItem(name: "receiver", type: MessageProtocol.MsgType.cdcAddress, value: MessageProtocol.CDCAddress(addr: receiver, t: 4))
    data.addItem(item: ownerItem!)
    let hashValueItem = MessageProtocol.createMessageItem(name: "hashValue", type: MessageProtocol.MsgType.cdcString, value: hashValue)
    data.addItem(item: hashValueItem!)

    let session = MessageProtocol.Session(oId: sessionId, oType: sessionType, oCallback: sessionCallback, oc: sessionCommitment.utf8, oa: sessionAnswer.utf8)

    let receivedMessageCore = ReceivedMessageContract.ReceivedMessageCore(id: id, fromChain: fromChain, sender: sender.toString(), sqos: sqos, resourceAccount: resourceAccount, link: publicPath, data: data, session: session)

    // query nonce
    let addr: Address = sender;
    let n = IdentityVerification.getNonce(pubAddr: sender);

    let originData: [UInt8] = sender.toBytes().concat(n.toBigEndianBytes()).concat(receivedMessageCore.getRecvMessageHash());

    // return createdDatarawData: receivedMessageCore.messageHash, toBeSign: String.encodeHex(originData));
    return createdData(rawData: receivedMessageCore.messageHash, toBeSign: String.encodeHex(originData));
}