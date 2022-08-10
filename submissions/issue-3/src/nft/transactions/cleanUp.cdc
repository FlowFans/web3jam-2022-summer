transaction {

    prepare(signer: AuthAccount) {
        let admin1 <- signer.load<@AnyResource>(from: /storage/caaArtsMinter)
        destroy admin1

        let admin2 <- signer.load<@AnyResource>(from: /storage/caaPassMinter)
        destroy admin2
    }
}
