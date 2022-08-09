
import MelodyTicket from 0xMelodyTicket

pub fun main(id: UInt64): {String: AnyStruct}? {
  return MelodyTicket.getMetadata(id)
}
