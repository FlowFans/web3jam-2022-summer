// Distributors defines the ways to setting the reward amount
//
// In Drizzle, for setting the amount of reward, you have two choices as well:
// 1. Identical: All claimers will get identical amount of reward.
// 2. Random: The claimers will get a random amount of reward (the range should be from 0.00000001 to (2.0 * available amount / available capacity - 0.00000001)). To simplify user interaction, a somewhat naive implementation is applied here, and someone might get a higher reward by using “Try & Abort”, so please use it for fun only.

pub contract Distributors {

    pub struct interface IDistributor {
        // capacity defines the available quota in a DROP
        pub let capacity: UInt32
        pub let type: String

        pub fun isAvailable(params: {String: AnyStruct}): Bool
        // getEligibleAmount defines how much reward can a claimer get in this DROP
        pub fun getEligibleAmount(params: {String: AnyStruct}): UFix64
    }

    pub struct Exclusive: IDistributor {
        pub let capacity: UInt32
        pub let distributeList: {Address: UFix64}
        pub let type: String

        init(distributeList: {Address: UFix64}) {
            pre {
                distributeList.keys.length > 0: "empty distributeList"
            }
            self.capacity = UInt32.max
            self.distributeList = distributeList
            self.type = "Exclusive"
        }

        // always available
        pub fun isAvailable(params: {String: AnyStruct}): Bool {
            return true
        }

        pub fun getEligibleAmount(params: {String: AnyStruct}): UFix64 {
            let claimer = params["claimer"]! as! Address
            return self.distributeList[claimer] ?? 0.0
        }
    }

    pub struct Identical: IDistributor {
        pub let capacity: UInt32
        pub let amountPerEntry: UFix64
        pub let type: String

        init(capacity: UInt32, amountPerEntry: UFix64) {
            pre {
                amountPerEntry > 0.0: "invalid amount"
            }
            self.capacity = capacity
            self.amountPerEntry = amountPerEntry
            self.type = "Identical"
        }

        pub fun isAvailable(params: {String: AnyStruct}): Bool {
            let claimedCount = params["claimedCount"]! as! UInt32
            let availableCapacity = self.capacity - claimedCount 
            return availableCapacity > 0
        }

        pub fun getEligibleAmount(params: {String: AnyStruct}): UFix64 {
            if !self.isAvailable(params: params) {
                return 0.0
            }
            return self.amountPerEntry
        }
    }

    // To simplify user interaction, the implementation of this mode is a bit naive, 
    // someone might get a higher reward by using "Try & Abort", so please use it just for fun.
    pub struct Random: IDistributor {
        pub let capacity: UInt32
        pub let totalAmount: UFix64
        pub let type: String

        init(capacity: UInt32, totalAmount: UFix64) {
            assert(totalAmount >= UFix64(capacity) * 0.001, message: "amount is too small")

            self.capacity = capacity
            self.totalAmount = totalAmount
            self.type = "Random"
        }

        pub fun isAvailable(params: {String: AnyStruct}): Bool {
            let claimedCount = params["claimedCount"]! as! UInt32
            let availableCapacity = self.capacity - claimedCount 
            if availableCapacity <= 0 {
                return false
            }

            let claimedAmount = params["claimedAmount"]! as! UFix64
            let availableAmount = self.totalAmount - claimedAmount
            if availableAmount <= 0.0 {
                return false
            }

            return true
        }

        pub fun getEligibleAmount(params: {String: AnyStruct}): UFix64 {
            let claimedCount = params["claimedCount"]! as! UInt32
            let availableCapacity = self.capacity - claimedCount 
            if availableCapacity <= 0 {
                return 0.0
            }

            let claimedAmount = params["claimedAmount"]! as! UFix64
            let availableAmount = self.totalAmount - claimedAmount
            if availableAmount <= 0.0 {
                return 0.0
            }

            if availableCapacity == 1 {
                return availableAmount
            }

            let minAmount = 0.00000001
            let upperAmount = 2.0 * (availableAmount / UFix64(availableCapacity)) - minAmount
            let amount = UFix64(unsafeRandom() / 100000000) / UFix64(UInt64.max / 100000000) * upperAmount

            // make sure no account will claim a 0 packet
            return amount < minAmount ? minAmount : amount
        }
    }
}