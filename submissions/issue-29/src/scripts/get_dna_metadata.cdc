
import OverluDNA from 0xOverluDNA

pub fun main(typeId: UInt64): {String: AnyStruct}? {
  return OverluDNA.getMetadata(typeId)
}
