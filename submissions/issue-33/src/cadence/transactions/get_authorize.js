export const getAuthorizeTx = `

import MyNFT from 0xf2011014fb9bee77
import NonFungibleToken from 0x631e88ae7f1d7c20
import FlowToken from 0x7e60df042a9c0868
import NFTCharityMarket from 0xf2011014fb9bee77

transaction(account: Address, id: UInt64) {

  prepare(acct: AuthAccount) {
    let saleCollection = getAccount(account).getCapability(/public/MyAuthorizeCollection)
                            .borrow<&NFTCharityMarket.AuthorizeCollection{NFTCharityMarket.AuthorizeCollectionPublic}>()
                            ?? panic("could not borrow the user's salecollection")
    let authorizedCollection = getAccount(account).getCapability<&MyNFT.Collection{MyNFT.CollectionPublic, NonFungibleToken.CollectionPublic}>(/public/MyNFTCollection)
    let recipientCollection = getAccount(acct.address).getCapability(/public/MyFractionalNFTCollection)
                            .borrow<&NFTCharityMarket.FractionalNFTCollection>()
                            ?? panic("Cannot get the user's collection")
    
    let price = saleCollection.getAuthorizePrice(id: id)

    let payment <- acct.borrow<&FlowToken.Vault>(from: /storage/flowTokenVault)!.withdraw(amount: price) as! @FlowToken.Vault
    
    saleCollection.authorize(id: id, recipientCollection: recipientCollection, payment: <- payment, authorizationNFT: authorizedCollection)
  }

  execute {
    log("A user purchased an NFT")
  }
}

`