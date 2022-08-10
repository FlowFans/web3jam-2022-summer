export const GET_ACTIVITY_ALL = `
import PioneerMarketplace from 0xPioneerNFT
pub fun main(adminAcct:Address):{UInt64:PioneerMarketplace.Activity}? {
    let account = getAccount(adminAcct)
    
    var all_activity:{UInt64:PioneerMarketplace.Activity}={}
    if let storefrontRef = account.getCapability<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}>(PioneerMarketplace.StorefrontActivityPublicPath).borrow() {
      let addrKeys = storefrontRef.getListingIDs()
      if addrKeys.length > 0{
          var i=0
          while i <addrKeys.length {
              if let listing = storefrontRef.borrowListing(listingResourceID: addrKeys[i]) {
                  let details = listing.getDetails()
                  let itemID : UInt64 = details.activeID!
                  let id :UInt64 = addrKeys[i]
                  all_activity[id] = PioneerMarketplace.getActivity(id:itemID)!
              }
              i=i+1
          }
        return all_activity 
        }
    }
    return nil
}
  
`