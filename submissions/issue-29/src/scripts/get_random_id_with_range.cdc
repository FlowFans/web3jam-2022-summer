
import OverluConfig from 0xOverluConfig

pub fun main(range: UInt64): UInt64 {
  return OverluConfig.getRandomId(10) % range
}
