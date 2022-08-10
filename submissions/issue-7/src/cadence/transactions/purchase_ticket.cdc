import NebulaActivity from 0x01
import FungibleToken from 0x04
import ExampleToken from 0x05

transaction(activityName: String, number: UInt64, type: String, sellerAddress: Address) {
    // "ticketCollection" is the buyer's owned ticketCollection
    let ticketCollection: &NebulaActivity.TicketsCollection{NebulaActivity.TicketsCollectionPublic}
    // "ticketMachine" is the auto-sell-machine for this activity
    let ticketMachine: &NebulaActivity.ActivitiesManager{NebulaActivity.AutoActivitesShop}

    let paymentVault: @FungibleToken.Vault

    var commissionRecipient: Capability<&{FungibleToken.Receiver}>?

    prepare(acct: AuthAccount) {
        // Acquire the capability of the ticketMachine
        self.ticketMachine = getAccount(sellerAddress)
        .getCapability<&NebulaActivity.ActivitiesManager{NebulaActivity.AutoActivitesShop}>(/public/AutoActivityShop)!
        .borrow() ?? panic("No way")

        // Access the buyer's tickets collection
        self.ticketCollection = acct.borrow<&NebulaActivity.TicketsCollection{NebulaActivity.TicketsCollectionPublic}>
        (from: /storage/TicketsCollection) ?? panic("hh")

        // Get the corresponding price
        let prices = self.ticketMachine.equeryTicketsPrice(activityName: activityName)
        let price = prices[type]!

        // Get the vault to pay
        let mainFlowVault = acct.borrow<&ExampleToken.Vault>(from: /storage/flowTokenVault)
            ?? panic("Cannot borrow FlowToken vault from acct storage")
        self.paymentVault <- mainFlowVault.withdraw(amount: price)

        self.commissionRecipient = getAccount(sellerAddress!).getCapability<&{FungibleToken.Receiver}>(/public/flowTokenReceiver)

  }

  execute {
    let ticket <- self.ticketMachine.purchase(
    activityName: activityName,
    type: type,
    number: number,
    payment: <- self.paymentVault,
    commissionRecipient: self.commissionRecipient
    )
    // Put the new purchased ticket into your collection
    self.ticketCollection.deposit(ticket: <- ticket)
  }
}
