import NebulaActivity from 0x01

transaction(address: Address) {

  prepare(acct: AuthAccount) {
    // create the activities manager to our account
    acct.save(<-NebulaActivity.createEmptyActivitiesManager(_address: address), to: /storage/ActivitiesManager)
    // Link the public functions to the public storage
    acct.link<&NebulaActivity.ActivitiesManager{NebulaActivity.AutoActivitesShop}>(/public/AutoActivityShop, target: /storage/ActivitiesManager)

    // create the tickets collection to our account
    acct.save(<- NebulaActivity.createEmptyTicketsCollection(), to: /storage/TicketsCollection)
    // Link the public functions to the public storage
    acct.link<&NebulaActivity.TicketsCollection{NebulaActivity.TicketsCollectionPublic}>(/public/TicketsCollectionPublic, target: /storage/TicketsCollection)

  }

  execute {

  }
}
