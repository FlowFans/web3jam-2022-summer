
import OverluModel from 0xOverluModel

pub fun main(id: UInt64): Address {
  return OverluModel.getOwner(id)
}
