// import NFTStorefront from "../../contracts/NFTStorefront.cdc"

// This script returns an array of all the nft uuids for sale through a Storefront

// testnet
import NFTStorefront from 0x4eb8a10cb9f87357

pub fun main(account: Address): Int {
    let storefrontRef = getAccount(account)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
            NFTStorefront.StorefrontPublicPath
        )
        .borrow()
        ?? panic("Could not borrow public storefront from address")
    
    return storefrontRef.getListingIDs().length
}
 