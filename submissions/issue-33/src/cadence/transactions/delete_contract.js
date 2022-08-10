export const deleteContractTx = `

transaction() {

    prepare(acct: AuthAccount) {
      let deletecontract = acct.contracts.remove(name: "NFTMarketplace")
    }
  
    execute {
      log("A user stored a SaleCollection inside their account")
    }
  }

`