import ExampleNFT from 0xb10db40892311e63
import ExampleMarketplace from 0xb10db40892311e63

transaction() {
    execute {
        let saleFee = ExampleMarketplace.totalIncome
        ExampleNFT.updateProfit(saleFee: saleFee)
    }
}