export const CREATE_USER_COLLECTION = `
    import NonFungibleToken from 0x631e88ae7f1d7c20
    import ExampleNFTUser from 0xb096b656ab049551

    transaction{
        let acct: AuthAccount
        prepare(acct: AuthAccount){
            self.acct = acct
        }
        execute{
            self.acct.save(<- ExampleNFTUser.createEmptyCollection(),to: ExampleNFTUser.CollectionStoragePath)
            self.acct.link<&ExampleNFTUser.Collection{NonFungibleToken.CollectionPublic, ExampleNFTUser.NFTUserCollectionPublic}>(
            ExampleNFTUser.CollectionPublicPath,
            target: ExampleNFTUser.CollectionStoragePath
            )
        }
    }
`
