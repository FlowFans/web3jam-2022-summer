
import Melody from 0xMelody

pub fun main(addr: Address): [{String: AnyStruct}] {
    let prymentIds = Melody.getUserTicketRecords(addr)
    let tickets: [{String: AnyStruct}] = []

    for id in prymentIds {
        let paymentInfo = Melody.getPaymentInfo(id)
        tickets.append(paymentInfo)
    }
    return tickets
}
