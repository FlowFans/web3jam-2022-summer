// let counter <- acct.load<@MyNFT.Collection>(from: /storage/MyNFTCollection) ?? panic("THere is no resource");
// destroy counter
// let MyNFTCollection <- acct.load<@NFTMarketplace.SaleCollection>(from: /storage/MySaleCollection) ?? panic("THere is no salescollection");
// destroy MyNFTCollection

export const deleteCollection = `
import MyNFT from 0xf2011014fb9bee77
import NFTMarketplace from 0xf2011014fb9bee77
import NFTCharityMarket from 0xf2011014fb9bee77



transaction {
    prepare(acct: AuthAccount) {
        let counter <- acct.load<@MyNFT.Collection>(from: /storage/MyNFTCollection) ?? panic("THere is no resource");
        destroy counter
        let MyNFTCollection <- acct.load<@NFTMarketplace.SaleCollection>(from: /storage/MySaleCollection) ?? panic("THere is no salescollection");
        destroy MyNFTCollection
        let counter2 <- acct.load<@NFTCharityMarket.AuthorizeCollection>(from: /storage/MyAuthorizeCollection) ?? panic("THere is no resource");
        destroy counter2
        let MyNFTCollection2 <- acct.load<@NFTCharityMarket.FractionalNFTCollection>(from: /storage/MyFractionalNFTCollection) ?? panic("THere is no salescollection");
        destroy MyNFTCollection2
    }
    execute {
      log("A user delete a Collection inside their account")
    }
  }

`