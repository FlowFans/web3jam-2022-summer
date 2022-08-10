import NebulaActivity from "./contract/NebulaActivity.cdc"

transaction(consumerAddress: Address, activityName: String, id: UInt64) {
    let ticketCollection: &NebulaActivity.TicketsCollection{NebulaActivity.TicketsCollectionPublic}
    let activityShop: &NebulaActivity.ActivitiesManager
    let ticket: &NebulaActivity.NebulaTicket

    prepare(signer: AuthAccount) {
        // Access the buyer's tickets collection
        self.ticketCollection = getAccount(consumerAddress)
        .getCapability<&NebulaActivity.TicketsCollection{NebulaActivity.TicketsCollectionPublic}>(/public/TicketsCollection)
        .borrow() ?? panic("hh")
        self.ticket = self.ticketCollection.borrowNebulaTicket(id: id) ?? panic("")

        self.activityShop = signer.borrow<&NebulaActivity.ActivitiesManager>(from: /storage/ActivitiesManager) ?? panic("No manager in your account")

    }

    execute {
        let ticketMachine = self.activityShop.borrowTicketMachine(activityName: activityName)
        let res = ticketMachine.verifyHost(id: 1, ticket: Capability<&NebulaTicket{NebulaTicketPublic}>, date: Date)

    }
}
