
import OverluPackage from 0xOverluPackage

pub fun main(typeId: UInt64): {String: AnyStruct}? {
  return OverluPackage.getMetadata(typeId)
}
