
import Melody from 0xMelody

pub fun main(id: UInt64): {String: AnyStruct} {

  return Melody.getPaymentInfo(id)
}
