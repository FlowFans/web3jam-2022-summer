import Racenumber from 0xf8d6e0586b0a20c7


pub fun main(addr:Address):[Racenumber.ThemeMeta]{
    let acct = getAccount(addr)
    let nfts:[Racenumber.ThemeMeta] = []
    if !acct.getCapability<&Racenumber.ThemeCollection{Racenumber.ThemeCollectionPublic}>(Racenumber.ThemeNFTCollectionPublicPath).check(){
        return nfts
    }
    let themeCollectionRef = acct.getCapability<&Racenumber.ThemeCollection{Racenumber.ThemeCollectionPublic}>(Racenumber.ThemeNFTCollectionPublicPath).borrow()!
    let ids = themeCollectionRef.getIDs()
    for id in ids{
        let nft = themeCollectionRef.borrowNFT(id: id)
        let gameDetail = Racenumber.getAllGames()[nft.gameUId]!
        let metadata = Racenumber.ThemeMeta(gameDetail:gameDetail,num:nft.num,background:nft.background)
        nfts.append(metadata)
    }
    log(nfts.length)
    return nfts
}