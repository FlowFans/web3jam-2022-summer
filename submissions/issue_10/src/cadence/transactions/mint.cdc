import NonFungibleToken from "../contracts/NonFungibleToken.cdc"
import SinGirlsNFT from "../contracts/SinGirlsNFT.cdc"
import MetadataViews from "../contracts/MetadataViews.cdc"
import FungibleToken from "./contracts/FungibleToken.cdc"

transaction(
    recipient: Address,
    name: [String],
    description: [String],
    thumbnail: [String],
    cuts: [UFix64],
    royaltyDescriptions: [String],
    royaltyBeneficiaries: [Address],
    metadata:  [{String: AnyStruct}]
) {

  // Let's assume the `signer` was the one who deployed the contract, since only they have the `Minter` resource
  prepare(signer: AuthAccount) {

  var royalties: [MetadataViews.Royalty] = []
    var count = 0
    while royaltyBeneficiaries.length > count {
      let beneficiary = royaltyBeneficiaries[count]
      let beneficiaryCapability = getAccount(beneficiary)
        .getCapability<&{FungibleToken.Receiver}>(MetadataViews.getRoyaltyReceiverPublicPath())

      // Make sure the royalty capability is valid before minting the NFT
      if !beneficiaryCapability.check() { panic("Beneficiary capability is not valid!") }

      royalties.append(
        MetadataViews.Royalty(
          receiver: beneficiaryCapability,
            cut: cuts[count],
            description: royaltyDescriptions[count]
            )
          )
      count = count + 1
    }
    // Get a reference to the `Minter`
    let minter = signer.borrow<&SinGirlsNFT.Minter>(from: /storage/Minter)
                    ?? panic("This signer is not the one who deployed the contract.")

    // Get a reference to the `recipient`s public Collection
    let recipientsCollection = getAccount(recipient).getCapability(/public/SinGirlsCollection)
                                  .borrow<&SinGirlsNFT.Collection{SinGirlsNFT.CollectionPublic}>()
                                  ?? panic("The recipient does not have a Collection.")

    // mint the NFT using the reference to the `Minter` and pass in the metadata
    var i = 0
    while name.length > i{
      let nft <- minter.createNFT(_name: name[i], _description: description[i], _thumbnail: thumbnail[i], _royalty: royalties, _metadata: metadata[i])

    // deposit the NFT in the recipient's Collection
      recipientsCollection.deposit(token: <- nft)

      i = i + 1}
  }

}
