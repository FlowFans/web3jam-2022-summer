
import Melody from 0xMelody
import MelodyTicket from 0xMelodyTicket

pub fun main(addr: Address): {String: Int} {
    var payments: [{String: AnyStruct}] = []
    let collection = getAccount(addr).getCapability<&{MelodyTicket.CollectionPublic}>(MelodyTicket.CollectionPublicPath).borrow()
    let stats : {String: Int} = {}

    if collection == nil {
        
        stats["incomes"] = 0
    } else {
        let ids = collection!.getIDs()
        stats["incomes"] = ids.length
    }
   

    let paymentIds = Melody.getPaymentsIdRecords(addr)
    stats["payments"] = paymentIds.length
    
    let unclaimedTicketIds = Melody.getUserTicketRecords(addr)
    stats["unclaimed"] = unclaimedTicketIds.length

    return stats
}
