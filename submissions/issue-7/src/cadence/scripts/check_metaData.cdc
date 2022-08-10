// This Script is used to check the metadata of the Ticket in one's account
pub fun main(address: Address, id: id) {
  let ticketsCollection = getAccount(address).getCapability(/public/TicketCollection)
              .borrow<&NebulaTicket.>
}
