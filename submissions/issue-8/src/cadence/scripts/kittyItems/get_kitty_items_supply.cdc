import OnlyBadges from "../../contracts/OnlyBadges.cdc"

// This scripts returns the number of OnlyBadges currently in existence.

pub fun main(): UInt64 {    
    return OnlyBadges.totalSupply
}
