// Made by Lanford33
//
// Mist.cdc defines the NFT Raffle and the collections of it.
//
// There are 4 stages in a NFT Raffle. 
// 1. You create a new NFT Raffle by setting the basic information, depositing NFTs and setting the criteria for eligible accounts, then share the Raffle link to your community;
// 2. Community members go to the Raffle page, check their eligibility and register for the Raffle if they are eligible;
// 3. Once the registration end, you can draw the winners. For each draw, a winner will be selected randomly from registrants, and an NFT will be picked out randomly from NFTs in the Raffle as the reward for winner;
// 4. Registrants go to the Raffle page to check whether they are winners or not, and claim the reward if they are.

import NonFungibleToken from "./core/NonFungibleToken.cdc"
import EligibilityVerifiers from "./EligibilityVerifiers.cdc"

pub contract Mist {

    pub let MistAdminStoragePath: StoragePath
    pub let MistAdminPublicPath: PublicPath
    pub let MistAdminPrivatePath: PrivatePath

    pub let RaffleCollectionStoragePath: StoragePath
    pub let RaffleCollectionPublicPath: PublicPath
    pub let RaffleCollectionPrivatePath: PrivatePath

    pub event ContractInitialized()

    pub event RaffleCreated(raffleID: UInt64, name: String, host: Address, description: String, nftIdentifier: String)
    pub event RaffleRegistered(raffleID: UInt64, name: String, host: Address, registrator: Address, nftIdentifier: String)
    pub event RaffleWinnerDrawn(raffleID: UInt64, name: String, host: Address, winner: Address, nftIdentifier: String, tokenIDs: [UInt64])
    pub event RaffleClaimed(raffleID: UInt64, name: String, host: Address, claimer: Address, nftIdentifier: String, tokenIDs: [UInt64])
    pub event RafflePaused(raffleID: UInt64, name: String, host: Address)
    pub event RaffleUnpaused(raffleID: UInt64, name: String, host: Address)
    pub event RaffleEnded(raffleID: UInt64, name: String, host: Address)
    pub event RaffleDestroyed(raffleID: UInt64, name: String, host: Address)

    pub enum AvailabilityStatus: UInt8 {
        pub case notStartYet
        pub case ended
        pub case registering
        pub case drawing
        pub case drawn
        pub case expired
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
            case AvailabilityStatus.notStartYet:
                return "not start yet"
            case AvailabilityStatus.ended:
                return "ended"
            case AvailabilityStatus.registering:
                return "registering"
            case AvailabilityStatus.drawing:
                return "drawing"
            case AvailabilityStatus.drawn:
                return "drawn"
            case AvailabilityStatus.expired:
                return "expired"
            case AvailabilityStatus.paused:
                return "paused"
            }
            panic("invalid status")
        }
    }

    pub enum EligibilityStatus: UInt8 {
        pub case eligibleForRegistering
        pub case eligibleForClaiming

        pub case notEligibleForRegistering
        pub case notEligibleForClaiming

        pub case hasRegistered
        pub case hasClaimed
    }

    pub struct Eligibility {
        pub let status: EligibilityStatus
        pub let eligibleNFTs: [UInt64]
        pub let extraData: {String: AnyStruct}

        init(
            status: EligibilityStatus, 
            eligibleNFTs: [UInt64],
            extraData: {String: AnyStruct}) {
            self.status = status
            self.eligibleNFTs = eligibleNFTs
            self.extraData = extraData
        }

        pub fun getStatus(): String {
            switch self.status {
            case EligibilityStatus.eligibleForRegistering: 
                return "eligible for registering"
            case EligibilityStatus.eligibleForClaiming:
                return "eligible for claiming"
            case EligibilityStatus.notEligibleForRegistering:
                return "not eligible for registering"
            case EligibilityStatus.notEligibleForClaiming:
                return "not eligible for claiming"
            case EligibilityStatus.hasRegistered:
                return "has registered"
            case EligibilityStatus.hasClaimed:
                return "has claimed" 
            }
            panic("invalid status")
        }
    }

    // We want to get the thumbnail uri directly from Raffle
    // so we define NFTDisplay rather than use MetadataViews.Display
    pub struct NFTDisplay {
        pub let tokenID: UInt64
        pub let name: String
        pub let description: String
        pub let thumbnail: String

        init(tokenID: UInt64, name: String, description: String, thumbnail: String) {
            self.tokenID = tokenID
            self.name = name
            self.description = description
            self.thumbnail = thumbnail
        }
    }

    pub struct RegistrationRecord {
        pub let address: Address
        pub let extraData: {String: AnyStruct}

        init(address: Address, extraData: {String: AnyStruct}) {
            self.address = address
            self.extraData = extraData
        }
    }

    pub struct WinnerRecord {
        pub let address: Address
        pub let rewardTokenIDs: [UInt64]
        pub let extraData: {String: AnyStruct}
        pub var isClaimed: Bool

        access(contract) fun markAsClaimed() {
            self.isClaimed = true
            self.extraData["claimedAt"] = getCurrentBlock().timestamp
        }

        init(
            address: Address, 
            rewardTokenIDs: [UInt64],
            extraData: {String: AnyStruct}
        ) {
            self.address = address
            self.rewardTokenIDs = rewardTokenIDs
            self.extraData = extraData
            self.isClaimed = false
        }
    }

    pub struct NFTInfo {
        pub let name: String
        pub let nftType: Type
        pub let contractName : String
        pub let contractAddress : Address
        pub let collectionType: Type
        pub let collectionLogoURL: String
        pub let collectionStoragePath : StoragePath
        pub let collectionPublicPath : PublicPath

        init(
            name: String,
            nftType: Type,
            contractName: String,
            contractAddress: Address,
            collectionType: Type,
            collectionLogoURL: String,
            collectionStoragePath: StoragePath,
            collectionPublicPath: PublicPath
        ) {
            self.name = name
            self.nftType = nftType
            self.contractName = contractName
            self.contractAddress = contractAddress
            self.collectionType = collectionType
            self.collectionLogoURL = collectionLogoURL
            self.collectionStoragePath = collectionStoragePath
            self.collectionPublicPath = collectionPublicPath
        }
    }

    pub resource interface IRafflePublic {
        pub let raffleID: UInt64
        pub let name: String
        pub let description: String
        pub let host: Address
        pub let createdAt: UFix64
        pub let image: String?
        pub let url: String?
        pub let startAt: UFix64?
        pub let endAt: UFix64?

        pub let registrationEndAt: UFix64
        pub let numberOfWinners: UInt64

        pub let nftInfo: NFTInfo

        pub let registrationVerifyMode: EligibilityVerifiers.VerifyMode
        pub let claimVerifyMode: EligibilityVerifiers.VerifyMode

        pub var isPaused: Bool
        pub var isEnded: Bool

        pub let extraData: {String: AnyStruct}

        pub fun register(account: Address, params: {String: AnyStruct})
        pub fun hasRegistered(account: Address): Bool
        pub fun getRegistrationRecords(): {Address: RegistrationRecord}
        pub fun getRegistrationRecord(account: Address): RegistrationRecord?

        pub fun getWinners(): {Address: WinnerRecord}
        pub fun getWinner(account: Address): WinnerRecord?

        pub fun claim(receiver: &{NonFungibleToken.CollectionPublic}, params: {String: AnyStruct})
        pub fun checkAvailability(params: {String: AnyStruct}): Availability
        pub fun checkRegistrationEligibility(account: Address, params: {String: AnyStruct}): Eligibility
        pub fun checkClaimEligibility(account: Address, params: {String: AnyStruct}): Eligibility

        pub fun getRegistrationVerifiers(): {String: [{EligibilityVerifiers.IEligibilityVerifier}]}
        pub fun getClaimVerifiers(): {String: [{EligibilityVerifiers.IEligibilityVerifier}]}
        
        pub fun getRewardDisplays(): {UInt64: NFTDisplay}
    }

    pub resource Raffle: IRafflePublic {
        pub let raffleID: UInt64
        pub let name: String
        pub let description: String
        pub let host: Address
        pub let createdAt: UFix64
        pub let image: String?
        pub let url: String?
        pub let startAt: UFix64?
        pub let endAt: UFix64?

        pub let registrationEndAt: UFix64
        pub let numberOfWinners: UInt64

        pub let nftInfo: NFTInfo

        pub let registrationVerifyMode: EligibilityVerifiers.VerifyMode
        pub let claimVerifyMode: EligibilityVerifiers.VerifyMode

        pub var isPaused: Bool
        // After a Raffle ended, it can't be recovered.
        pub var isEnded: Bool

        pub let extraData: {String: AnyStruct}

        // Check an account is eligible for registration or not
        access(account) let registrationVerifiers: {String: [{EligibilityVerifiers.IEligibilityVerifier}]}
        // Check a winner account is eligible for claiming the reward or not
        // This is mainly used to allow the host add some extra requirements to the winners
        access(account) let claimVerifiers: {String: [{EligibilityVerifiers.IEligibilityVerifier}]}
        access(self) let collection: @NonFungibleToken.Collection
        // The information of registrants
        access(self) let registrationRecords: {Address: RegistrationRecord}
        // The information of winners
        access(self) let winners: {Address: WinnerRecord}
        // Candidates stores the accounts of registrants. It's a helper field to make drawing easy
        access(self) let candidates: [Address]
        // nftToBeDrawn stores the tokenIDs of undrawn NFTs. It's a helper field to make drawing easy
        access(self) let nftToBeDrawn: [UInt64]
        // rewardDisplays stores the Display of NFTs added to this Raffle. Once an NFT added as reward, the Display will be recorded here.
        // No item will be deleted from this field.
        access(self) let rewardDisplays: {UInt64: NFTDisplay}

        pub fun register(account: Address, params: {String: AnyStruct}) {
            let availability = self.checkAvailability(params: params)
            assert(availability.status == AvailabilityStatus.registering, message: availability.getStatus())

            let eligibility = self.checkRegistrationEligibility(account: account, params: params)
            assert(eligibility.status == EligibilityStatus.eligibleForRegistering, message: eligibility.getStatus())

            emit RaffleRegistered(
                raffleID: self.raffleID, 
                name: self.name, 
                host: self.host, 
                registrator: account, 
                nftIdentifier: self.nftInfo.nftType.identifier
            )

            self.registrationRecords[account] = RegistrationRecord(address: account, extraData: {})
            self.candidates.append(account)
        }

        pub fun hasRegistered(account: Address): Bool {
            return self.registrationRecords[account] != nil
        }

        pub fun getRegistrationRecords(): {Address: RegistrationRecord} {
            return self.registrationRecords
        }

        pub fun getRegistrationRecord(account: Address): RegistrationRecord? {
            return self.registrationRecords[account]
        }

        pub fun getWinners(): {Address: WinnerRecord} {
            return self.winners
        }

        pub fun getWinner(account: Address): WinnerRecord? {
            return self.winners[account]
        }

        pub fun claim(receiver: &{NonFungibleToken.CollectionPublic}, params: {String: AnyStruct}) {
            let availability = self.checkAvailability(params: params)
            assert(availability.status == AvailabilityStatus.drawn || availability.status == AvailabilityStatus.drawing, message: availability.getStatus())

            let claimer = receiver.owner!.address
            let eligibility = self.checkClaimEligibility(account: claimer, params: params)
            assert(eligibility.status == EligibilityStatus.eligibleForClaiming, message: eligibility.getStatus())

            self.winners[claimer]!.markAsClaimed()
            let winnerRecord = self.winners[claimer]!

            emit RaffleClaimed(
                raffleID: self.raffleID, 
                name: self.name, 
                host: self.host, 
                claimer: claimer, 
                nftIdentifier: self.nftInfo.nftType.identifier, 
                tokenIDs: winnerRecord.rewardTokenIDs
            )

            for tokenID in winnerRecord.rewardTokenIDs {
                let nft <- self.collection.withdraw(withdrawID: tokenID)
                receiver.deposit(token: <- nft)
            }
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

            if self.isPaused {
                return Availability(
                    status: AvailabilityStatus.paused,
                    extraData: {}
                ) 
            }

            assert(UInt64(self.winners.keys.length) <= self.numberOfWinners, message: "invalid winners")

            if (UInt64(self.winners.keys.length) == self.numberOfWinners) {
                return Availability(
                    status: AvailabilityStatus.drawn,
                    extraData: {}
                )
            }

            if getCurrentBlock().timestamp > self.registrationEndAt {
                if self.candidates.length == 0 {
                    return Availability(
                        status: AvailabilityStatus.drawn,
                        extraData: {} 
                    ) 
                }
                return Availability(
                    status: AvailabilityStatus.drawing,
                    extraData: {} 
                )
            }

            return Availability(
                status: AvailabilityStatus.registering,
                extraData: {}
            )
        }

        pub fun checkRegistrationEligibility(account: Address, params: {String: AnyStruct}): Eligibility {
            if let record = self.registrationRecords[account] {
                return Eligibility(
                    status: EligibilityStatus.hasRegistered,
                    eligibleNFTs: [],
                    extraData: {}
                )
            }

            let isEligible = self.isEligible(
                account: account,
                mode: self.registrationVerifyMode,
                verifiers: self.registrationVerifiers,
                params: params
            ) 

            return Eligibility(
                status: isEligible ? 
                    EligibilityStatus.eligibleForRegistering : 
                    EligibilityStatus.notEligibleForRegistering,
                eligibleNFTs: [],
                extraData: {}
            )
        }

        pub fun checkClaimEligibility(account: Address, params: {String: AnyStruct}): Eligibility {
            if self.winners[account] == nil {
                return Eligibility(
                    status: EligibilityStatus.notEligibleForClaiming,
                    eligibleNFTs: [],
                    extraData: {}
                )
            }

            let record = self.winners[account]!
            if record.isClaimed {
                return Eligibility(
                    status: EligibilityStatus.hasClaimed,
                    eligibleNFTs: record.rewardTokenIDs,
                    extraData: {}
                ) 
            }

            // Raffle host can add extra requirements to the winners for claiming the NFTs
            // by adding claimVerifiers
            let isEligible = self.isEligible(
                account: account,
                mode: self.claimVerifyMode,
                verifiers: self.claimVerifiers,
                params: params
            ) 

            return Eligibility(
                status: isEligible ? 
                    EligibilityStatus.eligibleForClaiming: 
                    EligibilityStatus.eligibleForClaiming,
                eligibleNFTs: record.rewardTokenIDs,
                extraData: {}
            ) 
        }

        pub fun getRegistrationVerifiers(): {String: [{EligibilityVerifiers.IEligibilityVerifier}]} {
            return self.registrationVerifiers
        }

        pub fun getClaimVerifiers(): {String: [{EligibilityVerifiers.IEligibilityVerifier}]} {
            return self.claimVerifiers
        }

        pub fun getRewardDisplays(): {UInt64: NFTDisplay} {
            return self.rewardDisplays
        }

        access(self) fun isEligible(
            account: Address,
            mode: EligibilityVerifiers.VerifyMode, 
            verifiers: {String: [{EligibilityVerifiers.IEligibilityVerifier}]},
            params: {String: AnyStruct}
        ): Bool {
            params.insert(key: "claimer", account)
            if mode == EligibilityVerifiers.VerifyMode.oneOf {
                for identifier in verifiers.keys {
                    let verifiers = verifiers[identifier]!
                    for verifier in verifiers {
                        if verifier.verify(account: account, params: params).isEligible {
                            return true
                        }
                    }
                }
                return false
            } 
            
            if mode == EligibilityVerifiers.VerifyMode.all {
                for identifier in verifiers.keys {
                    let verifiers = verifiers[identifier]!
                    for verifier in verifiers {
                        if !verifier.verify(account: account, params: params).isEligible {
                            return false
                        }
                    }
                }
                return true
            }
            panic("invalid mode: ".concat(mode.rawValue.toString()))
        }

        pub fun draw(params: {String: AnyStruct}) {
            let availability = self.checkAvailability(params: params)
            assert(availability.status == AvailabilityStatus.drawing, message: availability.getStatus())

            let capacity = self.numberOfWinners - UInt64(self.winners.keys.length)
            let upperLimit = capacity > UInt64(self.candidates.length) ?
                UInt64(self.candidates.length) : capacity
            assert(UInt64(self.nftToBeDrawn.length) >= upperLimit, message: "nft is not enough")

            let winnerIndex = unsafeRandom() % UInt64(self.candidates.length)
            let winner = self.candidates[winnerIndex]

            assert(self.winners[winner] == nil, message: "winner already recorded")

            let rewardIndex = unsafeRandom() % UInt64(self.nftToBeDrawn.length)
            let rewardTokenID = self.nftToBeDrawn[rewardIndex]

            let winnerRecord = WinnerRecord(
                address: winner, 
                rewardTokenIDs: [rewardTokenID],
                extraData: {}
            )
            self.winners[winner] = winnerRecord

            self.candidates.remove(at: winnerIndex)
            self.nftToBeDrawn.remove(at: rewardIndex)

            emit RaffleWinnerDrawn(
                raffleID: self.raffleID, 
                name: self.name, 
                host: self.host, 
                winner: winner, 
                nftIdentifier: self.nftInfo.nftType.identifier, 
                tokenIDs: [rewardTokenID]
            )
        }

        pub fun batchDraw(params: {String: AnyStruct}) {
            let availability = self.checkAvailability(params: params)
            assert(availability.status == AvailabilityStatus.drawing, message: availability.getStatus())

            let capacity = self.numberOfWinners - UInt64(self.winners.keys.length)
            let upperLimit = capacity > UInt64(self.candidates.length) ?
                UInt64(self.candidates.length) : capacity
            assert(UInt64(self.nftToBeDrawn.length) >= upperLimit, message: "nft is not enough")

            var counter: UInt64 = 0
            while counter < upperLimit {
                let winnerIndex = unsafeRandom() % UInt64(self.candidates.length)
                let winner = self.candidates[winnerIndex]

                assert(self.winners[winner] == nil, message: "winner already recorded")

                let rewardIndex = unsafeRandom() % UInt64(self.nftToBeDrawn.length)
                let rewardTokenID = self.nftToBeDrawn[rewardIndex]

                let winnerRecord = WinnerRecord(
                    address: winner, 
                    rewardTokenIDs: [rewardTokenID],
                    extraData: {}
                )
                self.winners[winner] = winnerRecord

                self.candidates.remove(at: winnerIndex)
                self.nftToBeDrawn.remove(at: rewardIndex)
                counter = counter + 1
            }
        }

        // private methods

        pub fun togglePause(): Bool {
            pre { 
                !self.isEnded: "Raffle has ended" 
            }

            self.isPaused = !self.isPaused
            if self.isPaused {
                emit RafflePaused(raffleID: self.raffleID, name: self.name, host: self.host)
            } else {
                emit RaffleUnpaused(raffleID: self.raffleID, name: self.name, host: self.host)
            }
            return self.isPaused
        }

        // deposit more NFT into the Raffle
        pub fun deposit(token: @NonFungibleToken.NFT, display: NFTDisplay) {
            pre {
                !self.isEnded: "Raffle has ended"
            }

            let tokenID = token.id
            self.collection.deposit(token: <- token)
            self.nftToBeDrawn.append(tokenID)
            self.rewardDisplays[tokenID] = display
        }

        pub fun end(receiver: &{NonFungibleToken.CollectionPublic}) {
            self.isEnded = true
            self.isPaused = true
            emit RaffleEnded(raffleID: self.raffleID, name: self.name, host: self.host)
            let tokenIDs = self.collection.getIDs()
            for tokenID in tokenIDs {
                let token <- self.collection.withdraw(withdrawID: tokenID)
                receiver.deposit(token: <- token)
            }
        }

        init(
            name: String,
            description: String,
            host: Address,
            image: String?,
            url: String?,
            startAt: UFix64?,
            endAt: UFix64?,
            registrationEndAt: UFix64, 
            numberOfWinners: UInt64,
            nftInfo: NFTInfo,
            collection: @NonFungibleToken.Collection,
            registrationVerifyMode: EligibilityVerifiers.VerifyMode,
            claimVerifyMode: EligibilityVerifiers.VerifyMode,
            registrationVerifiers: {String: [{EligibilityVerifiers.IEligibilityVerifier}]},
            claimVerifiers: {String: [{EligibilityVerifiers.IEligibilityVerifier}]},
            extraData: {String: AnyStruct} 
        ) {
            if !collection.isInstance(nftInfo.collectionType) {
                panic("invalid nft info: get ".concat(collection.getType().identifier)
                .concat(", want ").concat(nftInfo.collectionType.identifier))
            }

            if let _startAt = startAt {
                if let _endAt = endAt {
                    assert(_startAt < _endAt, message: "endAt should greater than startAt")
                    assert(registrationEndAt < _endAt, message: "registrationEndAt should smaller than endAt")
                }
                assert(registrationEndAt > _startAt, message: "registrationEndAt should greater than startAt")
            }

            self.raffleID = self.uuid
            self.name = name
            self.description = description
            self.createdAt = getCurrentBlock().timestamp
            self.host = host
            self.image = image
            self.url = url

            self.startAt = startAt
            self.endAt = endAt

            self.registrationEndAt = registrationEndAt
            self.numberOfWinners = numberOfWinners

            self.nftInfo = nftInfo
            self.collection <- collection

            self.registrationVerifyMode = registrationVerifyMode
            self.claimVerifyMode = claimVerifyMode
            self.registrationVerifiers = registrationVerifiers
            self.claimVerifiers = claimVerifiers

            self.extraData = extraData

            self.isPaused = false
            self.isEnded = false

            self.registrationRecords = {}
            self.candidates = []
            self.winners = {}
            self.nftToBeDrawn = []
            self.rewardDisplays = {}

            Mist.totalRaffles = Mist.totalRaffles + 1
            emit RaffleCreated(
                raffleID: self.raffleID, 
                name: self.name, 
                host: self.host, 
                description: self.description, 
                nftIdentifier: self.nftInfo.nftType.identifier 
            )
        }

        destroy() {
            pre {
                self.collection.getIDs().length == 0: "collection is not empty, please withdraw all NFTs before delete Raffle"
            }

            destroy self.collection
            emit RaffleDestroyed(raffleID: self.raffleID, name: self.name, host: self.host)
        }
    }

    pub resource interface IMistPauser {
        pub fun toggleContractPause(): Bool
    }

    pub resource Admin: IMistPauser {
        // Use to pause the creation of new Raffle
        // If we want to migrate the contracts, we can make sure no more Raffle in old contracts be created.
        pub fun toggleContractPause(): Bool {
            Mist.isPaused = !Mist.isPaused
            return Mist.isPaused
        }
    }

    pub resource interface IRaffleCollectionPublic {
        pub fun getAllRaffles(): {UInt64: &{IRafflePublic}}
        pub fun borrowPublicRaffleRef(raffleID: UInt64): &{IRafflePublic}?
    }

    pub resource RaffleCollection: IRaffleCollectionPublic {
        pub var raffles: @{UInt64: Raffle}

        pub fun createRaffle(
            name: String,
            description: String,
            host: Address,
            image: String?,
            url: String?,
            startAt: UFix64?,
            endAt: UFix64?,
            registrationEndAt: UFix64, 
            numberOfWinners: UInt64,
            nftInfo: NFTInfo,
            collection: @NonFungibleToken.Collection,
            registrationVerifyMode: EligibilityVerifiers.VerifyMode,
            claimVerifyMode: EligibilityVerifiers.VerifyMode,
            registrationVerifiers: [{EligibilityVerifiers.IEligibilityVerifier}],
            claimVerifiers: [{EligibilityVerifiers.IEligibilityVerifier}],
            extraData: {String: AnyStruct} 
        ): UInt64 {
            pre {
                registrationVerifiers.length <= 1: "Currently only 0 or 1 registration verifier supported"
                claimVerifiers.length <= 1: "Currently only 0 or 1 registration verifier supported"
                !Mist.isPaused: "Mist contract is paused!"
            }

            let typedRegistrationVerifiers: {String: [{EligibilityVerifiers.IEligibilityVerifier}]} = {}
            for verifier in registrationVerifiers {
                let identifier = verifier.getType().identifier
                if typedRegistrationVerifiers[identifier] == nil {
                    typedRegistrationVerifiers[identifier] = [verifier]
                } else {
                    typedRegistrationVerifiers[identifier]!.append(verifier)
                }
            }

            let typedClaimVerifiers: {String: [{EligibilityVerifiers.IEligibilityVerifier}]} = {}
            for verifier in claimVerifiers {
                let identifier = verifier.getType().identifier
                if typedClaimVerifiers[identifier] == nil {
                    typedClaimVerifiers[identifier] = [verifier]
                } else {
                    typedClaimVerifiers[identifier]!.append(verifier)
                }
            }

            let raffle <- create Raffle(
                name: name,
                description: description,
                host: host,
                image: image,
                url: url,
                startAt: startAt,
                endAt: endAt,
                registrationEndAt: registrationEndAt,
                numberOfWinners: numberOfWinners,
                nftInfo: nftInfo,
                collection: <- collection,
                registrationVerifyMode: registrationVerifyMode,
                claimVerifyMode: claimVerifyMode,
                registrationVerifiers: typedRegistrationVerifiers,
                claimVerifiers: typedClaimVerifiers,
                extraData: extraData
            )

            let raffleID = raffle.raffleID

            self.raffles[raffleID] <-! raffle
            return raffleID
        }

        pub fun getAllRaffles(): {UInt64: &{IRafflePublic}} {
            let raffleRefs: {UInt64: &{IRafflePublic}} = {}

            for raffleID in self.raffles.keys {
                let raffleRef = (&self.raffles[raffleID] as &{IRafflePublic}?)!
                raffleRefs.insert(key: raffleID, raffleRef)
            }

            return raffleRefs
        }

        pub fun borrowPublicRaffleRef(raffleID: UInt64): &{IRafflePublic}? {
            return &self.raffles[raffleID] as &{IRafflePublic}?
        }

        pub fun borrowRaffleRef(raffleID: UInt64): &Raffle? {
            return &self.raffles[raffleID] as &Raffle?
        }

        pub fun deleteRaffle(raffleID: UInt64, receiver: &{NonFungibleToken.CollectionPublic}) {
            // Clean the Raffle before make it ownerless
            let raffleRef = self.borrowRaffleRef(raffleID: raffleID) ?? panic("This raffle does not exist")
            raffleRef.end(receiver: receiver)
            let raffle <- self.raffles.remove(key: raffleID) ?? panic("This raffle does not exist")
            destroy raffle
        }

        init() {
            self.raffles <- {}
        }

        destroy() {
            destroy self.raffles
        }
    }

    pub fun createEmptyRaffleCollection(): @RaffleCollection {
        return <- create RaffleCollection()
    }

    pub var isPaused: Bool
    pub var totalRaffles: UInt64

    init() {
        self.RaffleCollectionStoragePath = /storage/drizzleRaffleCollection
        self.RaffleCollectionPublicPath = /public/drizzleRaffleCollection
        self.RaffleCollectionPrivatePath = /private/drizzleRaffleCollection

        self.MistAdminStoragePath = /storage/drizzleMistAdmin
        self.MistAdminPublicPath = /public/drizzleMistAdmin
        self.MistAdminPrivatePath = /private/drizzleMistAdmin

        self.isPaused = false
        self.totalRaffles = 0

        self.account.save(<- create Admin(), to: self.MistAdminStoragePath)

        emit ContractInitialized()
    }
}