
import OverluDNA from 0xOverluDNA

pub fun main(typeId: UInt64): UInt64 {
  return OverluDNA.getTypeSupply(typeId) ?? 0
}
