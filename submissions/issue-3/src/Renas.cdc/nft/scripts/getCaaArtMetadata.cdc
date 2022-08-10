import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import CaaArts from "../../contracts/CaaArts.cdc"

pub fun main(address: Address): CaaArts.Metadata? {
    let collectionRef = getAccount(address).getCapability(CaaArts.CollectionPublicPath)
        .borrow<&{NonFungibleToken.CollectionPublic, CaaArts.CollectionPublic}>()
        ?? panic("Could not borrow collection public reference")

    let ids = collectionRef.getIDs()
    let caaArt = collectionRef.borrowCaaArt(id: ids[0])!

    return caaArt.getMetadata()
}
