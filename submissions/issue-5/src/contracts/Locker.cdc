import MessageProtocol from "../contracts/MessageProtocol.cdc"
import SentMessageContract from "../contracts/SentMessageContract.cdc"
import ReceivedMessageContract from "../contracts/ReceivedMessageContract.cdc"
// import MetadataViews from "./MetadataViews.cdc"
// import NonFungibleToken from "./NonFungibleToken.cdc"
// import StarRealm from  "./StarRealm.cdc"
import MetadataViews from 0x1a478a7149935b63
import NonFungibleToken from 0x1a478a7149935b63
import StarRealm from  0x1a478a7149935b63

pub contract Locker{
    init(){
        // // Create a new empty collection
        // let collection <- StarRealm.createStarPort();

        // // save it to the account
        // self.account.save(<-collection, to: StarRealm.PortStoragePath);

        // // create a public capability for the collection
        // self.account.link<&{StarRealm.StarDocker}>(
        //     StarRealm.DockerPublicPath,
        //     target: StarRealm.PortStoragePath
        // );

        ////////////////////////////////////////////////////////////////////////////////////////////////
        // create cross chain received message resource
        let receivedMessageVault <- ReceivedMessageContract.createReceivedMessageVault()
        // save message as resource
        self.account.save(<-receivedMessageVault, to: /storage/receivedMessageVault)
        self.account.link<&{ReceivedMessageContract.ReceivedMessageInterface}>(/public/receivedMessageVault, target: /storage/receivedMessageVault)
    
        ////////////////////////////////////////////////////////////////////////////////////////////////
        // create cross chain sent message resource
        let sentMessageVault <-SentMessageContract.createSentMessageVault()
        // save message as resource
        self.account.save(<-sentMessageVault, to: /storage/sentMessageVault)
        self.account.link<&{SentMessageContract.SentMessageInterface}>(/public/sentMessageVault, target: /storage/sentMessageVault)
        // add acceptor link
        self.account.link<&{SentMessageContract.AcceptorFace}>(/public/acceptorFace, target: /storage/sentMessageVault)

        // add message submitter
        let msgSubmitter <- SentMessageContract.createMessageSubmitter()
        self.account.save(<-msgSubmitter, to: /storage/msgSubmitter)
        self.account.link<&{SentMessageContract.SubmitterFace}>(/public/msgSubmitter, target: /storage/msgSubmitter)

        ////////////////////////////////////////////////////////////////////////////////////////////////
        // create callme vault 
        // let calleeVault <- Locker.createEmptyCalleeVault()
        // save vault as resource
        self.account.save(<-create Locker.createEmptyCalleeVault(), to: /storage/calleeVault)
        self.account.link<&{ReceivedMessageContract.Callee}>(/public/calleeVault, target: /storage/calleeVault)
        self.account.link<&{StarRealm.StarDocker}>(/public/lockerDocker, target: /storage/calleeVault)
    }

    // Resouce to store messages from ReceivedMessageContract
    pub resource CalleeVault: ReceivedMessageContract.Callee, StarRealm.StarDocker{
        pub let receivedMessages: [MessageProtocol.MessagePayload]
        priv let lockedNFTs: @{UInt64: AnyResource{NonFungibleToken.INFT}};

        init(){
            self.receivedMessages = []
            self.lockedNFTs <- {};
        }

        destroy () {
            destroy self.lockedNFTs;
        }

        // There will be one id exists at a time
        pub fun docking(nft: @AnyResource{NonFungibleToken.INFT}): @AnyResource{NonFungibleToken.INFT}? {
            if self.lockedNFTs.containsKey(nft.id) {
                return <- nft;
            } else {
                self.lockedNFTs[nft.id] <-! nft;
                return nil;
            }
        }

        // This is a temporary solutions
        // Receive message from ReceivedMessageContract
        pub fun callMe(data: MessageProtocol.MessagePayload){
            self.receivedMessages.append(data)
        }

        pub fun getMessagesLength(): Int{
            return self.receivedMessages.length
        }

        pub fun getAllMessages(): [MessageProtocol.MessagePayload]{
            return self.receivedMessages
        }

        pub fun getLockedNFTs(): [UInt64] {
            return self.lockedNFTs.keys;
        }

        pub fun claim(id: UInt64, answer: String){
            // Match NFT id
            var isMatched = false
            for index,element in self.receivedMessages {
                if (element.items[0].value as? UInt64 == id) {
                    isMatched = true
                    // id matched
                    let receiver: Address = (element.items[1].value as? MessageProtocol.CDCAddress!).getFlowAddress()!
                    let hashValue: String = element.items[2].value as? String!
                        
                    let digest = HashAlgorithm.SHA2_256.hash(answer.utf8)

                    if("0x".concat(String.encodeHex(digest)) != hashValue){
                        panic("digest match failed")
                    }

                    // Receiver submit random number to claim NFT
                    self.transfer(id: id, receiver: receiver)

                    self.receivedMessages.remove(at: index);
                    break
                }
            }

            if(!isMatched){
                panic("id is not matched")
            }
        }

        // Transfer NFT back to receiver
        priv fun transfer(
            id: UInt64,
            receiver: Address
        ){
            log(id);
            if let starDockerRef = StarRealm.getStarDockerFromAddress(addr: receiver) {
                if self.lockedNFTs.containsKey(id) {
                    let v <- starDockerRef.docking(nft: <- self.lockedNFTs.remove(key: id)!);
                    if v != nil {
                        panic("Transfer failed when docking!");
                    } else {
                        destroy v;
                    }
                } else {
                    panic("The id of NFT has not been locked!");
                }
            } else {
                panic("star docker does not exist!");
            }
        }
    }

    pub fun createEmptyCalleeVault(): @CalleeVault{
        return <- create CalleeVault()
    }

    // pub fun test(): AnyStruct{
    //     let digest = HashAlgorithm.SHA2_256.hash("044cecaa8c944515dfc8bbab90c34a5973e75f60015bfa2af985176c33a91217".utf8)
    //     // return "0x".concat(String.encodeHex(digest));
    //     let calleeRef = self.account.getCapability<&{ReceivedMessageContract.Callee}>(/public/calleeVault).borrow()!
    //     let hashValue: String = element.items[2].value as? String!
    //     return "0x".concat(String.encodeHex(digest)) != hashValue
    // }
    // query all callee messages
    pub fun queryMessage(): [MessageProtocol.MessagePayload]{
        let calleeRef = self.account.borrow<&Locker.CalleeVault>(from: /storage/calleeVault)!
        return calleeRef.getAllMessages()
    }

    // This is a temporary solutions
    pub fun sendCrossChainNFT(transferToken: @AnyResource, signerAddress: Address, id: UInt64, owner: String, hashValue: String){

        let NFTResolver <- transferToken as! @AnyResource{MetadataViews.Resolver};
        var tokenURL: String = (NFTResolver.resolveView(Type<MetadataViews.Display>())! as! MetadataViews.Display).thumbnail.uri();
        tokenURL = tokenURL.slice(from: 7, upTo: tokenURL.length);
        tokenURL = "http://47.242.71.251:8080/ipfs/".concat(tokenURL);

        let NonToken <- NFTResolver as! @AnyResource{NonFungibleToken.INFT};
        let id: UInt64 = NonToken.id

        // Get the locker's public account object
        let locker = self.account

        // Get the Collection reference for the locker
        // getting the public capability and borrowing a reference from it
        let lockerRef = locker.getCapability(/public/lockerDocker)
            .borrow<&{StarRealm.StarDocker}>()
            ?? panic("Could not get locker reference to the NFT Collection")

        // Deposit the NFT in the locker collection
        let v <- lockerRef.docking(nft: <- NonToken);

        if v != nil {
            panic("NFT docking failed, the `id` exists!")
        } else {
            destroy v;
        }

        log("NFT transferred from owner to account locker")

        // Message params
        let toChain = "Ethereum"
        let sqosItem = MessageProtocol.SQoSItem(type: MessageProtocol.SQoSType.Identity, value: "")
        let contractName = "0x5818f70E7468e14a048B63E0211A1f4A5A4534e2"
        let actionName = "crossChainMint"
        let callType: UInt8 = 1
        let callback = ""
        let commitment = ""
        let answer = ""

        let data = MessageProtocol.MessagePayload()
        
        let idItem = MessageProtocol.createMessageItem(name: "id", type: MessageProtocol.MsgType.cdcU64, value: id as UInt64)
        data.addItem(item: idItem!)
        let tokenURLItem = MessageProtocol.createMessageItem(name: "tokenURL", type: MessageProtocol.MsgType.cdcString, value: tokenURL)
        data.addItem(item: tokenURLItem!)
        let ownerItem = MessageProtocol.createMessageItem(name: "receiver", type: MessageProtocol.MsgType.cdcString, value: owner)
        data.addItem(item: ownerItem!)
        let hashValueItem = MessageProtocol.createMessageItem(name: "hashValue", type: MessageProtocol.MsgType.cdcString, value: hashValue)
        data.addItem(item: hashValueItem!)

        // Send cross chain message
        let msgSubmitterRef  = locker.borrow<&SentMessageContract.Submitter>(from: /storage/msgSubmitter)
        let msg = SentMessageContract.msgToSubmit(toChain: toChain, sqos: [sqosItem], contractName: contractName, actionName: actionName, data: data, callType: callType, callback: callback, commitment: commitment, answer: answer)
        msgSubmitterRef!.submitWithAuth(msg, acceptorAddr: locker.address, alink: "acceptorFace", oSubmitterAddr: locker.address, slink: "msgSubmitter")
    }

    // This is a temporary solutions
    pub fun receivedCrossChainNFT(
        signer:Address,
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
        sessionAnswer: String,
        signature: String
    ){
        // Get the locker's public account object
        let locker = self.account

        // prepare received message
        let sender = signer.toString()

        let sqos = MessageProtocol.SQoS()
        let sqosItem = MessageProtocol.SQoSItem(type: MessageProtocol.SQoSType.Reveal, value: sqosString)
        sqos.addItem(item: sqosItem)

        let resourceAccount = locker.address
        let link = publicPath
        let data = MessageProtocol.MessagePayload()

        let idItem = MessageProtocol.createMessageItem(name: "id", type: MessageProtocol.MsgType.cdcU64, value: nftID as UInt64)
        data.addItem(item: idItem!)
        let ownerItem = MessageProtocol.createMessageItem(name: "receiver", type: MessageProtocol.MsgType.cdcAddress, value: MessageProtocol.CDCAddress(addr: receiver, t: 4))
        data.addItem(item: ownerItem!)
        let hashValueItem = MessageProtocol.createMessageItem(name: "hashValue", type: MessageProtocol.MsgType.cdcString, value: hashValue)
        data.addItem(item: hashValueItem!)

        let session = MessageProtocol.Session(oId: sessionId, oType: sessionType, oCallback: sessionCallback, oc: sessionCommitment.utf8, oa: sessionAnswer.utf8)

        let receivedMessageCore = ReceivedMessageContract.ReceivedMessageCore(id: id, fromChain: fromChain, sender: signer.toString(), sqos: sqos, resourceAccount: resourceAccount, link: publicPath, data: data, session: session)

        // Submit received message
        let lockerCapability = locker.getCapability<&{ReceivedMessageContract.ReceivedMessageInterface}>(/public/receivedMessageVault)
        if let receivedMessageVaultRef = lockerCapability.borrow(){
            receivedMessageVaultRef.submitRecvMessage(recvMsg:receivedMessageCore, pubAddr: signer, signatureAlgorithm: SignatureAlgorithm.ECDSA_P256, signature:signature.decodeHex())    
        }else{
            panic("Invalid ReceivedMessageVault!")
        }
    }

    pub fun claimNFT(id: UInt64, answer: String) {
        let locker = self.account;

        let calleeVaultRef = locker.borrow<&Locker.CalleeVault>(from: /storage/calleeVault)!;

        calleeVaultRef.claim(id: id, answer: answer);
    }

    pub fun getLockedNFTs(): [UInt64] {
        let locker = self.account;

        let calleeVaultRef = locker.borrow<&Locker.CalleeVault>(from: /storage/calleeVault)!;

        return calleeVaultRef.getLockedNFTs();
    }
}