// EligibilityVerifiers are used to check the eligibility of accounts
//
// With drizzle, you can decide who is eligible for your rewards by using our different modes.
// 1. FLOAT Event. You can limit the eligibility to people who own FLOATs of specific FLOAT Event at the time of the DROP being created.
// 2. FLOAT Group. You can also limit the eligibility to people who own FLOATs in a FLOAT Group. You can set a threshold to the number of FLOATs the users should have.
// 3. Whitelist. You can upload a whitelist. Only accounts on the whitelist are eligible for rewards.

import FungibleToken from "./core/FungibleToken.cdc"
import FLOAT from "./float/FLOAT.cdc"

pub contract EligibilityVerifiers {

    pub enum VerifyMode: UInt8 {
        pub case oneOf
        pub case all
    }

    pub struct VerifyResult {
        pub let isEligible: Bool
        pub let extraData: {String: AnyStruct}

        init(isEligible: Bool, extraData: {String: AnyStruct}) {
            self.isEligible = isEligible
            self.extraData = extraData
        }
    }

    pub struct interface IEligibilityVerifier {
        pub let type: String

        pub fun verify(account: Address, params: {String: AnyStruct}): VerifyResult
    }

    pub struct FLOATEventData {
        pub let host: Address
        pub let eventID: UInt64

        init(host: Address, eventID: UInt64) {
            self.host = host
            self.eventID = eventID
        }
    }

    pub struct FLOATGroupData {
        pub let host: Address
        pub let name: String

        init(host: Address, name: String) {
            self.host = host
            self.name = name
        }
    }

    pub struct Whitelist: IEligibilityVerifier {
        pub let whitelist: {Address: AnyStruct}
        pub let type: String

        init(whitelist: {Address: AnyStruct}) {
            self.whitelist = whitelist
            self.type = "Whitelist"
        }

        pub fun verify(account: Address, params: {String: AnyStruct}): VerifyResult {
            return VerifyResult(
                isEligible: self.whitelist[account] != nil,
                extraData: {}
            )
        }
    }

    pub struct FLOATGroup: IEligibilityVerifier {
        pub let group: FLOATGroupData
        pub let threshold: UInt32
        pub let receivedBefore: UFix64
        pub let type: String

        init(
            group: FLOATGroupData, 
            threshold: UInt32,
        ) {
            pre {
                threshold > 0: "threshold should greater than 0"
            }

            self.group = group
            self.threshold = threshold
            // The FLOAT should be received before this DROP be created
            // or the users can transfer their FLOATs and claim again
            self.receivedBefore = getCurrentBlock().timestamp
            self.type = "FLOATGroup"
        }

        pub fun verify(account: Address, params: {String: AnyStruct}): VerifyResult {
            let floatEventCollection = getAccount(self.group.host)
                .getCapability(FLOAT.FLOATEventsPublicPath)
                .borrow<&FLOAT.FLOATEvents{FLOAT.FLOATEventsPublic}>()
                ?? panic("Could not borrow the FLOAT Events Collection from the account.")
            
            let group = floatEventCollection.getGroup(groupName: self.group.name) 
                ?? panic("This group doesn't exist.")
            let eventIDs = group.getEvents()

            let floatCollection = getAccount(account)
                .getCapability(FLOAT.FLOATCollectionPublicPath)
                .borrow<&FLOAT.Collection{FLOAT.CollectionPublic}>()

            if floatCollection == nil {
                return VerifyResult(isEligible: false, extraData: {})
            } 

            var validCount: UInt32 = 0
            for eventID in eventIDs {
                let ownedIDs = floatCollection!.ownedIdsFromEvent(eventId: eventID)
                for ownedEventID in ownedIDs {
                    if let float = floatCollection!.borrowFLOAT(id: ownedEventID) {
                        if float.dateReceived <= self.receivedBefore {
                            validCount = validCount + 1
                            if validCount >= self.threshold {
                                return VerifyResult(isEligible: true, extraData: {})
                            }
                        }
                    }
                }
            }
            return VerifyResult(isEligible: false, extraData: {})
        }
    }

    pub struct FLOATs: IEligibilityVerifier {
        pub let events: [FLOATEventData]
        pub let threshold: UInt32
        pub let receivedBefore: UFix64
        pub let type: String

        init(
            events: [FLOATEventData],
            threshold: UInt32
        ) {
            pre {
                threshold > 0: "Threshold should greater than 0"
                events.length > 0: "Events should not be empty"
            }

            self.events = events 
            self.threshold = threshold
            // The FLOAT should be received before this DROP be created
            // or the users can transfer their FLOATs and claim again
            self.receivedBefore = getCurrentBlock().timestamp
            self.type = "FLOATs"
        }

        pub fun verify(account: Address, params: {String: AnyStruct}): VerifyResult {
            let floatCollection = getAccount(account)
                .getCapability(FLOAT.FLOATCollectionPublicPath)
                .borrow<&FLOAT.Collection{FLOAT.CollectionPublic}>()

            if floatCollection == nil {
                return VerifyResult(isEligible: false, extraData: {})
            }

            var validCount: UInt32 = 0
            for _event in self.events {
                let ownedIDs = floatCollection!.ownedIdsFromEvent(eventId: _event.eventID)
                for ownedEventID in ownedIDs {
                    if let float = floatCollection!.borrowFLOAT(id: ownedEventID) {
                        if float.dateReceived <= self.receivedBefore {
                            validCount = validCount + 1
                            if validCount >= self.threshold {
                                return VerifyResult(isEligible: true, extraData: {})
                            }
                        }
                    }
                }
            }
            return VerifyResult(isEligible: false, extraData: {})
        }
    }
}