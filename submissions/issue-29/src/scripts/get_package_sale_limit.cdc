
import OverluPackage from 0xOverluPackage

pub fun main(addr: Address): [UInt64] {
  return OverluPackage.getSaleRecords(addr)
}
