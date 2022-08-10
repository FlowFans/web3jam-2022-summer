import FungibleToken from "../contracts/core/FungibleToken.cdc"
import Cloud from "../contracts/Cloud.cdc"
import EligibilityVerifiers from "../contracts/EligibilityVerifiers.cdc"
import Distributors from "../contracts/Distributors.cdc"

transaction(
    name: String,
    description: String,
    image: String?,
    url: String?,
    startAt: UFix64?,
    endAt: UFix64?,
    // TokenInfo
    tokenIssuer: Address,
    tokenContractName: String,
    tokenSymbol: String,
    tokenProviderPath: String,
    tokenBalancePath: String,
    tokenReceiverPath: String,
    // EligibilityVerifier
    // Distributor
    withExclusiveWhitelist: Bool,
    exclusiveWhitelist: {Address: UFix64},
    whitelistTokenAmount: UFix64?,

    withWhitelist: Bool,
    whitelist: {Address: Bool},

    withIdenticalDistributor: Bool,
    capacity: UInt32?,
    amountPerEntry: UFix64?,

    withRandomDistributor: Bool,
    totalRandomAmount: UFix64?,

    withFloats: Bool,
    threshold: UInt32?,
    eventIDs: [UInt64],
    eventHosts: [Address],

    withFloatGroup: Bool,
    floatGroupName: String?,
    floatGroupHost: Address?
) {
    let dropCollection: &Cloud.DropCollection
    let vault: &FungibleToken.Vault

    prepare(acct: AuthAccount) {
        if acct.borrow<&Cloud.DropCollection>(from: Cloud.DropCollectionStoragePath) == nil {
            acct.save(<- Cloud.createEmptyDropCollection(), to: Cloud.DropCollectionStoragePath)
            let cap = acct.link<&Cloud.DropCollection{Cloud.IDropCollectionPublic}>(
                Cloud.DropCollectionPublicPath,
                target: Cloud.DropCollectionStoragePath
            ) ?? panic("Could not link DropCollection to PublicPath")
        }

        self.dropCollection = acct.borrow<&Cloud.DropCollection>(from: Cloud.DropCollectionStoragePath)
            ?? panic("Could not borrow DropCollection from signer")

        let providerPath = StoragePath(identifier: tokenProviderPath)!
        self.vault = acct.borrow<&FungibleToken.Vault>(from: providerPath)
            ?? panic("Could not borrow Vault from signer")
    }

    execute {
        let tokenInfo = Cloud.TokenInfo(
            account: tokenIssuer,
            contractName: tokenContractName,
            symbol: tokenSymbol,
            providerPath: tokenProviderPath,
            balancePath: tokenBalancePath,
            receiverPath: tokenReceiverPath
        )
        
        var amount: UFix64 = 0.0
        var distributor: {Distributors.IDistributor}? = nil
        if withExclusiveWhitelist {
            distributor = Distributors.Exclusive(distributeList: exclusiveWhitelist)
            amount = whitelistTokenAmount!
        } else if withIdenticalDistributor {
            distributor = Distributors.Identical(
                capacity: capacity!,
                amountPerEntry: amountPerEntry!
            )
            amount = UFix64(capacity!) * amountPerEntry!
        } else if withRandomDistributor {
            distributor = Distributors.Random(
                capacity: capacity!,
                totalAmount: totalRandomAmount!
            )
            amount = totalRandomAmount!
        } else {
            panic("invalid distributor")
        }
        
        var verifier: {EligibilityVerifiers.IEligibilityVerifier}? = nil
        if withExclusiveWhitelist {
            verifier = EligibilityVerifiers.Whitelist(
                whitelist: exclusiveWhitelist
            )
        } else if withWhitelist {
            verifier = EligibilityVerifiers.Whitelist(
                whitelist: whitelist
            )
        } else if withFloats {
            assert(eventIDs.length == eventHosts.length, message: "eventIDs should have the same length with eventHosts")
            let events: [EligibilityVerifiers.FLOATEventData] = []
            var counter = 0
            while counter < eventIDs.length {
                let event = EligibilityVerifiers.FLOATEventData(host: eventHosts[counter], eventID: eventIDs[counter])
                events.append(event)
                counter = counter + 1
            }
            verifier = EligibilityVerifiers.FLOATs(
                events: events,
                threshold: threshold!
            )
        } else if withFloatGroup {
            let groupData = EligibilityVerifiers.FLOATGroupData(
                host: floatGroupHost!,
                name: floatGroupName!
            )
            verifier = EligibilityVerifiers.FLOATGroup(
                group: groupData,
                threshold: threshold!
            )
        } else {
            panic("invalid verifier")
        }

        self.dropCollection.createDrop(
            name: name, 
            description: description, 
            host: self.vault.owner!.address, 
            image: image,
            url: url,
            startAt: startAt,
            endAt: endAt,
            tokenInfo: tokenInfo,
            distributor: distributor!,
            verifyMode: EligibilityVerifiers.VerifyMode.all,
            verifiers: [verifier!], 
            vault: <- self.vault.withdraw(amount: amount),
            extraData: {}
        )
    }
}