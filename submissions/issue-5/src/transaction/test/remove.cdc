
transaction {

    prepare(acct: AuthAccount) {
        acct.contracts.remove(name: "FungibleToken")
    }

    execute {

    }
}
