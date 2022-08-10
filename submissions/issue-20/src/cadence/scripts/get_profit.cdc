import ExampleNFT from 0xb10db40892311e63
import ExampleMarketplace from 0xb10db40892311e63

pub fun main(): UFix64 {
    return ExampleNFT.totalIncome + ExampleMarketplace.totalIncome
}