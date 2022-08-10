// This script reads the total supply field
// of the CarlyToken smart contract

import CarlyToken from "../contracts/CarlyToken.cdc"

pub fun main(): UFix64 {

    let supply = CarlyToken.totalSupply

    log(supply)

    return supply
}