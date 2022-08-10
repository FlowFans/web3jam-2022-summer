import MelodyError from "./MelodyError.cdc"
import FungibleToken from "./standard/FungibleToken.cdc"
import NonFungibleToken from "./standard/NonFungibleToken.cdc"
import MetadataViews from "./standard/MetadataViews.cdc"
import MelodyTicket from "./MelodyTicket.cdc"


pub contract Melody {

    /**    ___  ____ ___ _  _ ____
       *   |__] |__|  |  |__| [__
        *  |    |  |  |  |  | ___]
         *************************/

    
    pub let UserCertificateStoragePath: StoragePath
    pub let UserCertificatePrivatePath: PrivatePath
    // pub let CollectionStoragePath: StoragePath
    // pub let CollectionPublicPath: PublicPath
    // pub let CollectionPrivatePath: PrivatePath
    pub let AdminStoragePath: StoragePath

    /**    ____ _  _ ____ _  _ ___ ____
       *   |___ |  | |___ |\ |  |  [__
        *  |___  \/  |___ | \|  |  ___]
         ******************************/

    pub event ContractInitialized()
    pub event PauseStateChanged(pauseFlag: Bool, operator: Address)
    pub event GraceDurationChanged(before: UFix64, after: UFix64)
    pub event MinimumPaymentChanged(before: UFix64, after: UFix64)
    pub event CommisionChanged(before: UFix64, after: UFix64)
    pub event TicketCached(paymentId: UInt64, ticketId: UInt64, receiver: Address)
    pub event TicketClaimed(paymentId: UInt64, ticketId: UInt64, receiver: Address)
    pub event PaymentConfigUpdated(paymentId: UInt64, key: String,)
    pub event PaymentRevoked(paymentId: UInt64, amount: UFix64, operator: Address)
    pub event PaymentStatusUpdated(paymentId: UInt64, oldStatus: UInt8, newStatus: UInt8)
    pub event PaymentTypeChanged(paymentId: UInt64, oldType: UInt8, newType: UInt8)
    pub event PaymentDestroyed(paymentId: UInt64, ticketId: UInt64?)
    pub event PaymentWithdrawn(paymentId: UInt64, type: UInt8, status: UInt8, amount: UFix64)
    pub event VaultDeposited(identifier:String, amount: UFix64)
    pub event VaultCreated(identifier: String)
    pub event VaultWithdrawn(identifier: String, amount: UFix64, balance: UFix64)
    pub event PaymentRecordsUpdated(address: Address, before: [UInt64], after: [UInt64])
    pub event PaymentCreated(paymentId: UInt64, type: UInt8, creator: Address, receiver: Address, amount: UFix64 )
    pub event TicketRecordChanged(address: Address , before: [UInt64], after: [UInt64])
    pub event CommisionSended(paymentId: UInt64, identifier: String, amount: UFix64)
    /**    ____ ___ ____ ___ ____
       *   [__   |  |__|  |  |___
        *  ___]  |  |  |  |  |___
         ************************/

     // ticket type
    pub enum PaymentType: UInt8 {
        pub case STREAM  // stream ticke 
        pub case REVOCABLE_STREAM // revocable stream ticket
        pub case VESTING
        pub case REVOCABLE_VESTING // revocable vesting ticket
    }

    // status for payment life cycle
    pub enum PaymentStatus: UInt8 {
        pub case UPCOMING  // not start yet 
        pub case ACTIVE // running payment
        pub case COMPLETE // completed payment
        pub case CANCELED // revoced payment
    }

    pub var totalCreated: UInt64
    pub var vestingCount: UInt64
    pub var streamCount: UInt64
    
   
    // global pause: true will stop pool creation
    pub var pause: Bool

    pub var melodyCommision: UFix64

    pub var minimumPayment: UFix64

    pub var graceDuration: UFix64

    // records user unclaim tickets with payments
    access(account) var userTicketRecords: {Address: [UInt64]}
    access(account) var paymentsRecords: {Address: [UInt64]}

    /// Reserved parameter fields: {ParamName: Value}
    access(self) let _reservedFields: {String: AnyStruct}




    /**    ____ _  _ _  _ ____ ___ _ ____ _  _ ____ _    _ ___ _   _
       *   |___ |  | |\ | |     |  | |  | |\ | |__| |    |  |   \_/
        *  |    |__| | \| |___  |  | |__| | \| |  | |___ |  |    |
         ***********************************************************/

    
    pub resource interface IdentityCertificate {}

    pub resource UserCertificate: IdentityCertificate{ 

    }

    pub resource Payment {

        pub let id: UInt64
        pub var desc: String
        pub let creator: Address
        pub let config: {String: AnyStruct}
        pub var type: PaymentType
        pub let vault: @FungibleToken.Vault
        pub var ticket: @MelodyTicket.NFT?
        pub var withdrawn: UFix64
        pub var status: PaymentStatus


        pub var metadata: {String: AnyStruct}
        

        init(id: UInt64, desc: String, creator: Address, type: PaymentType, vault: @FungibleToken.Vault, config: {String: AnyStruct}) {
            self.id = id
            self.desc = desc
            self.creator = creator
            self.config = config
            self.type = type
            self.vault <- vault
            self.ticket <- nil
            self.withdrawn = 0.0
            self.status = PaymentStatus.UPCOMING
            self.metadata = {}
        }

        // query payment revocable
        pub fun getRevocable (): Bool {
            return self.type == Melody.PaymentType.REVOCABLE_STREAM || self.type == Melody.PaymentType.REVOCABLE_VESTING
        }
        // query balance
        pub fun queryBalance(): UFix64 {
            return self.vault.balance
        }

        // query metadata
        pub fun getInfo(): {String: AnyStruct} {

            let metadata: {String: AnyStruct} = {}
            metadata["id"] = self.id
            metadata["balance"] = self.vault.balance
            metadata["withdrawn"] = self.withdrawn
            metadata["claimable"] = self.getClaimable()
            metadata["type"] = self.type.rawValue
            metadata["status"] = self.status.rawValue
            metadata["claimed"] = self.ticket == nil
            metadata["creator"] = self.creator
            metadata["desc"] = self.desc
            // metadata["config"] = self.config
            let keys = self.config.keys
            for key in keys {
                metadata[key] = self.config[key]
            }

            let nftMetadata = MelodyTicket.getMetadata(self.id)!
            if nftMetadata != nil {
                metadata["recipient"] = (nftMetadata["owner"] as? Address)
            }
            metadata["ticketInfo"] = nftMetadata
            return metadata
        }

        

        // === write funcs ===
        // rrevoke payment
        pub fun revokePayment(userCertificateCap: Capability<&{Melody.IdentityCertificate}>): @FungibleToken.Vault {
            pre {
                self.status != PaymentStatus.COMPLETE && self.status != PaymentStatus.CANCELED : MelodyError.errorEncode(msg: "Cannot cancel close payment", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE)
                self.creator == userCertificateCap.borrow()!.owner!.address : MelodyError.errorEncode(msg: "Only owner can revokePayment", err: MelodyError.ErrorCode.ACCESS_DENIED)
                self.type == PaymentType.REVOCABLE_STREAM || self.type == Melody.PaymentType.REVOCABLE_VESTING : MelodyError.errorEncode(msg: "Only revocable payment can be revoked", err: MelodyError.ErrorCode.PAYMENT_NOT_REVOKABLE)
            }

            let balance = self.vault.balance
            self.status = PaymentStatus.CANCELED
            Melody.updateTicketMetadata(id: self.id, key: "status", value: PaymentStatus.CANCELED.rawValue)
            
            emit PaymentRevoked(paymentId: self.id, amount: balance, operator: self.creator)

            return <- self.vault.withdraw(amount: balance)
        }

        // cache ticket while receiver do not have receievr resource
        access(contract) fun chacheTicket(ticket: @MelodyTicket.NFT) {
            pre {
                self.ticket == nil : MelodyError.errorEncode(msg: "Ticket already cached", err: MelodyError.ErrorCode.ALREADY_EXIST)
            }

            let receievr = (self.config["receiver"] as? Address?)!

            emit TicketCached(paymentId: self.id, ticketId: ticket.id, receiver: receievr!)

            self.ticket <-! ticket
        }

        // cache ticket while receiver do not have receievr resource
        access(contract) fun claimTicket():@MelodyTicket.NFT {
            pre {
                self.ticket != nil : MelodyError.errorEncode(msg: "Ticket already cached", err: MelodyError.ErrorCode.ALREADY_EXIST)
            }
            let ticket <- self.ticket <- nil
            self.config.remove(key: "receiver")

            return <- ticket!
        }

        // cache ticket while receiver do not have receievr resource
        access(contract) fun updateConfig(_ key: String, value: AnyStruct) {
            pre {
                self.config[key] != nil : MelodyError.errorEncode(msg: "Not set vaule", err: MelodyError.ErrorCode.NOT_EXIST)
            }
            let oldVal = self.config[key]
            self.config[key] = value

            emit PaymentConfigUpdated(paymentId: self.id, key: key)

        }

        // cache ticket while receiver do not have receievr resource
        access(contract) fun changeRevocable() {
            pre {
                self.status != PaymentStatus.COMPLETE && self.status != PaymentStatus.CANCELED : MelodyError.errorEncode(msg: "Cannot change close payment ", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE)
            }
            let oldType = self.type
            var type = oldType
            if oldType == PaymentType.REVOCABLE_STREAM {
                type = PaymentType.STREAM
            } else if oldType == PaymentType.REVOCABLE_VESTING {
                type = PaymentType.VESTING
            } 
            assert(oldType != type, message: MelodyError.errorEncode(msg: "Canot set same type", err: MelodyError.ErrorCode.INVALID_PARAMETERS))
            self.type = type

            emit PaymentTypeChanged(paymentId: self.id, oldType: oldType.rawValue, newType: type.rawValue)
        }

        access(contract) fun getClaimable(): UFix64 {
            
            let config = self.config
            let withdrawn = self.withdrawn
            let currentTimestamp = getCurrentBlock().timestamp
            let startTimestamp = (self.config["startTimestamp"] as? UFix64?)!!
            let vaultBalance = self.vault.balance
            var claimable = 0.0
            
            if self.status == PaymentStatus.COMPLETE || self.status == PaymentStatus.CANCELED {
                return 0.0
            } 
            if self.type == PaymentType.STREAM || self.type == PaymentType.REVOCABLE_STREAM {
                let endTimestamp = (self.config["endTimestamp"] as? UFix64?)!!
                let amount = (self.config["amount"] as? UFix64?)!!
                // assert(1==2, message: "start time:".concat(startTimestamp.toString()).concat("end time:").concat(currentTimestamp.toString()))
                var timeDelta = 0.0
                if currentTimestamp <= startTimestamp {
                    return 0.0
                }
                if currentTimestamp > endTimestamp {
                    timeDelta = endTimestamp - startTimestamp
                } else {
                    timeDelta = currentTimestamp - startTimestamp
                }
                let streamed = timeDelta / (endTimestamp - startTimestamp) * amount
                claimable = streamed
                 
            } else {

                let cliffDuration = (config["cliffDuration"] as? UFix64?)! ?? 0.0
                let cliffAmount = (config["cliffAmount"] as? UFix64?)! ?? 0.0
                let stepDuration = (config["stepDuration"] as? UFix64?)!!
                let steps = (config["steps"] as? Int8?)!!
                let stepAmount = (config["stepAmount"] as? UFix64?)!!
                let timeAfterCliff = startTimestamp + cliffDuration

                if currentTimestamp < timeAfterCliff {
                    return 0.0
                }
                var vested = cliffAmount

                let passedSinceCliff = currentTimestamp - timeAfterCliff

                var stepPassed = Int8(passedSinceCliff / stepDuration)
                if stepPassed > steps {
                    stepPassed = steps
                }

                vested = vested + (UFix64(stepPassed) * stepAmount)
                claimable = vested
                
            }

            return claimable
        }




        // withdraw
        access(contract) fun withdraw(_ amount: UFix64): @FungibleToken.Vault {
            pre {
                self.status != PaymentStatus.COMPLETE && self.status != PaymentStatus.CANCELED : MelodyError.errorEncode(msg: "Cannot update close payment", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE)
            }
            self.withdrawn = self.withdrawn + amount
            self.updateStatus()
            return <- self.vault.withdraw(amount: amount)
        }

        access(self) fun updateStatus() {

            let currentTimestamp = getCurrentBlock().timestamp
            let startTimestamp = (self.config["startTimestamp"] as? UFix64?)!!
            
            let oldStatus = self.status
            var status = oldStatus
            // update status when stream start
            if self.type == PaymentType.STREAM || self.type == PaymentType.REVOCABLE_STREAM {
                let endTimestamp = (self.config["endTimestamp"] as? UFix64?)!!

                if self.status == PaymentStatus.UPCOMING && currentTimestamp >= startTimestamp {
                    status = PaymentStatus.ACTIVE
                }
                if self.status == PaymentStatus.ACTIVE && currentTimestamp >= endTimestamp {
                    status = PaymentStatus.COMPLETE
                }
            } else { // update vesing status
                if self.status == PaymentStatus.UPCOMING && currentTimestamp >= startTimestamp {
                    status = PaymentStatus.ACTIVE
                }
                let stepDuration = (self.config["stepDuration"] as? UFix64?)!!
                let steps = (self.config["steps"] as? Int8?)!!
                let cliffDuration = (self.config["cliffDuration"] as? UFix64?)! ?? 0.0
                let endVestingTimestamp = startTimestamp + cliffDuration + UFix64(steps) * stepDuration
                if self.status == PaymentStatus.ACTIVE && currentTimestamp >= endVestingTimestamp {
                    status = PaymentStatus.COMPLETE
                }
            }

            self.status = status
            if status == PaymentStatus.COMPLETE {
                Melody.updateTicketMetadata(id: self.id, key: "status", value: PaymentStatus.COMPLETE.rawValue)
            }
            if status == PaymentStatus.ACTIVE {
                Melody.updateTicketMetadata(id: self.id, key: "status", value: PaymentStatus.ACTIVE.rawValue)
            }

            emit PaymentStatusUpdated(paymentId: self.id, oldStatus: oldStatus.rawValue, newStatus: status.rawValue)

        }

        destroy (){
            pre {
                self.status == PaymentStatus.COMPLETE && self.status == PaymentStatus.CANCELED : MelodyError.errorEncode(msg: "Cannot destroy active payment", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE)
                self.vault.balance > 0.0 : MelodyError.errorEncode(msg: "Please withdraw the remaining funds", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE)
            }

            emit PaymentDestroyed(paymentId: self.id, ticketId: self.ticket?.id)

            destroy self.vault
            destroy self.ticket


        }
    }


    // resources
    // melody admin resource for manage melody contract
    pub resource Admin {

        access(self) var vaults: @{String: FungibleToken.Vault}

        access(self) let payments: @{UInt64: Payment}

        init() {
            self.payments <- {}
            self.vaults <- {}
        }

        pub fun setPause(_ flag: Bool) {
            pre {
                Melody.pause != flag : MelodyError.errorEncode(msg: "Set pause state faild, the state is same", err: MelodyError.ErrorCode.SAME_BOOL_STATE)
            }
            Melody.pause = flag

            emit PauseStateChanged(pauseFlag: flag, operator: self.owner!.address)
        }

        pub fun setCommision(_ commision: UFix64) {

            emit CommisionChanged(before: Melody.melodyCommision, after: commision)
            Melody.melodyCommision = commision
        }

        pub fun setMinimumPayment(_ min: UFix64) {

            emit MinimumPaymentChanged(before: Melody.minimumPayment, after: min)
            Melody.minimumPayment = min
        }

        pub fun setGraceDuration(_ duration: UFix64) {
            emit GraceDurationChanged(before: Melody.graceDuration, after: duration)
            Melody.graceDuration = duration
        }
        
        pub fun getPayment(_ id: UInt64): &Payment {
            pre{
                self.payments[id] != nil : MelodyError.errorEncode(msg: "Payment not found", err: MelodyError.ErrorCode.NOT_EXIST)
            }
            let paymentRef = (&self.payments[id] as &Payment?)!
            return paymentRef
        }

        pub fun savePayment(_ payment: @Payment) {
            pre {
                self.payments[payment.id] == nil : MelodyError.errorEncode(msg: "Payment already exists", err: MelodyError.ErrorCode.ALREADY_EXIST)
            }
            self.payments[payment.id] <-! payment
        }


        pub fun deposit(_ vault: @FungibleToken.Vault) {
            let identifier = vault.getType().identifier
            if self.vaults[identifier] == nil {
                self.vaults[identifier] <-! vault
                emit VaultCreated(identifier: identifier)
            } else {
                let vaultRef = (&self.vaults[identifier] as &FungibleToken.Vault?)!
                emit VaultDeposited(identifier:identifier, amount: vault.balance)
                vaultRef.deposit(from: <- vault)
            }

        }

        pub fun withdraw(_ key: String?, amount: UFix64?): @{String: FungibleToken.Vault} {
            let vaults: @{String: FungibleToken.Vault} <- {}
            var keys: [String] = []
            if key != nil && key != "" {
                let vaultRef = (&vaults[key!] as &FungibleToken.Vault?)!
                let balance = vaultRef.balance
                let withdrawAmount = amount ?? balance
                vaults[key!] <-! vaultRef!.withdraw(amount: withdrawAmount)
                emit VaultWithdrawn(identifier: key!, amount: withdrawAmount, balance: balance- withdrawAmount)
                return <- vaults
            } else {
                keys = self.vaults.keys
                for k in keys {
                    let vaultRef = (&vaults[k] as &FungibleToken.Vault?)!
                    let balance = vaultRef.balance
                    let withdrawAmount = amount ?? balance
                    vaults[k] <-! vaultRef!.withdraw(amount: withdrawAmount)
                    emit VaultWithdrawn(identifier: key!, amount: withdrawAmount, balance: balance- withdrawAmount)
                }
                return <- vaults
            }
        }
       
        


        destroy() {
            destroy self.payments
            destroy self.vaults
        }
        
    }

   
    // ---- contract methods ----

    pub fun setupUser(): @UserCertificate {
        let certificate <- create UserCertificate()
        return <- certificate
    }

    // update nft metadata
    access(account) fun updateTicketMetadata(id: UInt64, key: String, value: AnyStruct) {
        pre {
            MelodyTicket.getMetadata(id) != nil : MelodyError.errorEncode(msg: "Ticket not found", err: MelodyError.ErrorCode.NOT_EXIST)
        }
        MelodyTicket.updateMetadata(id: id, key: key, value: value)
    }

    // set metadata
    access(account) fun setTicketMetadata(id: UInt64, metadata: {String: AnyStruct}) {
        pre {
            MelodyTicket.getMetadata(id) == nil : MelodyError.errorEncode(msg: "Ticket already exist", err: MelodyError.ErrorCode.ALREADY_EXIST)
        }
        MelodyTicket.setMetadata(id: id, metadata: metadata)
    }

    // set payments records
    access(account) fun updatePaymentsRecords(address: Address, id: UInt64) {
        let ids = self.paymentsRecords[address] ?? []
        var newIds = ids
        newIds.append(id)
        self.paymentsRecords[address] = newIds
        
        emit PaymentRecordsUpdated(address: address, before: ids, after: newIds)
    }


    /// create stream 
    /**
     ** @param userCertificateCap - creator cap to proof there identity
     ** @param vault - contain the FT token to steam
     ** @param receiver - the receiver address
     ** @param revocable - stream can be revoke or not
     ** @param config - config of create a stream
        ** @param startTimestamp - start time of stream
        ** @param endTimestamp - end time of stream
        ** @param desc - desc of stream
     */
    pub fun createStream(userCertificateCap: Capability<&{Melody.IdentityCertificate}>, vault: @FungibleToken.Vault, receiver: Address, revocable: Bool, config: {String: AnyStruct}) {
        pre {
            vault.balance >= Melody.minimumPayment : MelodyError.errorEncode(msg: "Vault balance must be greater than ".concat(Melody.minimumPayment.toString()), err: MelodyError.ErrorCode.INVALID_PARAMETERS)
            self.pause == false: MelodyError.errorEncode(msg: "Create stream is paused", err: MelodyError.ErrorCode.PAUSED)
        }
        let account = self.account
        let adminRef = account.borrow<&Admin>(from: self.AdminStoragePath)!
        let creator = userCertificateCap.borrow()!.owner!.address
        let paymentId = Melody.totalCreated + UInt64(1)
        Melody.streamCount = Melody.streamCount + UInt64(1)
        
        let desc = (config["desc"] as? String) ?? ""
        var type = PaymentType.STREAM
        if revocable {
            type = PaymentType.REVOCABLE_STREAM
        }

        let recipient = getAccount(receiver).getCapability<&{NonFungibleToken.CollectionPublic}>(MelodyTicket.CollectionPublicPath).borrow()


        let balance = vault.balance
        let currentTimestamp = getCurrentBlock().timestamp
        let startTimestamp = (config["startTimestamp"] as? UFix64?)!!
        let endTimestamp = (config["endTimestamp"] as? UFix64?)!!
        let transferable = (config["transferable"] as? Bool?)! ?? true

        let vaultIdentifier = (config["vaultIdentifier"] as? String?)!! 
        assert(vaultIdentifier != "", message: MelodyError.errorEncode(msg: "Must have vaultIdentifier", err: MelodyError.ErrorCode.INVALID_PARAMETERS))
        assert(currentTimestamp + Melody.graceDuration <= startTimestamp, message: MelodyError.errorEncode(msg: "Start time must be greater than current time, currentTimestamp: ".concat(currentTimestamp.toString()).concat(" startTimestamp: ").concat(startTimestamp.toString()), err: MelodyError.ErrorCode.INVALID_PARAMETERS))
        assert(endTimestamp > startTimestamp + Melody.graceDuration, message: MelodyError.errorEncode(msg: "End time must be greater than current time", err: MelodyError.ErrorCode.INVALID_PARAMETERS))

        if recipient == nil {
           config["receiver"] = receiver
        }
        config["amount"] = balance
        config["vaultType"]= vault.getType().identifier

        let payment <- create Payment(id: paymentId, desc:desc, creator: creator, type: type, vault: <- vault, config: config)
       
        adminRef.savePayment(<- payment)
        let paymentRef = adminRef.getPayment(paymentId)

        self.totalCreated = paymentId
        self.streamCount = self.streamCount + UInt64(1)
        self.updatePaymentsRecords(address: creator, id: paymentId)

        let ticketMinter = account.borrow<&MelodyTicket.NFTMinter>(from: MelodyTicket.MinterStoragePath)!
       

        let name = "Melody".concat(" stream ticket#").concat(paymentId.toString())
        // todo
        let metadata: {String: AnyStruct} = {}
        // metadata["paymentInfo"] = config
        metadata["paymentType"] = type.rawValue
        metadata["paymentId"] = paymentId
        metadata["status"] = PaymentStatus.UPCOMING.rawValue
        metadata["receiver"] = receiver
        metadata["vaultType"]= paymentRef.vault.getType().identifier
        
        if transferable == false {
            metadata["transferable"] = false
        }
         // info for nft ticket
        let nftMetadata: {String: AnyStruct} = {}
        nftMetadata["creator"] = creator

        emit PaymentCreated(paymentId: paymentRef.id, type: type.rawValue, creator: creator, receiver: receiver, amount: balance )

        let nft <- ticketMinter.mintNFT(name: name, description: desc, metadata: nftMetadata)

        self.setTicketMetadata(id: nft.id, metadata: metadata)

        if recipient != nil {
            recipient!.deposit(token: <- nft)
        } else {
                self.updateUserTicketsRecord(address: receiver, id: paymentRef.id, isDelete: false )
            paymentRef.chacheTicket(ticket: <- nft)
        }
    }

     /// create vesting
    pub fun createVesting(userCertificateCap: Capability<&{Melody.IdentityCertificate}>, vault: @FungibleToken.Vault, receiver: Address, revocable: Bool, config: {String: AnyStruct}) {
        pre {
            vault.balance > Melody.minimumPayment : MelodyError.errorEncode(msg: "Vault balance must be greater than 0", err: MelodyError.ErrorCode.CAN_NOT_BE_ZERO)
            self.pause == false: MelodyError.errorEncode(msg: "Create stream is paused", err: MelodyError.ErrorCode.PAUSED)
        }
        let account = self.account
        let adminRef = account.borrow<&Admin>(from: self.AdminStoragePath)!
        let creator = userCertificateCap.borrow()!.owner!.address
        let paymentId = Melody.totalCreated + UInt64(1)
        Melody.vestingCount = Melody.vestingCount + UInt64(1)
        
        let desc = (config["desc"] as? String) ?? ""
        var type = Melody.PaymentType.VESTING
        if revocable {
            type = PaymentType.REVOCABLE_VESTING
        }

        let recipient = getAccount(receiver).getCapability<&{NonFungibleToken.CollectionPublic}>(MelodyTicket.CollectionPublicPath).borrow()

        // validate config
        let balance = vault.balance
        let currentTimestamp = getCurrentBlock().timestamp
        let startTimestamp = (config["startTimestamp"] as? UFix64?)!!
        let cliffDuration = (config["cliffDuration"] as? UFix64?)! ?? 0.0
        let cliffAmount = (config["cliffAmount"] as? UFix64?)! ?? 0.0
        let stepDuration = (config["stepDuration"] as? UFix64?)!!
        let steps = (config["steps"] as? Int8?)!!
        let stepAmount = (config["stepAmount"] as? UFix64?)!!
        let transferable = (config["transferable"] as? Bool?)! ?? true
        assert(steps >= 1, message: MelodyError.errorEncode(msg: "Step must greater than 0", err: MelodyError.ErrorCode.INVALID_PARAMETERS))

        let totalAmount = cliffAmount + UFix64(steps) * stepAmount!
        let vaultIdentifier = (config["vaultIdentifier"] as? String?)!! 

        assert(stepAmount >= Melody.minimumPayment, message: MelodyError.errorEncode(msg: "Step amount must be greater than minimum payment", err: MelodyError.ErrorCode.INVALID_PARAMETERS))
        // assert(vaultIdentifier != "", message: MelodyError.errorEncode(msg: "Must have vaultIdentifier", err: MelodyError.ErrorCode.INVALID_PARAMETERS))
        // assert(cliffAmount > 0.0 && cliffDuration + startTimestamp > currentTimestamp, message: MelodyError.errorEncode(msg: "Start time must be greater than current time", err: MelodyError.ErrorCode.INVALID_PARAMETERS))
        if cliffAmount > 0.0 || cliffDuration > 0.0 {
            assert(cliffAmount > 0.0 && cliffDuration > 0.0, message: MelodyError.errorEncode(msg: "Cliff amount and duration invalid", err: MelodyError.ErrorCode.INVALID_PARAMETERS))
            // assert(cliffAmount > 0.0 && cliffDuration + startTimestamp > currentTimestamp, message: MelodyError.errorEncode(msg: "Start time must be greater than current time", err: MelodyError.ErrorCode.INVALID_PARAMETERS))
        }
        assert(balance >= totalAmount, message: MelodyError.errorEncode(msg: "Valut balance not enougth - balance: ".concat(balance.toString()).concat("required: ").concat(totalAmount.toString()), err: MelodyError.ErrorCode.INVALID_PARAMETERS))
        assert(currentTimestamp + Melody.graceDuration <= startTimestamp, message: MelodyError.errorEncode(msg: "Start time must be greater than current time with grace period, currentTimestamp: ".concat(currentTimestamp.toString()).concat(" startTimestamp: ").concat(startTimestamp.toString()), err: MelodyError.ErrorCode.INVALID_PARAMETERS))
        assert(stepDuration >=  Melody.graceDuration, message: MelodyError.errorEncode(msg: "Step duration must be greater than grace period", err: MelodyError.ErrorCode.INVALID_PARAMETERS))

        if recipient == nil {
           config["receiver"] = receiver
        }
        config["amount"] = balance
        config["vaultType"]= vault.getType().identifier

        let payment <- create Payment(id: paymentId, desc:desc, creator: creator, type: type, vault: <- vault, config: config)
       
        adminRef.savePayment(<- payment)
        let paymentRef = adminRef.getPayment(paymentId)

        self.totalCreated = paymentId
        self.streamCount = self.streamCount + UInt64(1)
        self.updatePaymentsRecords(address: creator, id: paymentId)

        let ticketMinter = account.borrow<&MelodyTicket.NFTMinter>(from: MelodyTicket.MinterStoragePath)!

        let name = "Melody".concat(" vesting ticket#").concat(paymentId.toString())

        let metadata:{String: AnyStruct} = {}
        metadata["paymentInfo"] = config
        metadata["paymentType"] = type.rawValue
        metadata["paymentId"] = paymentId
        metadata["status"] = PaymentStatus.UPCOMING.rawValue
        metadata["receiver"] = receiver
        

        if transferable == false {
            metadata["transferable"] = false
        }
        // info for nft ticket
        let nftMetadata: {String: AnyStruct} = {}
        nftMetadata["creator"] = creator

        emit PaymentCreated(paymentId: paymentRef.id, type: type.rawValue, creator: creator, receiver: receiver, amount: balance )
        let nft <- ticketMinter.mintNFT(name: name, description: desc, metadata: nftMetadata)

        self.setTicketMetadata(id: nft.id, metadata: metadata)

        if recipient != nil {
            recipient!.deposit(token: <- nft)
        } else {
            paymentRef.chacheTicket(ticket: <- nft)
            self.updateUserTicketsRecord(address: receiver, id:paymentRef.id, isDelete: false )
        }
    }

    // todo update
    // pub fun updatePayment(userCertificateCap: Capability<&{Melody.IdentityCertificate}>, paymentId: UInt64, config: {String: AnyStruct}) {
    //     pre {
    //         self.paymentsRecords[userCertificateCap.borrow()!.owner!.address]!.contains(paymentId): MelodyError.errorEncode(msg: "Access denied when update payment info", err: MelodyError.ErrorCode.ACCESS_DENIED)
    //     }

    //     let paymentRef = self.account.borrow<&Admin>(from: self.AdminStoragePath)!.getPayment(paymentId)
    //     let config = paymentRef.config
    //     // todo make sure modify 
    //     if paymentRef.status == PaymentStatus.UPCOMING {
    //         let desc = (config["desc"] as? String)
    //         if desc != nil {
    //             paymentRef.updateConfig("desc", value: desc)
    //         }
    //     }

        
        
    // }


    // change payment revocable to non-revocable
    pub fun revokePayment(userCertificateCap: Capability<&{Melody.IdentityCertificate}>, paymentId: UInt64): @FungibleToken.Vault {
        pre {
            self.paymentsRecords[userCertificateCap.borrow()!.owner!.address]!.contains(paymentId): MelodyError.errorEncode(msg: "Access denied when cancle payment", err: MelodyError.ErrorCode.ACCESS_DENIED)

        }
        let paymentRef = self.account.borrow<&Admin>(from: self.AdminStoragePath)!.getPayment(paymentId)

        assert(paymentRef.status != PaymentStatus.CANCELED || paymentRef.status != PaymentStatus.COMPLETE , message: MelodyError.errorEncode(msg: "Payment already canceled", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        assert(paymentRef.type != PaymentType.VESTING && paymentRef.type != PaymentType.STREAM, message: MelodyError.errorEncode(msg: "Cannot cancel non-revoked payment", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        return <- paymentRef.revokePayment(userCertificateCap: userCertificateCap)
    }

    // change payment revocable to non-revocable
    pub fun changeRevocable(userCertificateCap: Capability<&{Melody.IdentityCertificate}>, paymentId: UInt64) {
        pre {
            self.paymentsRecords[userCertificateCap.borrow()!.owner!.address]!.contains(paymentId): MelodyError.errorEncode(msg: "Access denied when update payment info", err: MelodyError.ErrorCode.ACCESS_DENIED)
        }
        let paymentRef = self.account.borrow<&Admin>(from: self.AdminStoragePath)!.getPayment(paymentId)

        assert(paymentRef.status != PaymentStatus.CANCELED || paymentRef.status != PaymentStatus.COMPLETE , message: MelodyError.errorEncode(msg: "Cannot change revocable with canceled payment", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        assert(paymentRef.type != PaymentType.VESTING && paymentRef.type != PaymentType.STREAM, message: MelodyError.errorEncode(msg: "Cannot change revocable with non-revoked payment", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        paymentRef.changeRevocable()
    }


    // change payment ticket transferable if is non-transferable
    pub fun changeTransferable(userCertificateCap: Capability<&{Melody.IdentityCertificate}>, paymentId: UInt64) {
        pre {
            self.paymentsRecords[userCertificateCap.borrow()!.owner!.address]!.contains(paymentId): MelodyError.errorEncode(msg: "Access denied when update payment info", err: MelodyError.ErrorCode.ACCESS_DENIED)
        }
        let paymentRef = self.account.borrow<&Admin>(from: self.AdminStoragePath)!.getPayment(paymentId)
        let transferable = (paymentRef.config["transferable"] as? Bool?)! ?? true
        assert(paymentRef.status != PaymentStatus.CANCELED || paymentRef.status != PaymentStatus.COMPLETE , message: MelodyError.errorEncode(msg: "Cannot change transferable with canceled payment", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        assert(transferable == false, message: MelodyError.errorEncode(msg: "Only allow non-transferable to transferable", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        paymentRef.updateConfig("transferable", value: true)
        let nftMetadata = MelodyTicket.getMetadata(paymentId)
        Melody.updateTicketMetadata(id: paymentId, key: "paymentInfo", value: paymentRef.config)
        Melody.updateTicketMetadata(id: paymentId, key: "transferable", value: true)

    }

    pub fun claimTicket(userCertificateCap: Capability<&{Melody.IdentityCertificate}>, paymentId: UInt64): @MelodyTicket.NFT {
        pre {
            self.getUserTicketRecords(userCertificateCap.borrow()!.owner!.address)!.contains(paymentId): MelodyError.errorEncode(msg: "Access denied when claim ticket", err: MelodyError.ErrorCode.ACCESS_DENIED)
        }
        let paymentRef = self.account.borrow<&Admin>(from: self.AdminStoragePath)!.getPayment(paymentId)
        assert(paymentRef.status != PaymentStatus.CANCELED, message: MelodyError.errorEncode(msg: "Cannot claim ticket from canceled payment", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        let config = paymentRef.config
        let recipient = (config["receiver"] as? Address?)!
        assert(recipient == userCertificateCap.borrow()!.owner!.address, message: MelodyError.errorEncode(msg: "Cannot claim ticket from wrong receiver", err: MelodyError.ErrorCode.ACCESS_DENIED))
        

        self.updateUserTicketsRecord(address: recipient!, id:paymentRef.id, isDelete: true )
        let ticketRef = &paymentRef.ticket as &MelodyTicket.NFT?

        emit TicketClaimed(paymentId: paymentRef.id, ticketId: ticketRef!.id, receiver: recipient! )

        return <- paymentRef.claimTicket()
    }


    pub fun withdraw(userCertificateCap: Capability<&{Melody.IdentityCertificate}>, ticket: &MelodyTicket.NFT): @FungibleToken.Vault {
        pre {
            userCertificateCap.borrow()!.owner!.address == ticket.owner!.address : MelodyError.errorEncode(msg: "Withdraw ", err: MelodyError.ErrorCode.ACCESS_DENIED)
        }
        // todo
        let paymentRef = self.account.borrow<&Admin>(from: self.AdminStoragePath)!.getPayment(ticket.id)

        var vault: @FungibleToken.Vault? <- nil

        if paymentRef.type == PaymentType.VESTING || paymentRef.type == PaymentType.REVOCABLE_VESTING {
            vault <-! self.withdrawVesting(ticket: ticket)
        } else {
            vault <-! self.withdrawStream(ticket: ticket)
        }

        return <- vault!
    }

    // stream withdraw
    access(contract) fun withdrawStream(ticket: &MelodyTicket.NFT): @FungibleToken.Vault {

        let paymentRef = self.account.borrow<&Admin>(from: self.AdminStoragePath)!.getPayment(ticket.id)
        let paymentType = paymentRef.type
        let paymentStatus = paymentRef.status
        let config = paymentRef.config
        let vaultRef = &paymentRef.vault as! &FungibleToken.Vault
        let withdrawn = paymentRef.withdrawn

        assert(paymentType == PaymentType.STREAM || paymentType == PaymentType.REVOCABLE_STREAM, message: MelodyError.errorEncode(msg: "Can only withdraw from stream payment", err: MelodyError.ErrorCode.TYPE_MISMATCH))
        assert(paymentStatus == PaymentStatus.UPCOMING || paymentStatus == PaymentStatus.ACTIVE, message: MelodyError.errorEncode(msg: "Can withdraw with wrong status", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        
        let currentTimestamp = getCurrentBlock().timestamp

        let startTimestamp = (config["startTimestamp"] as? UFix64?)!!
        let vaultBalance = vaultRef.balance

        assert(currentTimestamp > startTimestamp, message: MelodyError.errorEncode(msg: "Can not withdraw before start", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        let streamed = paymentRef.getClaimable()
        let canClaimAmount = streamed - withdrawn

        assert(streamed >= withdrawn, message: MelodyError.errorEncode(msg: "Steamed amount must greater than withdraw amount", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        assert(canClaimAmount <= vaultBalance, message: MelodyError.errorEncode(msg: "Withdraw amount must lower than vault balance", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        let withdrawVault <- paymentRef.withdraw(canClaimAmount)

        // commision cut
        self.cutCommision(&withdrawVault as &FungibleToken.Vault, paymentId: paymentRef.id)
        
        emit PaymentWithdrawn(paymentId: paymentRef.id, type: paymentType.rawValue, status: paymentStatus.rawValue, amount: canClaimAmount)

        return <- withdrawVault
    }
    
    // vesting withdraw
    access(contract) fun withdrawVesting(ticket: &MelodyTicket.NFT): @FungibleToken.Vault {
        let paymentRef = self.account.borrow<&Admin>(from: self.AdminStoragePath)!.getPayment(ticket.id)
        let paymentType = paymentRef.type
        let paymentStatus = paymentRef.status
        let config = paymentRef.config
        let vaultRef = &paymentRef.vault as! &FungibleToken.Vault
        let withdrawn = paymentRef.withdrawn

        assert(paymentType == PaymentType.VESTING || paymentType == PaymentType.REVOCABLE_VESTING, message: MelodyError.errorEncode(msg: "Can only withdraw from vesting payment", err: MelodyError.ErrorCode.TYPE_MISMATCH))
        assert(paymentStatus == PaymentStatus.UPCOMING || paymentStatus == PaymentStatus.ACTIVE, message: MelodyError.errorEncode(msg: "Can withdraw with wrong status", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        
        let currentTimestamp = getCurrentBlock().timestamp
        let startTimestamp = (config["startTimestamp"] as? UFix64?)!!
        let vaultBalance = vaultRef.balance

        assert(currentTimestamp >= startTimestamp, message: MelodyError.errorEncode(msg: "Can withdraw before start".concat(currentTimestamp.toString()).concat(startTimestamp.toString()), err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        let claimable = paymentRef.getClaimable()
        let canClaimAmount = claimable - withdrawn
        assert(canClaimAmount <= vaultBalance, message: MelodyError.errorEncode(msg: "Withdraw amount must lower than vault balance", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        assert(claimable >= withdrawn, message: MelodyError.errorEncode(msg: "Vesting claimable amount must greater than withdraw amount", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
        assert(canClaimAmount > 0.0, message: MelodyError.errorEncode(msg: "No amount can claim", err: MelodyError.ErrorCode.CAN_NOT_BE_ZERO))

        let withdrawVault <- paymentRef.withdraw(canClaimAmount)

        self.cutCommision(&withdrawVault as &FungibleToken.Vault, paymentId: paymentRef.id)

        return <- withdrawVault
    }


    access(contract) fun cutCommision(_ vaultRef: &FungibleToken.Vault, paymentId: UInt64){
        if self.melodyCommision > 0.0 {
            let adminRef = self.account.borrow<&Admin>(from: self.AdminStoragePath)!
            let commisionAmount = vaultRef.balance * self.melodyCommision
            emit CommisionSended(paymentId: paymentId, identifier: vaultRef.getType().identifier, amount: commisionAmount)
            adminRef.deposit(<- vaultRef.withdraw(amount: commisionAmount))

        }
    }

    access(contract) fun updateUserTicketsRecord(address: Address, id: UInt64, isDelete: Bool){
        if isDelete == true && Melody.userTicketRecords[address]!.contains(id) == false {
            panic(MelodyError.errorEncode(msg: "Delete failed: record not existed", err: MelodyError.ErrorCode.INVALID_PARAMETERS))
        }

        let userTicketRecords = Melody.userTicketRecords[address] ?? []
        var newRecords = userTicketRecords
        if isDelete {
            let index = newRecords.firstIndex(of: id)!
            newRecords.remove(at: index)

        } else {
            newRecords.append(id)
        }

        Melody.userTicketRecords[address] = newRecords
        emit TicketRecordChanged(address: address , before: userTicketRecords, after: newRecords)
    }


    pub fun getPaymentsIdRecords(_ address: Address): [UInt64] {
        let ids = self.paymentsRecords[address] ?? []
        return ids
    }

    pub fun getPaymentInfo(_ id: UInt64): {String: AnyStruct} {
        var info: {String: AnyStruct}  = {}
        let paymentRef = self.account.borrow<&Admin>(from: self.AdminStoragePath)!.getPayment(id)

        info = paymentRef.getInfo()

        return info
    }

    pub fun getUserTicketRecords(_ address: Address): [UInt64] {
        let ids = self.userTicketRecords[address] ?? []
        return ids
    }
    


    // ---- init func ----
    init() {
        self.UserCertificateStoragePath = /storage/melodyUserCertificate
        self.UserCertificatePrivatePath = /private/melodyUserCertificate
        self._reservedFields = {}
        self.totalCreated = 0
        self.vestingCount = 0
        self.streamCount = 0
        self.pause = false

        // for store the unclaim ticket for users
        self.userTicketRecords = {}
        self.paymentsRecords = {}

        self.melodyCommision = 0.01

        self.minimumPayment = 0.1

        self.graceDuration = 300.0

        self.AdminStoragePath = /storage/MelodyAdmin

        let account = self.account
        let admin <- create Admin()
        account.save(<- admin, to: self.AdminStoragePath)

        account.save(<- create UserCertificate(), to: self.UserCertificateStoragePath)
    }

}