
import OverluPackage from 0xOverluPackage

pub fun main(typeId: UInt64): UInt64 {
  return OverluPackage.getTypeSupply(typeId) ?? 0
}
