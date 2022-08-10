import NebulaActivity from "../contract/NebulaActivity.cdc"

transaction(activityName: String, supply: {String: UInt64}, price: {String: UFix64}) {
    prepare(acct: AuthAccount) {
        let manager = acct.borrow<&NebulaActivity.ActivitiesManager>(from: /storage/ActivitiesManager)
            ?? panic("Your account don't have an activities manager")
        manager.addTickets(
        _supply: supply,
        _activityName: activityName,
        _price: price)
        log("Successfully add info in i")
    }
}
