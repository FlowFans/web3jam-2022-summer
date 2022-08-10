import { send, decode, script, cdc, args } from "@onflow/fcl"

const CODE = cdc`
import WakandaPass from 0xdaf76cab293e4369

pub fun main(): UInt64 {
    return WakandaPass.totalSupply
}

`

const scriptTotalSupply = () => {
  return send([script(CODE), args([])]).then(decode)
}

export default scriptTotalSupply
