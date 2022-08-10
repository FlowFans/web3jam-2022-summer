
import Melody from 0xMelody
import MelodyTicket from 0xMelodyTicket

pub fun main(addr: Address): [{String: AnyStruct}] {
    var payments: [{String: AnyStruct}] = []
    let collection = getAccount(addr).getCapability<&{MelodyTicket.CollectionPublic}>(MelodyTicket.CollectionPublicPath).borrow()
    if collection == nil {
        return payments
    }
    let ids = collection!.getIDs()
    let tickets: [{String: AnyStruct}] = []

    for id in ids {
        let paymentInfo = Melody.getPaymentInfo(id)
        tickets.append(paymentInfo)
    }
    return tickets
}
