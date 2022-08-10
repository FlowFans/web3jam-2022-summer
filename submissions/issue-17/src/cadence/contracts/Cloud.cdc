// Made by Lanford33
//
// Cloud.cdc defines the FungibleToken DROP and the collections of it.
//
// There are 4 stages in a DROP.
// 1. You create a new DROP by setting the basic information, depositing funds, setting the criteria for eligible accounts and token distribution mode, then share the DROP link to your community;
// 2. Community members access the DROP page via the link, check their eligibility and claim the token if they are eligible.

import FungibleToken from "./core/FungibleToken.cdc"
import Distributors from "./Distributors.cdc"
import EligibilityVerifiers from "./EligibilityVerifiers.cdc"

pub contract Cloud {

    pub let CloudAdminStoragePath: StoragePath
    pub let CloudAdminPublicPath: PublicPath
    pub let CloudAdminPrivatePath: PrivatePath

    pub let DropCollectionStoragePath: StoragePath
    pub let DropCollectionPublicPath: PublicPath
    pub let DropCollectionPrivatePath: PrivatePath

    pub event ContractInitialized()

    pub event DropCreated(dropID: UInt64, name: String, host: Address, description: String, tokenIdentifier: String)
    pub event DropClaimed(dropID: UInt64, name: String, host: Address, claimer: Address, tokenIdentifier: String, amount: UFix64)
    pub event DropPaused(dropID: UInt64, name: String, host: Address)
    pub event DropUnpaused(dropID: UInt64, name: String, host: Address)
    pub event DropEnded(dropID: UInt64, name: String, host: Address)
    pub event DropDestroyed(dropID: UInt64, name: String, host: Address)

    pub enum EligibilityStatus: UInt8 {
        pub case eligible
        pub case notEligible
        pub case hasClaimed
    }

    // Eligibility is a struct used to describe the eligibility of an account
    pub struct Eligibility {
        pub let status: EligibilityStatus
        pub let eligibleAmount: UFix64
        pub let extraData: {String: AnyStruct}

        init(
            status: EligibilityStatus, 
            eligibleAmount: UFix64,
            extraData: {String: AnyStruct}) {
            self.status = status
            self.eligibleAmount = eligibleAmount
            self.extraData = extraData
        }

        pub fun getStatus(): String {
            switch self.status {
            case EligibilityStatus.eligible: 
                return "eligible"
            case EligibilityStatus.notEligible:
                return "not eligible"
            case EligibilityStatus.hasClaimed:
                return "has claimed" 
            }
            panic("invalid status")
        }
    }

    // TokenInfo stores the information of the FungibleToken in a DROP
    pub struct TokenInfo {
        pub let tokenIdentifier: String
        pub let providerIdentifier: String
        pub let balanceIdentifier: String
        pub let receiverIdentifier: String
        pub let account: Address
        pub let contractName: String
        pub let symbol: String
        pub let providerPath: StoragePath
        pub let balancePath: PublicPath
        pub let receiverPath: PublicPath

        init(
            account: Address, 
            contractName: String,
            symbol: String,
            providerPath: String,
            balancePath: String,
            receiverPath: String 
        ) {
            let address = account.toString()
            let addrTrimmed = address.slice(from: 2, upTo: address.length)

            self.tokenIdentifier = "A.".concat(addrTrimmed).concat(".").concat(contractName)
            self.providerIdentifier = self.tokenIdentifier.concat(".Vault")
            self.balanceIdentifier = self.tokenIdentifier.concat(".Balance")
            self.receiverIdentifier = self.tokenIdentifier.concat(".Receiver")
            self.account = account
            self.contractName = contractName
            self.symbol = symbol
            self.providerPath = StoragePath(identifier: providerPath)!
            self.balancePath = PublicPath(identifier: balancePath)!
            self.receiverPath = PublicPath(identifier: receiverPath)!
        }
    }

    // We will add a ClaimRecord to claimedRecords after an account claiming it's reward
    pub struct ClaimRecord {
        pub let address: Address
        pub let amount: UFix64
        pub let claimedAt: UFix64
        pub let extraData: {String: AnyStruct}

        init(address: Address, amount: UFix64, extraData: {String: AnyStruct}) {
            self.address = address
            self.amount = amount
            self.extraData = extraData
            self.claimedAt = getCurrentBlock().timestamp
        }
    }

    pub enum AvailabilityStatus: UInt8 {
        pub case ok
        pub case ended
        pub case notStartYet
        pub case expired
        pub case noCapacity
        pub case paused
    }

    pub struct Availability {
        pub let status: AvailabilityStatus
        pub let extraData: {String: AnyStruct}

        init(status: AvailabilityStatus, extraData: {String: AnyStruct}) {
            self.status = status
            self.extraData = extraData
        }

        pub fun getStatus(): String {
            switch self.status {
            case AvailabilityStatus.ok:
                return "ok"
            case AvailabilityStatus.ended:
                return "ended"
            case AvailabilityStatus.notStartYet:
                return "not start yet"
            case AvailabilityStatus.expired:
                return "expired"
            case AvailabilityStatus.noCapacity:
                return "no capacity"
            case AvailabilityStatus.paused:
                return "paused"
            }
            panic("invalid status")
        }
    }

    // The airdrop created in Drizzle is called DROP.
    // IDropPublic defined the public fields and functions of a DROP
    pub resource interface IDropPublic {
        pub let dropID: UInt64
        pub let name: String
        pub let description: String
        pub let host: Address
        pub let createdAt: UFix64
        pub let image: String?
        pub let url: String?

        pub let startAt: UFix64?
        pub let endAt: UFix64?

        pub let tokenInfo: TokenInfo
        pub let distributor: {Distributors.IDistributor}
        pub let verifyMode: EligibilityVerifiers.VerifyMode

        pub var isPaused: Bool
        pub var isEnded: Bool
        // Helper field for use to access the claimed amount of DROP easily
        pub var claimedAmount: UFix64

        pub fun claim(receiver: &{FungibleToken.Receiver}, params: {String: AnyStruct})
        pub fun checkAvailability(params: {String: AnyStruct}): Availability
        pub fun checkEligibility(account: Address, params: {String: AnyStruct}): Eligibility

        pub fun getClaimedRecord(account: Address): ClaimRecord?
        pub fun getClaimedRecords(): {Address: ClaimRecord}
        pub fun getDropBalance(): UFix64
        pub fun getVerifiers(): {String: [{EligibilityVerifiers.IEligibilityVerifier}]}
    }

    pub resource interface IDropCollectionPublic {
        pub fun getAllDrops(): {UInt64: &{IDropPublic}}
        pub fun borrowPublicDropRef(dropID: UInt64): &{IDropPublic}?
    }

    pub resource Drop: IDropPublic {
        pub let dropID: UInt64
        pub let name: String
        pub let description: String
        pub let host: Address
        pub let createdAt: UFix64
        pub let image: String?
        pub let url: String?

        pub let startAt: UFix64?
        pub let endAt: UFix64?

        pub let tokenInfo: TokenInfo

        pub let distributor: {Distributors.IDistributor}
        pub let verifyMode: EligibilityVerifiers.VerifyMode

        pub var isPaused: Bool
        pub var isEnded: Bool
        pub let claimedRecords: {Address: ClaimRecord}
        pub var claimedAmount: UFix64
        pub let extraData: {String: AnyStruct}

        access(account) let verifiers: {String: [{EligibilityVerifiers.IEligibilityVerifier}]}
        access(self) let dropVault: @FungibleToken.Vault

        pub fun claim(receiver: &{FungibleToken.Receiver}, params: {String: AnyStruct}) {
            let availability = self.checkAvailability(params: params)
            assert(availability.status == AvailabilityStatus.ok, message: availability.getStatus())

            let claimer = receiver.owner!.address
            let eligibility = self.checkEligibility(account: claimer, params: params)

            assert(eligibility.status == EligibilityStatus.eligible, message: eligibility.getStatus())

            let claimRecord = ClaimRecord(
                address: claimer,
                amount: eligibility.eligibleAmount,
                extraData: {}
            )

            self.claimedRecords.insert(key: claimRecord.address, claimRecord)
            self.claimedAmount = self.claimedAmount + claimRecord.amount

            emit DropClaimed(
                dropID: self.dropID,
                name: self.name,
                host: self.host,
                claimer: claimRecord.address,
                tokenIdentifier: self.tokenInfo.tokenIdentifier,
                amount: claimRecord.amount
            )

            let v <- self.dropVault.withdraw(amount: claimRecord.amount)
            receiver.deposit(from: <- v)
        }

        pub fun checkAvailability(params: {String: AnyStruct}): Availability {
            if self.isEnded {
                return Availability(
                    status: AvailabilityStatus.ended,
                    extraData: {}
                )
            }

            if let startAt = self.startAt {
                if getCurrentBlock().timestamp < startAt {
                    return Availability(
                        status: AvailabilityStatus.notStartYet,
                        extraData: {}
                    )
                }
            }

            if let endAt = self.endAt {
                if getCurrentBlock().timestamp > endAt {
                    return Availability(
                        status: AvailabilityStatus.expired,
                        extraData: {}
                    )
                }
            }

            let newParams: {String: AnyStruct} = self.combinedParams(params: params)
            if !self.distributor.isAvailable(params: newParams) {
                return Availability(
                    status: AvailabilityStatus.noCapacity,
                    extraData: {}
                )
            }

            if self.isPaused {
                return Availability(
                    status: AvailabilityStatus.paused,
                    extraData: {}
                ) 
            }

            return Availability(
                status: AvailabilityStatus.ok,
                extraData: {}
            ) 
        }

        pub fun checkEligibility(account: Address, params: {String: AnyStruct}): Eligibility {
            if let record = self.claimedRecords[account] {
                return Eligibility(
                    status: EligibilityStatus.hasClaimed,
                    eligibleAmount: record.amount,
                    extraData: {}
                )
            } 

            params.insert(key: "claimer", account)
            let newParams: {String: AnyStruct} = self.combinedParams(params: params)
            var isEligible = false
            if self.verifyMode == EligibilityVerifiers.VerifyMode.oneOf {
                for identifier in self.verifiers.keys {
                    let verifiers = self.verifiers[identifier]!
                    for verifier in verifiers {
                        if verifier.verify(account: account, params: newParams).isEligible {
                            isEligible = true
                            break
                        }
                    }
                    if isEligible {
                        break
                    }
                }
            } else if self.verifyMode == EligibilityVerifiers.VerifyMode.all {
                isEligible = true
                for identifier in self.verifiers.keys {
                    let verifiers = self.verifiers[identifier]!
                    for verifier in verifiers {
                        if !verifier.verify(account: account, params: newParams).isEligible {
                            isEligible = false
                            break
                        }
                    }
                    if !isEligible {
                        break
                    }
                }
            }

            let eligibleAmount = self.distributor.getEligibleAmount(params: newParams)

            return Eligibility(
                status: isEligible ? EligibilityStatus.eligible : EligibilityStatus.notEligible,
                eligibleAmount: eligibleAmount,
                extraData: {}
            )
        }

        pub fun getClaimedRecord(account: Address): ClaimRecord? {
            return self.claimedRecords[account]
        }

        pub fun getClaimedRecords(): {Address: ClaimRecord} {
            return self.claimedRecords
        }

        pub fun getDropBalance(): UFix64 {
            return self.dropVault.balance
        }

        pub fun getVerifiers(): {String: [{EligibilityVerifiers.IEligibilityVerifier}]} {
            return self.verifiers
        }

        // private methods

        pub fun togglePause(): Bool {
            pre { 
                !self.isEnded: "DROP has ended" 
            }

            self.isPaused = !self.isPaused
            if self.isPaused {
                emit DropPaused(dropID: self.dropID, name: self.name, host: self.host)
            } else {
                emit DropUnpaused(dropID: self.dropID, name: self.name, host: self.host)
            }
            return self.isPaused
        }

        // deposit more token into the DROP.
        // If the whitelist of a DROP is allowed to extend, we need
        // this function to make sure the claimers can have enough funds to withdraw.
        pub fun deposit(from: @FungibleToken.Vault) {
            pre {
                !self.isEnded: "DROP has ended"
                from.balance > 0.0: "deposit empty vault"
            }

            self.dropVault.deposit(from: <- from)
        }

        pub fun end(receiver: &{FungibleToken.Receiver}) {
            self.isEnded = true
            self.isPaused = true
            emit DropEnded(dropID: self.dropID, name: self.name, host: self.host)
            if self.dropVault.balance > 0.0 {
                let v <- self.dropVault.withdraw(amount: self.dropVault.balance)
                receiver.deposit(from: <- v)
            }
        }

        access(self) fun combinedParams(params: {String: AnyStruct}): {String: AnyStruct} {
            let combined: {String: AnyStruct} = {
                "claimedCount": UInt32(self.claimedRecords.keys.length),
                "claimedAmount": self.claimedAmount
            }

            for key in params.keys {
                if !combined.containsKey(key) {
                    combined[key] = params[key]
                }
            }
            return combined
        }

        init(
            name: String,
            description: String,
            host: Address,
            image: String?,
            url: String?,
            startAt: UFix64?,
            endAt: UFix64?,
            tokenInfo: TokenInfo,
            distributor: {Distributors.IDistributor},
            verifyMode: EligibilityVerifiers.VerifyMode,
            verifiers: {String: [{EligibilityVerifiers.IEligibilityVerifier}]},
            vault: @FungibleToken.Vault,
            extraData: {String: AnyStruct}
        ) {
            pre {
                name.length > 0: "invalid name"
            }

            // `tokenInfo` should match with `vault`
            let tokenVaultType = CompositeType(tokenInfo.providerIdentifier)!
            if !vault.isInstance(tokenVaultType) {
                panic("invalid token info: get ".concat(vault.getType().identifier)
                .concat(", want ").concat(tokenVaultType.identifier))
            }

            if let _startAt = startAt {
                if let _endAt = endAt {
                    assert(_startAt < _endAt, message: "endAt should greater than startAt")
                }
            }

            self.dropID = self.uuid
            self.name = name
            self.description = description
            self.host = host
            self.createdAt = getCurrentBlock().timestamp
            self.image = image
            self.url = url

            self.startAt = startAt
            self.endAt = endAt

            self.tokenInfo = tokenInfo

            self.distributor = distributor
            self.verifyMode = verifyMode
            self.verifiers = verifiers

            self.isPaused = false
            self.isEnded = false
            self.claimedRecords = {}
            self.claimedAmount = 0.0

            self.dropVault <- vault
            self.extraData = extraData

            Cloud.totalDrops = Cloud.totalDrops + 1
            emit DropCreated(
                dropID: self.dropID,
                name: self.name,
                host: self.host,
                description: self.description,
                tokenIdentifier: self.tokenInfo.tokenIdentifier
            )
        }

        destroy() {
            pre {
                self.dropVault.balance == 0.0: "dropVault is not empty, please withdraw all funds before delete DROP"
            }

            destroy self.dropVault
            emit DropDestroyed(dropID: self.dropID, name: self.name, host: self.host)
        }
    }

    pub resource interface ICloudPauser {
        pub fun toggleContractPause(): Bool
    }

    pub resource Admin: ICloudPauser {
        // Use to pause the creation of new DROP
        // If we want to migrate the contracts, we can make sure no more DROP in old contracts be created.
        pub fun toggleContractPause(): Bool {
            Cloud.isPaused = !Cloud.isPaused
            return Cloud.isPaused
        }
    }

    pub resource DropCollection: IDropCollectionPublic {
        pub var drops: @{UInt64: Drop}

        pub fun createDrop(
            name: String,
            description: String,
            host: Address,
            image: String?,
            url: String?,
            startAt: UFix64?,
            endAt: UFix64?,
            tokenInfo: TokenInfo,
            distributor: {Distributors.IDistributor},
            verifyMode: EligibilityVerifiers.VerifyMode,
            verifiers: [{EligibilityVerifiers.IEligibilityVerifier}],
            vault: @FungibleToken.Vault,
            extraData: {String: AnyStruct}
        ): UInt64 {
            pre {
                verifiers.length == 1: "Currently only 1 verifier supported"
                !Cloud.isPaused: "Cloud contract is paused!"
            }

            let typedVerifiers: {String: [{EligibilityVerifiers.IEligibilityVerifier}]} = {}
            for verifier in verifiers {
                let identifier = verifier.getType().identifier
                if typedVerifiers[identifier] == nil {
                    typedVerifiers[identifier] = [verifier]
                } else {
                    typedVerifiers[identifier]!.append(verifier)
                }
            }
            
            let drop <- create Drop(
                name: name, 
                description: description, 
                host: host,
                image: image,
                url: url,
                startAt: startAt,
                endAt: endAt,
                tokenInfo: tokenInfo,
                distributor: distributor,
                verifyMode: verifyMode,
                verifiers: typedVerifiers,
                vault: <- vault,
                extraData: extraData
            )

            let dropID = drop.dropID

            self.drops[dropID] <-! drop
            return dropID
        }

        pub fun getAllDrops(): {UInt64: &{IDropPublic}} {
            let dropRefs: {UInt64: &{IDropPublic}} = {}

            for dropID in self.drops.keys {
                let dropRef = (&self.drops[dropID] as &{IDropPublic}?)!
                dropRefs.insert(key: dropID, dropRef)
            }

            return dropRefs
        }

        pub fun borrowPublicDropRef(dropID: UInt64): &{IDropPublic}? {
            return &self.drops[dropID] as &{IDropPublic}?
        }

        pub fun borrowDropRef(dropID: UInt64): &Drop? {
            return &self.drops[dropID] as &Drop?
        }

        pub fun deleteDrop(dropID: UInt64, receiver: &{FungibleToken.Receiver}) {
            // Clean the Drop before make it ownerless
            let dropRef = self.borrowDropRef(dropID: dropID) ?? panic("This drop does not exist")
            dropRef.end(receiver: receiver)
            let drop <- self.drops.remove(key: dropID) ?? panic("This drop does not exist")
            destroy drop
        }

        destroy() {
            destroy self.drops
        }

        init() {
            self.drops <- {}
        }
    }

    pub fun createEmptyDropCollection(): @DropCollection {
        return <- create DropCollection()
    }

    pub var isPaused: Bool
    pub var totalDrops: UInt64

    init() {
        self.DropCollectionStoragePath = /storage/drizzleDropCollection
        self.DropCollectionPublicPath = /public/drizzleDropCollection
        self.DropCollectionPrivatePath = /private/drizzleDropCollection

        self.CloudAdminStoragePath = /storage/drizzleCloudAdmin
        self.CloudAdminPublicPath = /public/drizzleCloudAdmin
        self.CloudAdminPrivatePath = /private/drizzleCloudAdmin

        self.isPaused = false
        self.totalDrops = 0

        self.account.save(<- create Admin(), to: self.CloudAdminStoragePath)

        emit ContractInitialized()
    }
}