
import Melody from 0xMelody


pub fun main(addr: Address): [{String: AnyStruct}] {
    var payments: [{String: AnyStruct}] = []
    let ids = Melody.getPaymentsIdRecords(addr)

    for id in ids {
        let paymentInfo = Melody.getPaymentInfo(id)
        payments.append(paymentInfo)
    }
    return payments
}
