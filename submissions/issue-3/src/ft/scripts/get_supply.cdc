// This script reads the total supply field

import ContributionPoint from "../../contracts/ContributionPoint.cdc"

pub fun main(): UFix64 {

    let supply = ContributionPoint.totalSupply

    log(supply)

    return supply
}