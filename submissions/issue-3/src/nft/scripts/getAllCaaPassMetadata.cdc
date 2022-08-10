import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import CaaPass from "../../contracts/CaaPass.cdc"

pub fun main(address: Address): [CaaPass.Metadata] {
    let collectionRef = getAccount(address).getCapability(CaaPass.CollectionPublicPath)
        .borrow<&{NonFungibleToken.CollectionPublic, CaaPass.CollectionPublic}>()
        ?? panic("Could not borrow collection public reference")

    let ids = collectionRef.getIDs()
    let array: [CaaPass.Metadata] = []

    for id in ids {
        let caaPass = collectionRef.borrowCaaPass(id: id)!
        array.append(caaPass.getMetadata()!)
    }

    return array
}
