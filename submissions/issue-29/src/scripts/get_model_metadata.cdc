
import OverluModel from 0xOverluModel

pub fun main(id: UInt64): {String: AnyStruct}? {
  return OverluModel.getMetadata(id)
}
