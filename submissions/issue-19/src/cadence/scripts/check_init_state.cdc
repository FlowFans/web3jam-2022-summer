
import Melody from 0xMelody
import MelodyTicket from 0xMelodyTicket

pub fun main(addr: Address): Bool {
    let account = getAccount(addr)    
    return account.getCapability<&{MelodyTicket.CollectionPublic}>(MelodyTicket.CollectionPublicPath).check()
}
